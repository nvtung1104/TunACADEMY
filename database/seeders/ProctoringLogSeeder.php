<?php

namespace Database\Seeders;

use App\Models\ExamAttempt;
use App\Models\ProctoringLog;
use Illuminate\Database\Seeder;

class ProctoringLogSeeder extends Seeder
{
    public function run(): void
    {
        $attempt = ExamAttempt::first();

        if ($attempt) {
            ProctoringLog::updateOrCreate(
                [
                    'attempt_id' => $attempt->id,
                    'student_id' => $attempt->student_id,
                    'violation_type' => 'tab_switch',
                ],
                [
                    'occurred_at' => now()->subMinutes(10),
                    'duration_seconds' => 5,
                    'details' => ['note' => 'Chuyển tab ngắn'],
                ]
            );
        }
    }
}