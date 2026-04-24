<?php

namespace Database\Seeders;

use App\Models\LiveParticipant;
use App\Models\LiveSession;
use App\Models\User;
use Illuminate\Database\Seeder;

class LiveParticipantSeeder extends Seeder
{
    public function run(): void
    {
        $session = LiveSession::first();
        $teacher = User::where('email', 'teacher1@tunacademy.com')->first();
        $students = User::whereHas('role', fn($q) => $q->where('name', 'student'))->get();

        LiveParticipant::updateOrCreate(
            [
                'session_id' => $session->id,
                'user_id' => $teacher->id,
            ],
            [
                'role' => 'host',
                'joined_at' => now(),
                'left_at' => null,
                'total_time_seconds' => 0,
                'mic_enabled' => true,
                'cam_enabled' => true,
                'is_present' => true,
                'device_info' => ['device' => 'Laptop'],
            ]
        );

        foreach ($students as $student) {
            LiveParticipant::updateOrCreate(
                [
                    'session_id' => $session->id,
                    'user_id' => $student->id,
                ],
                [
                    'role' => 'student',
                    'joined_at' => now(),
                    'left_at' => null,
                    'total_time_seconds' => 0,
                    'mic_enabled' => false,
                    'cam_enabled' => false,
                    'is_present' => true,
                    'device_info' => ['device' => 'Phone'],
                ]
            );
        }
    }
}