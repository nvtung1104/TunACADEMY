<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Lesson;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class LessonSeeder extends Seeder
{
    public function run(): void
    {
        $classroom = Classroom::where('name', '10A1')->first();
        $teacher = User::where('email', 'teacher1@tunacademy.com')->first();

        $lessons = [
            [
                'subject_code' => 'MATH',
                'title' => 'Hàm số bậc nhất',
                'description' => 'Giới thiệu về hàm số bậc nhất',
                'content' => '<p>Nội dung bài giảng hàm số bậc nhất</p>',
            ],
            [
                'subject_code' => 'PHYSICS',
                'title' => 'Chuyển động thẳng đều',
                'description' => 'Kiến thức cơ bản về chuyển động',
                'content' => '<p>Nội dung bài giảng vật lý</p>',
            ],
        ];

        foreach ($lessons as $index => $item) {
            $subject = Subject::where('code', $item['subject_code'])->first();

            Lesson::updateOrCreate(
                [
                    'classroom_id' => $classroom->id,
                    'subject_id' => $subject->id,
                    'title' => $item['title'],
                ],
                [
                    'teacher_id' => $teacher->id,
                    'description' => $item['description'],
                    'content' => $item['content'],
                    'video_path' => null,
                    'audio_path' => null,
                    'order_index' => $index + 1,
                    'view_count' => 0,
                    'status' => 'published',
                    'published_at' => now(),
                ]
            );
        }
    }
}