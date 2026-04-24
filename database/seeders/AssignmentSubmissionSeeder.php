<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssignmentSubmissionSeeder extends Seeder
{
    public function run(): void
    {
        $assignment = Assignment::first();
        $teacher = User::where('email', 'teacher1@tunacademy.com')->first();
        $students = User::whereHas('role', fn($q) => $q->where('name', 'student'))->get();

        foreach ($students as $index => $student) {
            AssignmentSubmission::updateOrCreate(
                [
                    'assignment_id' => $assignment->id,
                    'student_id' => $student->id,
                ],
                [
                    'content' => 'Bài làm của học sinh ' . $student->name,
                    'file_paths' => ['submissions/bai-lam-' . $student->id . '.pdf'],
                    'submitted_at' => now()->subHours($index),
                    'is_late' => false,
                    'status' => 'graded',
                    'score' => 7 + $index % 3,
                    'feedback' => 'Cần trình bày rõ hơn.',
                    'graded_at' => now(),
                    'graded_by' => $teacher->id,
                ]
            );
        }
    }
}