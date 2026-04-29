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
        $subject   = Subject::where('code', 'MATH')->first();
        $teacher   = User::where('email', 'teacher1@tunacademy.com')->first();

        // Bài kiểm tra 15 phút - chia sẻ cho lớp 10A1
        Exam::updateOrCreate(
            ['classroom_id' => $classroom->id, 'subject_id' => $subject->id, 'title' => 'Kiểm tra 15 phút - Toán'],
            [
                'teacher_id'         => $teacher->id,
                'type'               => 'quiz_15',
                'description'        => 'Kiểm tra nhanh kiến thức hàm số',
                'duration_minutes'   => 15,
                'opened_at'          => now()->subHour(),
                'closed_at'          => now()->addDays(1),
                'shuffle_questions'  => true,
                'shuffle_options'    => true,
                'proctoring_enabled' => true,
                'max_violations'     => 3,
                'show_result'        => 'after_close',
                'allow_retake'       => false,
                'status'             => 'published',
                'visibility'         => 'class',
            ]
        );

        // Bài kiểm tra 30 phút - chia sẻ cho lớp 10A1
        Exam::updateOrCreate(
            ['classroom_id' => $classroom->id, 'subject_id' => $subject->id, 'title' => 'Kiểm tra 30 phút - Toán'],
            [
                'teacher_id'         => $teacher->id,
                'type'               => 'quiz_30',
                'description'        => 'Kiểm tra chương Đạo hàm',
                'duration_minutes'   => 30,
                'opened_at'          => now()->addDays(3),
                'closed_at'          => now()->addDays(3)->addHours(2),
                'shuffle_questions'  => true,
                'shuffle_options'    => true,
                'proctoring_enabled' => true,
                'max_violations'     => 3,
                'show_result'        => 'after_close',
                'allow_retake'       => false,
                'status'             => 'draft',
                'visibility'         => 'private',
            ]
        );

        // Bài kiểm tra 45 phút - chia sẻ cho lớp 10A1
        Exam::updateOrCreate(
            ['classroom_id' => $classroom->id, 'subject_id' => $subject->id, 'title' => 'Kiểm tra 45 phút - Toán'],
            [
                'teacher_id'         => $teacher->id,
                'type'               => 'quiz_45',
                'description'        => 'Kiểm tra học kỳ 1 chương 1-3',
                'duration_minutes'   => 45,
                'opened_at'          => now()->addWeek(),
                'closed_at'          => now()->addWeek()->addHours(3),
                'shuffle_questions'  => true,
                'shuffle_options'    => true,
                'proctoring_enabled' => true,
                'max_violations'     => 3,
                'show_result'        => 'manual',
                'allow_retake'       => false,
                'status'             => 'draft',
                'visibility'         => 'private',
            ]
        );

        // Đề thi ôn tập - công khai, học sinh vào tự do
        Exam::updateOrCreate(
            ['subject_id' => $subject->id, 'title' => 'Đề thi ôn tập - Toán 10 (Học kỳ 1)'],
            [
                'classroom_id'       => null,
                'teacher_id'         => $teacher->id,
                'type'               => 'practice_exam',
                'description'        => 'Đề thi ôn tập tổng hợp kiến thức Toán 10 học kỳ 1. Học sinh có thể làm nhiều lần.',
                'duration_minutes'   => 90,
                'opened_at'          => null,
                'closed_at'          => null,
                'shuffle_questions'  => true,
                'shuffle_options'    => true,
                'proctoring_enabled' => false,
                'max_violations'     => 0,
                'show_result'        => 'immediate',
                'allow_retake'       => true,
                'status'             => 'published',
                'visibility'         => 'public',
            ]
        );
    }
}