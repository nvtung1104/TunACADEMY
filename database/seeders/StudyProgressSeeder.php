<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\StudyProgress;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudyProgressSeeder extends Seeder
{
    public function run(): void
    {
        $lesson = Lesson::first();
        $students = User::whereHas('role', fn($q) => $q->where('name', 'student'))->get();

        foreach ($students as $index => $student) {
            StudyProgress::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'lesson_id' => $lesson->id,
                ],
                [
                    'view_count' => $index + 1,
                    'total_time_seconds' => 300 * ($index + 1),
                    'last_viewed_at' => now(),
                    'is_completed' => $index % 2 === 0,
                ]
            );
        }
    }
}