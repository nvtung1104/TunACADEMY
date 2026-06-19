<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExamAttemptSeeder extends Seeder
{
    public function run(): void
    {
        $exam = Exam::first();
        $students = User::role('student')->get();

        foreach ($students as $index => $student) {
            ExamAttempt::updateOrCreate(
                [
                    'exam_id' => $exam->id,
                    'student_id' => $student->id,
                ],
                [
                    'started_at' => now()->subMinutes(20),
                    'submitted_at' => now()->subMinutes(5),
                    'expires_at' => now()->addMinutes(10),
                    'score' => 7 + ($index % 3),
                    'total_correct' => 1 + ($index % 2),
                    'status' => 'submitted',
                    'violation_count' => 0,
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Seeder Agent',
                ]
            );
        }
    }
}