<?php

namespace Database\Seeders;

use App\Models\LiveAttendance;
use App\Models\LiveSession;
use App\Models\User;
use Illuminate\Database\Seeder;

class LiveAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $session = LiveSession::first();
        $teacher = User::where('email', 'teacher1@tunacademy.com')->first();
        $students = User::whereHas('role', fn($q) => $q->where('name', 'student'))->get();

        foreach ($students as $student) {
            LiveAttendance::updateOrCreate(
                [
                    'session_id' => $session->id,
                    'student_id' => $student->id,
                ],
                [
                    'status' => 'present',
                    'join_time' => now(),
                    'total_time_seconds' => 1800,
                    'attendance_percent' => 100,
                    'note' => 'Có mặt đầy đủ',
                    'confirmed_by' => $teacher->id,
                    'confirmed_at' => now(),
                ]
            );
        }
    }
}