<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $classroom = Classroom::where('name', '10A1')->first();
        $subject = Subject::where('code', 'MATH')->first();
        $teacher = User::where('email', 'teacher1@tunacademy.com')->first();

        Assignment::updateOrCreate(
            [
                'classroom_id' => $classroom->id,
                'subject_id' => $subject->id,
                'title' => 'Bài tập hàm số bậc nhất',
            ],
            [
                'teacher_id' => $teacher->id,
                'description' => 'Làm các câu 1 đến 10',
                'type' => 'quiz',
                'deadline' => now()->addDays(7),
                'max_score' => 10,
                'allow_late' => false,
                'attachment_paths' => ['assignments/de-bai-1.pdf'],
                'status' => 'published',
            ]
        );
    }
}