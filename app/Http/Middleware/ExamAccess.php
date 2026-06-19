<?php

namespace App\Http\Middleware;

use App\Models\Exam;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ExamAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $examId = $request->route('exam_id') ?? $request->route('exam');

        if (!$examId) {
            return $next($request);
        }

        $exam = Exam::find($examId);

        if (!$exam) {
            return response()->json(['message' => 'Exam not found.'], 404);
        }

        $user = $request->user();

        $isTeacher = DB::table('classroom_subject_teachers')
            ->where('classroom_id', $exam->classroom_id)
            ->where('teacher_id', $user->id)
            ->exists();

        $isStudent = $isTeacher ? false : DB::table('classroom_students')
            ->where('classroom_id', $exam->classroom_id)
            ->where('student_id', $user->id)
            ->where('status', 'active')
            ->exists();

        if (!$isTeacher && !$isStudent) {
            return response()->json(['message' => 'You do not have access to this exam.'], 403);
        }

        if ($isStudent && $exam->status !== 'published') {
            return response()->json(['message' => 'This exam is not available yet.'], 403);
        }

        $request->merge(['exam' => $exam]);

        return $next($request);
    }
}
