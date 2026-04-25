<?php
namespace App\Http\Resources\Lesson;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'content'     => $this->content,
            'video_path'  => $this->video_path,
            'audio_path'  => $this->audio_path,
            'order_index' => $this->order_index,
            'view_count'  => $this->view_count,
            'status'      => $this->status,
            'published_at'=> $this->published_at?->toISOString(),
            'classroom'   => $this->whenLoaded('classroom', fn() => [
                'id'   => $this->classroom->id,
                'name' => $this->classroom->name,
            ]),
            'subject'     => $this->whenLoaded('subject', fn() => [
                'id'    => $this->subject->id,
                'name'  => $this->subject->name,
                'color' => $this->subject->color,
            ]),
            'teacher'     => $this->whenLoaded('teacher', fn() => new UserResource($this->teacher)),
            'materials'   => $this->whenLoaded('materials', fn() => $this->materials->map(fn($m) => [
                'id'             => $m->id,
                'file_name'      => $m->file_name,
                'file_type'      => $m->file_type,
                'mime_type'      => $m->mime_type,
                'file_size'      => $m->file_size,
                'download_count' => $m->download_count,
                'url'            => $m->url,
            ])),
            'slides'      => $this->whenLoaded('slides', fn() => $this->slides->map(fn($s) => [
                'id'          => $s->id,
                'title'       => $s->title,
                'content'     => $s->content,
                'image_path'  => $s->image_path,
                'order_index' => $s->order_index,
            ])),
            'created_at'  => $this->created_at->toISOString(),
        ];
    }
}
