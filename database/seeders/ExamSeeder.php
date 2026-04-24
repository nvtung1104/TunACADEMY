<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExamSeeder extends Seeder
{
    public function run(): void
    {
        $classroom = Classroom::where('name', '10A1')->first();
        $subject = Subject::where('code', 'MATH')->first();
        $teacher = User::where('email', 'teacher1@tunacademy.com')->first();

        Exam::updateOrCreate(
            [
                'classroom_id' => $classroom->id,
                'subject_id' => $subject->id,
                'title' => 'Kiểm tra 15 phút - Toán',
            ],
            [
                'teacher_id' => $teacher->id,
                'description' => 'Kiểm tra nhanh kiến thức hàm số',
                'duration_minutes' => 15,
                'opened_at' => now()->subHour(),
                'closed_at' => now()->addDays(1),
                'shuffle_questions' => true,
                'shuffle_options' => true,
                'proctoring_enabled' => false,
                'max_violations' => 3,
                'show_result' => 'immediate',
                'allow_retake' => false,
                'status' => 'published',
            ]
        );
    }
}