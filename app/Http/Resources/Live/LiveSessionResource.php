<?php
namespace App\Http\Resources\Live;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User\UserResource;
class LiveSessionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                => $this->id,
            'title'             => $this->title,
            'description'       => $this->description,
            'room_code'         => $this->room_code,
            'scheduled_at'      => $this->scheduled_at->toISOString(),
            'started_at'        => $this->started_at?->toISOString(),
            'ended_at'          => $this->ended_at?->toISOString(),
            'duration_minutes'  => $this->duration_minutes,
            'max_participants'  => $this->max_participants,
            'allow_screen_share'=> $this->allow_screen_share,
            'allow_student_mic' => $this->allow_student_mic,
            'allow_student_cam' => $this->allow_student_cam,
            'chat_enabled'      => $this->chat_enabled,
            'recording_enabled' => $this->recording_enabled,
            'status'            => $this->status,
            'is_live'           => $this->isLive(),
            'classroom'         => $this->whenLoaded('classroom', fn() => ['id' => $this->classroom->id, 'name' => $this->classroom->name]),
            'subject'           => $this->whenLoaded('subject', fn() => ['id' => $this->subject->id, 'name' => $this->subject->name]),
            'teacher'           => $this->whenLoaded('teacher', fn() => new UserResource($this->teacher)),
            'created_at'        => $this->created_at->toISOString(),
        ];
    }
}
