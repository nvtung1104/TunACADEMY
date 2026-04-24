<?php

namespace App\Http\Middleware;

use App\Models\Exam;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExamAccess
{
    /**
     * Handle an incoming request.
     */
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

        // Check if user is teacher of the exam's classroom
        $isTeacher = $exam->classroom->teachers()->where('user_id', $user->id)->exists();

        // Check if user is student in the exam's classroom
        $isStudent = $exam->classroom->students()->where('user_id', $user->id)->exists();

        if (!$isTeacher && !$isStudent) {
            return response()->json(['message' => 'You do not have access to this exam.'], 403);
        }

        // Additional check for students - exam must be published
        if ($isStudent && !$exam->is_published) {
            return response()->json(['message' => 'This exam is not available yet.'], 403);
        }

        $request->merge(['exam' => $exam]);

        return $next($request);
    }
}
