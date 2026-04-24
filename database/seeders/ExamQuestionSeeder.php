<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\ExamQuestion;
use Illuminate\Database\Seeder;

class ExamQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $exam = Exam::first();

        $questions = [
            [
                'type' => 'multiple_choice',
                'content' => '<p>Hàm số bậc nhất có dạng nào?</p>',
                'options' => [
                    ['id' => 'A', 'text' => 'y = ax + b'],
                    ['id' => 'B', 'text' => 'y = ax² + b'],
                    ['id' => 'C', 'text' => 'y = a/x'],
                    ['id' => 'D', 'text' => 'y = √x'],
                ],
                'correct_answer' => 'A',
                'points' => 1,
            ],
            [
                'type' => 'fill_blank',
                'content' => '<p>Điền hệ số góc của đường thẳng y = 2x + 1</p>',
                'options' => null,
                'correct_answer' => '2',
                'points' => 1,
            ],
        ];

        foreach ($questions as $index => $question) {
            ExamQuestion::updateOrCreate(
                [
                    'exam_id' => $exam->id,
                    'order_index' => $index + 1,
                ],
                [
                    'type' => $question['type'],
                    'content' => $question['content'],
                    'options' => $question['options'],
                    'correct_answer' => $question['correct_answer'],
                    'points' => $question['points'],
                    'audio_path' => null,
                    'explanation' => 'Đáp án đúng theo lý thuyết cơ bản.',
                    'chapter_tag' => 'Đại số',
                ]
            );
        }
    }
}