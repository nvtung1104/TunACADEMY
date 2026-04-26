<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\Lesson;
use App\Models\Subject;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function grades()
    {
        $grades = Grade::orderBy('order_index')->get(['id', 'level', 'name']);
        return response()->json(['data' => $grades]);
    }

    public function subjects()
    {
        $subjects = Subject::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'color', 'icon']);
        return response()->json(['data' => $subjects]);
    }

    public function lessons(Request $request)
    {
        $query = Lesson::published()
            ->with([
                'classroom:id,name,grade_id',
                'classroom.grade:id,name,level',
                'subject:id,name,code,color',
                'teacher:id,name',
            ])
            ->when($request->grade_id, fn($q) => $q->whereHas(
                'classroom', fn($q) => $q->where('grade_id', $request->grade_id)
            ))
            ->when($request->subject_id, fn($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->search, fn($q) => $q->where('title', 'like', "%{$request->search}%"));

        return response()->json($query->orderByDesc('published_at')->paginate(12));
    }

    public function exams(Request $request)
    {
        $query = Exam::published()
            ->with([
                'classroom:id,name,grade_id',
                'classroom.grade:id,name,level',
                'subject:id,name,code,color',
                'teacher:id,name',
            ])
            ->when($request->grade_id, fn($q) => $q->whereHas(
                'classroom', fn($q) => $q->where('grade_id', $request->grade_id)
            ))
            ->when($request->subject_id, fn($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->search, fn($q) => $q->where('title', 'like', "%{$request->search}%"));

        return response()->json($query->orderByDesc('opened_at')->paginate(12));
    }

    public function assignments(Request $request)
    {
        $query = Assignment::published()
            ->with([
                'classroom:id,name,grade_id',
                'classroom.grade:id,name,level',
                'subject:id,name,code,color',
                'teacher:id,name',
            ])
            ->when($request->grade_id, fn($q) => $q->whereHas(
                'classroom', fn($q) => $q->where('grade_id', $request->grade_id)
            ))
            ->when($request->subject_id, fn($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->search, fn($q) => $q->where('title', 'like', "%{$request->search}%"));

        return response()->json($query->orderByDesc('deadline')->paginate(12));
    }

    public function lesson($id)
    {
        $lesson = Lesson::published()
            ->with([
                'classroom:id,name,grade_id',
                'classroom.grade:id,name,level',
                'subject:id,name,code,color',
                'teacher:id,name',
                'materials',
            ])
            ->findOrFail($id);

        $lesson->increment('view_count');

        return response()->json(['data' => $lesson]);
    }

    public function exam($id)
    {
        $exam = Exam::published()
            ->with([
                'classroom:id,name,grade_id',
                'classroom.grade:id,name,level',
                'subject:id,name,code,color',
                'teacher:id,name',
            ])
            ->findOrFail($id);

        return response()->json(['data' => $exam]);
    }

    public function assignment($id)
    {
        $assignment = Assignment::published()
            ->with([
                'classroom:id,name,grade_id',
                'classroom.grade:id,name,level',
                'subject:id,name,code,color',
                'teacher:id,name',
            ])
            ->findOrFail($id);

        return response()->json(['data' => $assignment]);
    }

    public function classrooms(Request $request)
    {
        $query = Classroom::with([
                'grade:id,name,level',
                'schoolYear:id,name',
                'homeroomTeacher:id,name',
            ])
            ->where('status', 'active')
            ->when($request->grade_id, fn($q) => $q->where('grade_id', $request->grade_id))
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->withCount('students');

        return response()->json($query->orderBy('name')->paginate(12));
    }
}
