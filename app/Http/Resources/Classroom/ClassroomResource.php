<?php
namespace App\Http\Resources\Classroom;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassroomResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'max_students'     => $this->max_students,
            'status'           => $this->status,
            'student_count'    => $this->whenLoaded('students', fn() => $this->students->count()),
            'grade'            => $this->whenLoaded('grade', fn() => [
                'id'    => $this->grade->id,
                'level' => $this->grade->level,
                'name'  => $this->grade->name,
            ]),
            'school_year'      => $this->whenLoaded('schoolYear', fn() => [
                'id'     => $this->schoolYear->id,
                'name'   => $this->schoolYear->name,
                'status' => $this->schoolYear->status,
            ]),
            'homeroom_teacher' => $this->whenLoaded('homeroomTeacher', fn() => $this->homeroomTeacher
                ? new UserResource($this->homeroomTeacher)
                : null
            ),
            'students'         => $this->whenLoaded('students', fn() => UserResource::collection($this->students)),
            'created_at'       => $this->created_at->toISOString(),
        ];
    }
}
