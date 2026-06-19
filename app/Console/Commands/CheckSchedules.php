<?php

namespace App\Console\Commands;

use App\Models\Classroom;
use App\Models\Notification;
use App\Models\Schedule;
use App\Models\User;
use App\Mail\ScheduleNotificationMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CheckSchedules extends Command
{
    protected $signature = 'schedules:check';
    protected $description = 'Check and send notifications for schedules/timetables';

    public function handle()
    {
        $today = today()->toDateString();
        $now = now();
        $dayOfWeek = $now->dayOfWeekIso + 1; // ISO 1-7 -> 2-8 (2 = Mon, 8 = Sun)
        if ($dayOfWeek > 8) {
            $dayOfWeek = 2; // safety fallback
        }

        // We check if start_time is less than or equal to current time, and within a 5-minute window
        // to prevent missed notifications. Using last_notified_at ensures we notify exactly once a day.
        $currentTimeStr = $now->format('H:i:s');
        $fiveMinutesAgoStr = $now->copy()->subMinutes(5)->format('H:i:s');

        $activeSchedules = Schedule::where('day_of_week', $dayOfWeek)
            ->whereTime('start_time', '<=', $currentTimeStr)
            ->whereTime('start_time', '>=', $fiveMinutesAgoStr)
            ->where(function ($q) use ($today) {
                $q->whereNull('last_notified_at')
                  ->orWhereDate('last_notified_at', '<', $today);
            })
            ->get();

        $this->info('Found ' . $activeSchedules->count() . ' schedules to notify.');

        foreach ($activeSchedules as $schedule) {
            if ($schedule->classroom_id) {
                // Tier 1: Teacher-created classroom schedule. Notify student accounts.
                $classroom = Classroom::with('students')->find($schedule->classroom_id);
                if ($classroom) {
                    foreach ($classroom->students as $student) {
                        Notification::create([
                            'user_id' => $student->id,
                            'type'    => 'classroom_schedule',
                            'title'   => 'Đến giờ học lớp ' . $classroom->name,
                            'message' => 'Lớp học "' . $schedule->title . '" bắt đầu lúc ' . substr($schedule->start_time, 0, 5) . '.',
                            'data'    => [
                                'schedule_id'    => $schedule->id,
                                'classroom_id'   => $classroom->id,
                                'classroom_name' => $classroom->name,
                                'start_time'     => $schedule->start_time,
                            ],
                            'read_at' => null,
                        ]);
                    }
                    $this->info("Notified classroom: " . $classroom->name);
                }
            } elseif ($schedule->student_id) {
                // Tier 2: Student-created personal schedule. Notify student account + email.
                $student = User::find($schedule->student_id);
                if ($student) {
                    // 1. Account notification
                    Notification::create([
                        'user_id' => $student->id,
                        'type'    => 'personal_schedule',
                        'title'   => 'Đến giờ tự học: ' . $schedule->title,
                        'message' => 'Lịch tự học bắt đầu lúc ' . substr($schedule->start_time, 0, 5) . '.',
                        'data'    => [
                            'schedule_id' => $schedule->id,
                            'start_time'  => $schedule->start_time,
                        ],
                        'read_at' => null,
                    ]);

                    // 2. Email notification
                    if ($student->email) {
                        try {
                            Mail::to($student->email)->send(new ScheduleNotificationMail($student, $schedule));
                            $this->info("Emailed student: " . $student->email);
                        } catch (\Exception $e) {
                            Log::error("Failed to email schedule notification to " . $student->email . ": " . $e->getMessage());
                        }
                    }
                    $this->info("Notified student: " . $student->name);
                }
            }

            // Mark as notified today
            $schedule->update(['last_notified_at' => $now]);
        }

        return Command::SUCCESS;
    }
}
