<?php
namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\Classroom\ClassroomResource;
use App\Models\Classroom;
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
}
