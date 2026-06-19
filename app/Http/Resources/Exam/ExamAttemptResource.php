<?php

namespace App\Http\Resources\Exam;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User\UserResource;

class ExamAttemptResource extends JsonResource
{
    public function toArray($request): array
    {
        $isTeacherView = $request && $request->route()?->getPrefix() && str_contains($request->route()->getPrefix(), 'teacher');

        return [
            'id'                => $this->id,
            'started_at'        => $this->started_at->toISOString(),
            'submitted_at'      => $this->submitted_at?->toISOString(),
            'expires_at'        => $this->expires_at->toISOString(),
            'remaining_seconds' => $this->remainingSeconds(),
            'score'             => $this->score,
            'total_correct'     => $this->total_correct,
            'ai_evaluation'     => $isTeacherView ? $this->ai_evaluation : $this->stripCorrectAnswers($this->ai_evaluation),
            'ai_evaluated_at'   => $this->ai_evaluated_at?->toISOString(),
            'status'            => $this->status,
            'violation_count'   => $this->violation_count,
            'student'           => $this->whenLoaded('student', fn() => new UserResource($this->student)),
            'answers'           => $this->whenLoaded('answers', fn() => $this->answers->map(fn($a) => [
                'question_id'   => $a->question_id,
                'answer'        => $a->answer_content,
                'is_correct'    => $a->is_correct,
                'points_earned' => $a->score_earned,
                'question'      => $a->question ? [
                    'content'        => $a->question->content,
                    'options'        => $a->question->options,
                    'type'           => $a->question->type,
                    'correct_answer' => $a->question->correct_answer,
                    'explanation'    => $a->question->explanation,
                ] : null,
            ])),
        ];
    }

    private function stripCorrectAnswers(?array $aiEvaluation): ?array
    {
        if (!$aiEvaluation || !isset($aiEvaluation['answers'])) return $aiEvaluation;

        $aiEvaluation['answers'] = array_map(function ($row) {
            unset($row['correct_answer']);
            return $row;
        }, $aiEvaluation['answers']);

        return $aiEvaluation;
    }
}
