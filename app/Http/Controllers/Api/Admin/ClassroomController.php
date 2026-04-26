<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClassroomRequest;
use App\Http\Resources\Classroom\ClassroomResource;
use App\Models\{Classroom, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClassroomController extends Controller
{
    public function index(Request $request)
    {
        $classrooms = Classroom::with(['grade', 'schoolYear', 'homeroomTeacher'])
            ->withCount('students')
            ->when($request->school_year_id, fn($q) => $q->where('school_year_id', $request->school_year_id))
            ->when($request->grade_id, fn($q) => $q->where('grade_id', $request->grade_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->latest()->paginate($request->input('per_page', 20));

        return ClassroomResource::collection($classrooms);
    }

    public function store(StoreClassroomRequest $request)
    {
        $classroom = Classroom::create($request->validated());

        return $this->success(new ClassroomResource($classroom->load(['grade', 'schoolYear'])), 'Tạo lớp thành công', 201);
    }

    public function show(Classroom $classroom)
    {
        return $this->success(new ClassroomResource($classroom->load(['grade', 'schoolYear', 'homeroomTeacher', 'students'])));
    }

    public function update(StoreClassroomRequest $request, Classroom $classroom)
    {
        $classroom->update($request->validated());

        return $this->success(new ClassroomResource($classroom->fresh(['grade', 'schoolYear'])), 'Cập nhật thành công');
    }

    public function destroy(Classroom $classroom)
    {
        $classroom->delete();

        return $this->success(null, 'Xóa lớp thành công');
    }

    public function addStudent(Request $request, Classroom $classroom)
    {
        $request->validate(['student_id' => 'required|exists:users,id']);

        $student = User::findOrFail($request->student_id);
        abort_unless($student->hasRole('student'), 422, 'Người dùng không phải học sinh');

        $alreadyActive = $classroom->students()->where('classroom_students.student_id', $student->id)->exists();
        abort_if($alreadyActive, 422, 'Học sinh đã có trong lớp');

        $classroom->students()->attach($student->id, ['joined_at' => now(), 'status' => 'active']);

        return $this->success(null, 'Đã thêm học sinh vào lớp');
    }

    public function removeStudent(Classroom $classroom, $student)
    {
        abort_unless(
            $classroom->students()->where('classroom_students.student_id', $student)->exists(),
            404,
            'Học sinh không có trong lớp'
        );

        $classroom->students()->updateExistingPivot($student, ['status' => 'inactive', 'left_at' => now()]);

        return $this->success(null, 'Đã xóa học sinh khỏi lớp');
    }

    public function uploadCover(Request $request, Classroom $classroom)
    {
        $request->validate(['cover_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120']);
        if ($classroom->cover_image) Storage::disk('public')->delete($classroom->cover_image);
        $path = $request->file('cover_image')->store('classrooms', 'public');
        $classroom->update(['cover_image' => $path]);
        return $this->success(['cover_url' => asset("storage/{$path}")], 'Cập nhật ảnh bìa thành công');
    }
}
