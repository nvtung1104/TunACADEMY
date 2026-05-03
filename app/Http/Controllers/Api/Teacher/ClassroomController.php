<?php
namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Resources\Classroom\ClassroomResource;
use App\Models\{Classroom, ClassroomStudent, LiveSession, User};
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function allClassrooms(Request $request)
    {
        $classrooms = Classroom::with('grade')
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'grade_id']);

        return $this->success($classrooms->map(fn($c) => [
            'id'       => $c->id,
            'name'     => $c->name,
            'grade_id' => $c->grade_id,
            'grade'    => $c->grade ? ['id' => $c->grade->id, 'level' => $c->grade->level, 'name' => $c->grade->name] : null,
        ]));
    }

    public function index(Request $request)
    {
        $uid = $request->user()->id;
        $classrooms = Classroom::with(['grade', 'schoolYear'])
            ->withCount('students')
            ->where(function ($q) use ($uid) {
                $q->whereHas('subjectTeachers', fn($q) => $q->where('teacher_id', $uid))
                  ->orWhere('homeroom_teacher_id', $uid);
            })->paginate(20);
        return ClassroomResource::collection($classrooms);
    }

    public function show(Request $request, Classroom $classroom)
    {
        $this->gate($request, $classroom);
        return $this->success(new ClassroomResource($classroom->load(['grade', 'schoolYear', 'students', 'subjectTeachers.teacher', 'subjectTeachers.subject'])));
    }

    public function addStudent(Request $request, Classroom $classroom)
    {
        $this->gate($request, $classroom);
        $request->validate(['student_id' => 'required|exists:users,id']);
        ClassroomStudent::firstOrCreate(
            ['classroom_id' => $classroom->id, 'student_id' => $request->student_id],
            ['joined_at' => today(), 'status' => 'active']
        );
        return $this->success(null, 'Thêm học sinh thành công');
    }

    public function removeStudent(Request $request, Classroom $classroom, User $student)
    {
        $this->gate($request, $classroom);
        ClassroomStudent::where(['classroom_id' => $classroom->id, 'student_id' => $student->id])
            ->update(['status' => 'transferred', 'left_at' => today()]);
        return $this->success(null, 'Xóa học sinh thành công');
    }

    public function roomStatus(Request $request, Classroom $classroom)
    {
        $this->gate($request, $classroom);

        $session = LiveSession::where('classroom_id', $classroom->id)
            ->where('is_permanent', true)
            ->orderByDesc('id')
            ->with(['participants' => fn($q) => $q->whereNull('left_at')->with('user:id,name,avatar')])
            ->first();

        return $this->success([
            'classroom' => ['id' => $classroom->id, 'name' => $classroom->name, 'room_code' => $classroom->room_code],
            'session'   => $session,
            'is_live'   => $session?->status === 'live',
        ]);
    }

    public function startRoom(Request $request, Classroom $classroom)
    {
        $this->gate($request, $classroom);

        $session = LiveSession::where('classroom_id', $classroom->id)
            ->where('is_permanent', true)
            ->orderByDesc('id')
            ->first();

        if ($session) {
            $session->update(['status' => 'live', 'started_at' => now(), 'ended_at' => null, 'teacher_id' => $request->user()->id]);
        } else {
            $session = LiveSession::create([
                'classroom_id'     => $classroom->id,
                'teacher_id'       => $request->user()->id,
                'title'            => 'Phòng học ' . $classroom->name,
                'room_code'        => $classroom->room_code,
                'is_permanent'     => true,
                'status'           => 'live',
                'started_at'       => now(),
                'duration_minutes' => 90,
                'max_participants' => 50,
            ]);
        }

        return $this->success($session, 'Đã mở phòng học');
    }

    public function endRoom(Request $request, Classroom $classroom)
    {
        $this->gate($request, $classroom);

        LiveSession::where('classroom_id', $classroom->id)
            ->where('is_permanent', true)
            ->where('status', 'live')
            ->update(['status' => 'scheduled', 'ended_at' => now()]);

        return $this->success(null, 'Đã kết thúc phòng học');
    }

    private function gate(Request $request, Classroom $classroom): void
    {
        abort_unless(
            $classroom->homeroom_teacher_id === $request->user()->id ||
            $classroom->subjectTeachers()->where('teacher_id', $request->user()->id)->exists(),
            403, 'Bạn không có quyền truy cập lớp này'
        );
    }
}
