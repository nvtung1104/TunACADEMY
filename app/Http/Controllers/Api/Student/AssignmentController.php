<?php
namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\{Assignment, AssignmentSubmission};
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        $classroomIds = $request->user()->classrooms()->pluck('classrooms.id');
        $assignments = Assignment::with(['subject', 'classroom'])
            ->whereIn('classroom_id', $classroomIds)
            ->published()
            ->where('visibility', '!=', 'private')
            ->latest('deadline')->paginate(20);
        return $this->success($assignments);
    }

    public function show(Request $request, Assignment $assignment)
    {
        $this->checkAccess($request, $assignment);
        $submission = $assignment->submissions()->where('student_id', $request->user()->id)->first();
        return $this->success(['assignment' => $assignment->load(['subject', 'questions']), 'submission' => $submission]);
    }

    public function submit(Request $request, Assignment $assignment)
    {
        $this->checkAccess($request, $assignment);
        abort_if(!$assignment->allow_late && $assignment->isOverdue(), 422, 'Đã quá hạn nộp bài');
        abort_if($assignment->submissions()->where('student_id', $request->user()->id)->exists(), 422, 'Bạn đã nộp bài này rồi');
        $request->validate(['content' => 'nullable|string', 'files.*' => 'nullable|file|max:10240']);

        $filePaths = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $filePaths[] = $file->store("submissions/{$assignment->id}", 'public');
            }
        }
        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id'    => $request->user()->id,
            'content'       => $request->content,
            'file_paths'    => $filePaths,
            'submitted_at'  => now(),
            'is_late'       => $assignment->isOverdue(),
        ]);
        return $this->success($submission, 'Nộp bài thành công', 201);
    }

    private function checkAccess(Request $request, Assignment $assignment): void
    {
        $classroomIds = $request->user()->classrooms()->pluck('classrooms.id');
        abort_unless($classroomIds->contains($assignment->classroom_id), 403, 'Không có quyền truy cập');
    }
}
