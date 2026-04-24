<?php

namespace Database\Seeders;

use App\Models\AiChatSession;
use App\Models\Lesson;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class AiChatSessionSeeder extends Seeder
{
    public function run(): void
    {
        $lesson = Lesson::first();
        $subject = Subject::where('code', 'MATH')->first();
        $students = User::whereHas('role', fn($q) => $q->where('name', 'student'))->get();

        foreach ($students as $student) {
            AiChatSession::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'lesson_id' => $lesson?->id,
                    'subject_id' => $subject?->id,
                ],
                [
                    'message_count' => 2,
                    'total_tokens' => 150,
                    'started_at' => now()->subMinutes(30),
                    'ended_at' => now()->subMinutes(20),
                ]
            );
        }
    }
}