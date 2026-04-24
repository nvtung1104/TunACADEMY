<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClassroomRequest;
use App\Http\Resources\Classroom\ClassroomResource;
use App\Models\Classroom;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function index(Request $request)
    {
        $classrooms = Classroom::with(['grade', 'schoolYear', 'homeroomTeacher'])
            ->when($request->school_year_id, fn($q) => $q->where('school_year_id', $request->school_year_id))
            ->when($request->grade_id, fn($q) => $q->where('grade_id', $request->grade_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
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
}
