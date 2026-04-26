<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Exam;
use App\Models\Lesson;
use App\Models\LiveSession;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function lessons(Request $request)
    {
        $query = Lesson::with(['classroom.grade', 'subject:id,name,color', 'teacher:id,name'])
            ->when($request->subject_id, fn($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->status,     fn($q) => $q->where('status', $request->status))
            ->when($request->search,     fn($q) => $q->where('title', 'like', "%{$request->search}%"));
        return response()->json($query->orderByDesc('created_at')->paginate(15));
    }

    public function deleteLesson(Lesson $lesson)
    {
        $lesson->delete();
        return $this->success(null, 'Đã xóa bài học');
    }

    public function exams(Request $request)
    {
        $query = Exam::with(['classroom.grade', 'subject:id,name,color', 'teacher:id,name'])
            ->when($request->subject_id, fn($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->status,     fn($q) => $q->where('status', $request->status))
            ->when($request->search,     fn($q) => $q->where('title', 'like', "%{$request->search}%"));
        return response()->json($query->orderByDesc('created_at')->paginate(15));
    }

    public function deleteExam(Exam $exam)
    {
        $exam->delete();
        return $this->success(null, 'Đã xóa đề thi');
    }

    public function assignments(Request $request)
    {
        $query = Assignment::with(['classroom.grade', 'subject:id,name,color', 'teacher:id,name'])
            ->when($request->subject_id, fn($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->status,     fn($q) => $q->where('status', $request->status))
            ->when($request->search,     fn($q) => $q->where('title', 'like', "%{$request->search}%"));
        return response()->json($query->orderByDesc('created_at')->paginate(15));
    }

    public function deleteAssignment(Assignment $assignment)
    {
        $assignment->delete();
        return $this->success(null, 'Đã xóa bài tập');
    }

    public function liveSessions(Request $request)
    {
        $query = LiveSession::with(['classroom.grade', 'subject:id,name,color', 'teacher:id,name'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->where('title', 'like', "%{$request->search}%"));
        return response()->json($query->orderByDesc('created_at')->paginate(15));
    }

    public function deleteLiveSession(LiveSession $liveSession)
    {
        $liveSession->delete();
        return $this->success(null, 'Đã xóa phòng học');
    }
}
