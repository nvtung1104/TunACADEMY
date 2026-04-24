<?php

namespace App\Http\Middleware;

use App\Models\Assignment;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AssignmentAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $assignmentId = $request->route('assignment_id') ?? $request->route('assignment');

        if (!$assignmentId) {
            return $next($request);
        }

        $assignment = Assignment::find($assignmentId);

        if (!$assignment) {
            return response()->json(['message' => 'Assignment not found.'], 404);
        }

        $user = $request->user();

        // Check if user is teacher of the assignment's classroom
        $isTeacher = $assignment->classroom->teachers()->where('user_id', $user->id)->exists();

        // Check if user is student in the assignment's classroom
        $isStudent = $assignment->classroom->students()->where('user_id', $user->id)->exists();

        if (!$isTeacher && !$isStudent) {
            return response()->json(['message' => 'You do not have access to this assignment.'], 403);
        }

        // Additional check for students - assignment must be published
        if ($isStudent && !$assignment->is_published) {
            return response()->json(['message' => 'This assignment is not available yet.'], 403);
        }

        $request->merge(['assignment' => $assignment]);

        return $next($request);
    }
}
