<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;

/**
 * Handles server-side grading for structured assignment submissions.
 * Scores are always computed server-side for auto-graded question types.
 * Essay/speaking/writing types are skipped here and handled by manual or AI grading.
 */
class AssignmentGradingService
{
    public function __construct(private readonly ExamGradingService $grader) {}

    /**
     * Grade a structured submission and return a GradeResult array.
     * Only auto-graded question types contribute to the score.
     *
     * @param  array  $answers  Raw answers keyed by question ID (may be empty for essay submissions)
     * @return array{earnedPoints: float, totalPoints: float, totalAutoGraded: int, score: float|null, hasGradableQuestions: bool}
     */
    public function grade(AssignmentSubmission $submission, Assignment $assignment, array $answers): array
    {
        $questions = $assignment->questions()->get()->keyBy('id');

        if ($questions->isEmpty() || empty($answers)) {
            return $this->emptyResult();
        }

        $earnedPoints     = 0.0;
        $totalPoints      = 0.0;
        $totalAutoGraded  = 0;
        $hasGradable      = false;

        foreach ($answers as $questionId => $studentAnswer) {
            $question = $questions->get($questionId);
            if (!$question || !$question->isAutoGraded()) {
                continue;
            }

            $hasGradable = true;
            $isCorrect   = $this->grader->gradeAnswer($studentAnswer, $question->correct_answer, $question->type);
            $points      = (float) $question->points;

            $totalPoints     += $points;
            $earnedPoints    += $isCorrect ? $points : 0.0;
            $totalAutoGraded += $isCorrect ? 1 : 0;
        }

        if (!$hasGradable) {
            return $this->emptyResult();
        }

        $maxScore = (float) ($assignment->max_score ?? 10);
        $score    = $totalPoints > 0
            ? round(($earnedPoints / $totalPoints) * $maxScore, 2)
            : null;

        return compact('earnedPoints', 'totalPoints', 'totalAutoGraded', 'score', 'hasGradable') +
            ['hasGradableQuestions' => $hasGradable];
    }

    private function emptyResult(): array
    {
        return [
            'earnedPoints'         => 0.0,
            'totalPoints'          => 0.0,
            'totalAutoGraded'      => 0,
            'score'                => null,
            'hasGradableQuestions' => false,
        ];
    }
}
