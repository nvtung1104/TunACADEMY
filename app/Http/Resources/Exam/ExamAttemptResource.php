<?php
namespace App\Http\Resources\Exam;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User\UserResource;
class ExamAttemptResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'started_at'       => $this->started_at->toISOString(),
            'submitted_at'     => $this->submitted_at?->toISOString(),
            'expires_at'       => $this->expires_at->toISOString(),
            'remaining_seconds'=> $this->remainingSeconds(),
            'score'            => $this->score,
            'total_correct'    => $this->total_correct,
            'status'           => $this->status,
            'violation_count'  => $this->violation_count,
            'student'          => $this->whenLoaded('student', fn() => new UserResource($this->student)),
            'answers'          => $this->whenLoaded('answers', fn() => $this->answers->map(fn($a) => [
                'question_id'   => $a->question_id,
                'answer'        => $a->answer,
                'is_correct'    => $a->is_correct,
                'points_earned' => $a->points_earned,
            ])),
        ];
    }
}
