<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassroomSubjectTeacher;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function mySubjects(Request $request)
    {
        $assignments = ClassroomSubjectTeacher::with([
                'subject:id,name,code,color,icon',
                'classroom:id,name,grade_id',
                'classroom.grade:id,name,level',
                'schoolYear:id,name',
            ])
            ->where('teacher_id', $request->user()->id)
            ->get()
            ->groupBy('subject_id')
            ->map(function ($rows, $subjectId) {
                $subject = $rows->first()->subject;
                return [
                    'subject'    => $subject,
                    'classrooms' => $rows->map(fn($r) => [
                        'id'          => $r->classroom?->id,
                        'name'        => $r->classroom?->name,
                        'grade'       => $r->classroom?->grade?->name,
                        'school_year' => $r->schoolYear?->name,
                    ])->values(),
                ];
            })
            ->values();

        return $this->success($assignments);
    }
}
