<?php
namespace App\Http\Resources\Exam;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                 => $this->id,
            'title'              => $this->title,
            'description'        => $this->description,
            'duration_minutes'   => $this->duration_minutes,
            'opened_at'          => $this->opened_at?->toISOString(),
            'closed_at'          => $this->closed_at?->toISOString(),
            'shuffle_questions'  => $this->shuffle_questions,
            'shuffle_options'    => $this->shuffle_options,
            'proctoring_enabled' => $this->proctoring_enabled,
            'max_violations'     => $this->max_violations,
            'show_result'        => $this->show_result,
            'allow_retake'       => $this->allow_retake,
            'status'             => $this->status,
            'question_count'     => $this->questions_count ?? ($this->relationLoaded('questions') ? $this->questions->count() : 0),
            'classroom'          => $this->whenLoaded('classroom', fn() => [
                'id'   => $this->classroom->id,
                'name' => $this->classroom->name,
            ]),
            'subject'            => $this->whenLoaded('subject', fn() => [
                'id'    => $this->subject->id,
                'name'  => $this->subject->name,
                'color' => $this->subject->color,
            ]),
            'teacher'            => $this->whenLoaded('teacher', fn() => new UserResource($this->teacher)),
            'questions'          => $this->whenLoaded('questions', fn() => $this->questions->map(fn($q) => [
                'id'             => $q->id,
                'type'           => $q->type,
                'content'        => $q->content,
                'options'        => $q->options,
                'correct_answer' => $q->correct_answer,
                'points'         => $q->points,
                'explanation'    => $q->explanation,
                'order_index'    => $q->order_index,
                'chapter_tag'    => $q->chapter_tag,
                'audio_path'     => $q->audio_path,
            ])),
            'created_at'         => $this->created_at->toISOString(),
        ];
    }
}
