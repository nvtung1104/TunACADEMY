<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Lesson;
use App\Models\LiveSession;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class LiveSessionSeeder extends Seeder
{
    public function run(): void
    {
        $classroom = Classroom::where('name', '10A1')->first();
        $lesson = Lesson::first();
        $subject = Subject::where('code', 'MATH')->first();
        $teacher = User::where('email', 'teacher1@tunacademy.com')->first();

        LiveSession::updateOrCreate(
            ['room_code' => 'ROOM101'],
            [
                'classroom_id' => $classroom->id,
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
                'lesson_id' => $lesson?->id,
                'title' => 'Buổi học trực tuyến Toán 10',
                'description' => 'Ôn tập hàm số',
                'scheduled_at' => now()->addDay(),
                'started_at' => null,
                'ended_at' => null,
                'duration_minutes' => 45,
                'max_participants' => 50,
                'allow_screen_share' => true,
                'allow_student_mic' => true,
                'allow_student_cam' => true,
                'chat_enabled' => true,
                'recording_enabled' => false,
                'recording_path' => null,
                'status' => 'scheduled',
            ]
        );
    }
}