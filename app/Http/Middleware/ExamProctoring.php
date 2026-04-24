<?php

namespace App\Http\Middleware;

use App\Models\ExamAttempt;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExamProctoring
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $attemptId = $request->route('attempt_id') ?? $request->route('exam_attempt');

        if (!$attemptId) {
            return $next($request);
        }

        $attempt = ExamAttempt::find($attemptId);

        if (!$attempt) {
            return response()->json(['message' => 'Exam attempt not found.'], 404);
        }

        // Check if attempt belongs to authenticated user
        if ($attempt->user_id !== $request->user()->id) {
            return response()->json(['message' => 'This is not your exam attempt.'], 403);
        }

        // Check if exam requires proctoring
        if ($attempt->exam->require_proctoring) {
            // Verify proctoring log exists
            $proctoringLog = $attempt->proctoringLog;

            if (!$proctoringLog || !$proctoringLog->is_verified) {
                return response()->json(['message' => 'Proctoring verification required.'], 403);
            }

            // Check if proctoring session is still active
            if ($proctoringLog->ended_at && $proctoringLog->ended_at < now()) {
                return response()->json(['message' => 'Proctoring session has expired.'], 403);
            }
        }

        // Check if attempt is still in progress
        if ($attempt->status !== 'in_progress') {
            return response()->json(['message' => 'This exam attempt is not in progress.'], 403);
        }

        // Check exam time limit
        if ($attempt->exam->duration) {
            $timeElapsed = $attempt->started_at->diffInMinutes(now());

            if ($timeElapsed > $attempt->exam->duration) {
                return response()->json(['message' => 'Exam time limit exceeded.'], 403);
            }
        }

        $request->merge(['examAttempt' => $attempt]);

        return $next($request);
    }
}
