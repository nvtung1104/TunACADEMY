<?php

namespace App\Http\Middleware;

use App\Models\Classroom;
use App\Models\ClassroomStudent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClassroomAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $classroomId = $request->route('classroom_id') ?? $request->route('classroom');

        if (!$classroomId) {
            return $next($request);
        }

        $classroom = Classroom::find($classroomId);

        if (!$classroom) {
            return response()->json(['message' => 'Classroom not found.'], 404);
        }

        $user = $request->user();

        // Check if user is teacher of this classroom
        $isTeacher = $classroom->teachers()->where('user_id', $user->id)->exists();

        // Check if user is student in this classroom
        $isStudent = ClassroomStudent::where([
            ['classroom_id', '=', $classroomId],
            ['user_id', '=', $user->id],
        ])->exists();

        if (!$isTeacher && !$isStudent) {
            return response()->json(['message' => 'You do not have access to this classroom.'], 403);
        }

        $request->merge(['classroom' => $classroom]);

        return $next($request);
    }
}
