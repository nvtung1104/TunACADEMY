<?php
namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\Classroom\ClassroomResource;
use App\Models\{Classroom, LiveSession};
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function index(Request $request)
    {
        $classrooms = $request->user()->classrooms()->with(['grade', 'schoolYear', 'homeroomTeacher'])->get();
        return ClassroomResource::collection($classrooms);
    }

    public function show(Request $request, Classroom $classroom)
    {
        abort_unless($request->user()->classrooms()->where('classrooms.id', $classroom->id)->exists(), 403, 'Bạn không học lớp này');
        return $this->success(new ClassroomResource($classroom->load(['grade', 'schoolYear', 'homeroomTeacher', 'subjectTeachers.teacher', 'subjectTeachers.subject'])));
    }

    public function myRooms(Request $request)
    {
        $classroomIds = $request->user()->classrooms()->pluck('classrooms.id');

        $sessions = LiveSession::with(['classroom:id,name,room_code', 'teacher:id,name,avatar'])
            ->where(function ($q) use ($classroomIds) {
                $q->whereIn('classroom_id', $classroomIds)
                  ->orWhereNull('classroom_id');
            })
            ->whereIn('status', ['scheduled', 'live'])
            ->orderByRaw("FIELD(status, 'live', 'scheduled')")
            ->orderByDesc('created_at')
            ->get()
            ->unique(fn($s) => $s->classroom_id ?? 'public_' . $s->id)
            ->values();

        return $this->success($sessions);
    }
}
