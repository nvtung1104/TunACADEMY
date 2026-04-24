<?php

namespace Database\Seeders;

use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\StudentAnswer;
use Illuminate\Database\Seeder;

class StudentAnswerSeeder extends Seeder
{
    public function run(): void
    {
        $attempts = ExamAttempt::all();
        $questions = ExamQuestion::orderBy('order_index')->get()->values();

        foreach ($attempts as $attempt) {
            foreach ($questions as $question) {
                $answer = $question->order_index === 1 ? 'A' : '2';

                StudentAnswer::updateOrCreate(
                    [
                        'attempt_id' => $attempt->id,
                        'question_id' => $question->id,
                    ],
                    [
                        'answer_content' => $answer,
                        'is_correct' => true,
                        'score_earned' => $question->points,
                        'answered_at' => now(),
                    ]
                );
            }
        }
    }
}