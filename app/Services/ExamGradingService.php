<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\QuestionBank;
use App\Models\StudentAnswer;

/**
 * Handles all answer grading and score calculation for exams.
 * Scores are always computed server-side — never accepted from client input.
 */
class ExamGradingService
{
    /**
     * Grade all answers for a submitted attempt and return a GradeResult array.
     *
     * @param  ExamAttempt  $attempt
     * @param  Exam         $exam
     * @param  array        $rawAnswers  Keyed by question ID
     * @return array{earnedPoints: float, totalPoints: float, totalCorrect: int, score: float, answerRows: array}
     */
    public function grade(ExamAttempt $attempt, Exam $exam, array $rawAnswers): array
    {
        $earnedPoints = 0.0;
        $totalPoints  = 0.0;
        $totalCorrect = 0;
        $answerRows   = [];

        // Pre-load all questions into a keyed collection — eliminates N+1
        $questions = $exam->questions()->get()->keyBy('id');
        $totalQuestions = count($questions);
        $weightPerQuestion = $totalQuestions > 0 ? (10.0 / $totalQuestions) : 0.0;

        foreach ($rawAnswers as $questionId => $studentAnswer) {
            $question = $questions->get($questionId);
            if (!$question) {
                continue;
            }

            $isCorrect    = $this->gradeAnswer($studentAnswer, $question->correct_answer, $question->type);
            $pointsEarned = $isCorrect ? $weightPerQuestion : 0.0;
            $stored       = is_array($studentAnswer)
                ? json_encode($studentAnswer, JSON_UNESCAPED_UNICODE)
                : (string) $studentAnswer;

            StudentAnswer::updateOrCreate(
                ['attempt_id' => $attempt->id, 'question_id' => $question->id],
                [
                    'answer_content' => $stored,
                    'is_correct'     => $isCorrect,
                    'score_earned'   => $pointsEarned,
                    'answered_at'    => now(),
                ]
            );

            $totalPoints  += $weightPerQuestion;
            $earnedPoints += $pointsEarned;
            if ($isCorrect) {
                $totalCorrect++;
            }

            $answerRows[] = [
                'question_id'      => $question->id,
                'question_type'    => $question->type,
                'question_content' => $question->content,
                'correct_answer'   => $question->correct_answer,
                'student_answer'   => $studentAnswer,
                'is_correct'       => $isCorrect,
                'points'           => $weightPerQuestion,
                'points_earned'    => $pointsEarned,
            ];
        }

        // Score is always computed as ratio of correct questions / total questions * 10
        $score = $totalQuestions > 0
            ? round(($totalCorrect / $totalQuestions) * 10, 2)
            : 0.0;

        return compact('earnedPoints', 'totalPoints', 'totalCorrect', 'score', 'answerRows');
    }

    /**
     * Determine whether a student answer matches the correct answer for the given question type.
     */
    public function gradeAnswer(mixed $student, mixed $correct, string $type): bool
    {
        // Teacher-graded types — always false (manual grading required)
        if (in_array($type, QuestionBank::TEACHER_GRADED, true)) {
            return false;
        }

        if (is_null($correct) || $correct === []) {
            return false;
        }

        // Numeric comparison with optional tolerance
        if ($type === 'calculation') {
            if (!is_numeric($student)) {
                return false;
            }
            $correctArr = is_array($correct) ? $correct : (array) $correct;
            $val        = (float) ($correctArr['value'] ?? 0);
            $tolerance  = (float) ($correctArr['tolerance'] ?? 0);
            return abs((float) $student - $val) <= $tolerance;
        }

        // Normalise both sides to string arrays for comparison
        $c = array_map('strval', is_array($correct) ? $correct : [$correct]);
        $s = is_array($student) ? array_map('strval', $student) : [strval($student)];

        return match ($type) {
            'multiple_select' => (static function () use ($c, $s): bool {
                sort($c);
                sort($s);
                return $c === $s;
            })(),
            'fill_blank' => count($c) === count($s) && !array_filter(
                array_map(
                    fn($i, $ci) => mb_strtolower(trim($ci)) !== mb_strtolower(trim($s[$i] ?? '')),
                    array_keys($c),
                    $c
                )
            ),
            'ordering' => $c === $s,
            'matching'  => json_encode($correct) === json_encode($student),
            default     => trim($c[0] ?? '') === trim($s[0] ?? strval($student)),
        };
    }
}
