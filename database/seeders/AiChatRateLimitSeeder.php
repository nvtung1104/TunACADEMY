<?php

namespace Database\Seeders;

use App\Models\AiChatRateLimit;
use App\Models\User;
use Illuminate\Database\Seeder;

class AiChatRateLimitSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::role('student')->get();

        foreach ($students as $student) {
            AiChatRateLimit::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'date' => now()->toDateString(),
                ],
                [
                    'daily_count' => 2,
                    'hourly_count' => 1,
                    'last_request_at' => now(),
                ]
            );
        }
    }
}