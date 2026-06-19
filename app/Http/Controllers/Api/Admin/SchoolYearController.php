<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSchoolYearRequest;
use App\Models\SchoolYear;
use Illuminate\Http\Request;

class SchoolYearController extends Controller
{
    public function index(Request $request)
    {
        $schoolYears = SchoolYear::withCount('classrooms')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderByDesc('start_date')
            ->get();

        return $this->success($schoolYears);
    }

    public function store(StoreSchoolYearRequest $request)
    {
        $schoolYear = SchoolYear::create($request->validated());

        return $this->success($schoolYear, 'Tạo năm học thành công', 201);
    }

    public function show(SchoolYear $schoolYear)
    {
        return $this->success($schoolYear->loadCount('classrooms'));
    }

    public function update(StoreSchoolYearRequest $request, SchoolYear $schoolYear)
    {
        $schoolYear->update($request->validated());

        return $this->success($schoolYear->fresh(), 'Cập nhật năm học thành công');
    }

    public function destroy(SchoolYear $schoolYear)
    {
        abort_if($schoolYear->classrooms()->exists(), 422, 'Không thể xóa năm học đang có lớp học');

        $schoolYear->delete();

        return $this->success(null, 'Xóa năm học thành công');
    }
}
