<?php
namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Resources\Classroom\ClassroomResource;
use App\Models\{Classroom, ClassroomStudent, User};
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function index(Request $request)
    {
        $uid = $request->user()->id;
        $classrooms = Classroom::with(['grade', 'schoolYear'])
            ->where(function ($q) use ($uid) {
                $q->whereHas('subjectTeachers', fn($q) => $q->where('teacher_id', $uid))
                  ->orWhere('homeroom_teacher_id', $uid);
            })->paginate(20);
        return ClassroomResource::collection($classrooms);
    }

    public function show(Request $request, Classroom $classroom)
    {
        $this->gate($request, $classroom);
        return $this->success(new ClassroomResource($classroom->load(['grade', 'schoolYear', 'students'])));
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

    private function gate(Request $request, Classroom $classroom): void
    {
        abort_unless(
            $classroom->homeroom_teacher_id === $request->user()->id ||
            $classroom->subjectTeachers()->where('teacher_id', $request->user()->id)->exists(),
            403, 'Bạn không có quyền truy cập lớp này'
        );
    }
}
