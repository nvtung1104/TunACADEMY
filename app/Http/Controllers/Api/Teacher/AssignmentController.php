<?php
namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreAssignmentRequest;
use App\Models\{Assignment, AssignmentSubmission};
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        $assignments = Assignment::with(['classroom', 'subject'])
            ->where('teacher_id', $request->user()->id)
            ->latest()->paginate(20);
        return $this->success($assignments);
    }

    public function store(StoreAssignmentRequest $request)
    {
        $assignment = Assignment::create([...$request->validated(), 'teacher_id' => $request->user()->id]);
        return $this->success($assignment->load(['classroom', 'subject']), 'Tạo bài tập thành công', 201);
    }

    public function show(Request $request, Assignment $assignment)
    {
        $this->gate($request, $assignment);
        return $this->success($assignment->load(['classroom', 'subject', 'submissions.student']));
    }

    public function update(StoreAssignmentRequest $request, Assignment $assignment)
    {
        $this->gate($request, $assignment);
        $assignment->update($request->validated());
        return $this->success($assignment->fresh(), 'Cập nhật thành công');
    }

    public function destroy(Request $request, Assignment $assignment)
    {
        $this->gate($request, $assignment);
        $assignment->delete();
        return $this->success(null, 'Xóa bài tập thành công');
    }

    public function submissions(Request $request, Assignment $assignment)
    {
        $this->gate($request, $assignment);
        $submissions = $assignment->submissions()->with('student')->latest('submitted_at')->paginate(30);
        return $this->success($submissions);
    }

    public function grade(Request $request, Assignment $assignment, AssignmentSubmission $submission)
    {
        $this->gate($request, $assignment);
        $request->validate([
            'score'    => "required|numeric|min:0|max:{$assignment->max_score}",
            'feedback' => 'nullable|string|max:2000',
        ]);
        $submission->update([
            'score'     => $request->score,
            'feedback'  => $request->feedback,
            'graded_at' => now(),
            'graded_by' => $request->user()->id,
            'status'    => 'graded',
        ]);
        return $this->success($submission->fresh(), 'Chấm điểm thành công');
    }

    private function gate(Request $request, Assignment $assignment): void
    {
        abort_unless($assignment->teacher_id === $request->user()->id, 403, 'Không có quyền');
    }
}
