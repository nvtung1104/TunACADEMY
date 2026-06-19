<?php
namespace App\Http\Resources\Classroom;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassroomResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'room_code'           => $this->room_code,
            'max_students'        => $this->max_students,
            'status'              => $this->status,
            'homeroom_teacher_id' => $this->homeroom_teacher_id,
            'students_count'   => $this->students_count ?? ($this->relationLoaded('students') ? $this->students->count() : null),
            'lessons_count'    => $this->lessons_count ?? ($this->relationLoaded('lessons') ? $this->lessons->count() : null),
            'assignments_count' => $this->assignments_count ?? ($this->relationLoaded('assignments') ? $this->assignments->count() : null),
            'exams_count'      => $this->exams_count ?? ($this->relationLoaded('exams') ? $this->exams->count() : null),
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
            'subject_teachers' => $this->whenLoaded('subjectTeachers', fn() => $this->subjectTeachers->map(fn($st) => [
                'teacher' => $st->teacher ? ['id' => $st->teacher->id, 'name' => $st->teacher->name] : null,
                'subject' => $st->subject ? ['id' => $st->subject->id, 'name' => $st->subject->name, 'color' => $st->subject->color ?? null] : null,
            ])),
            'created_at'       => $this->created_at->toISOString(),
        ];
    }
}
