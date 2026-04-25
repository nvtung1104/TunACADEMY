<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubjectRequest;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $subjects = Subject::withCount(['lessons', 'exams', 'assignments'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->orderBy('name')
            ->get();

        return $this->success($subjects);
    }

    public function store(StoreSubjectRequest $request)
    {
        $subject = Subject::create($request->validated());

        return $this->success($subject, 'Tạo môn học thành công', 201);
    }

    public function show(Subject $subject)
    {
        return $this->success($subject->loadCount(['lessons', 'exams', 'assignments']));
    }

    public function update(StoreSubjectRequest $request, Subject $subject)
    {
        $subject->update($request->validated());

        return $this->success($subject->fresh(), 'Cập nhật môn học thành công');
    }

    public function destroy(Subject $subject)
    {
        abort_if(
            $subject->lessons()->exists() || $subject->exams()->exists() || $subject->assignments()->exists(),
            422,
            'Không thể xóa môn học đang có dữ liệu'
        );

        $subject->delete();

        return $this->success(null, 'Xóa môn học thành công');
    }
}
