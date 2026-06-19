<?php

namespace App\Http\Middleware;

use App\Models\Classroom;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ClassroomAccess
{
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

        $isTeacher = DB::table('classroom_subject_teachers')
            ->where('classroom_id', $classroomId)
            ->where('teacher_id', $user->id)
            ->exists();

        $isStudent = $isTeacher ? false : DB::table('classroom_students')
            ->where('classroom_id', $classroomId)
            ->where('student_id', $user->id)
            ->where('status', 'active')
            ->exists();

        if (!$isTeacher && !$isStudent) {
            return response()->json(['message' => 'You do not have access to this classroom.'], 403);
        }

        $request->merge(['classroom' => $classroom]);

        return $next($request);
    }
}
