<?php
namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreAssignmentRequest;
use App\Models\{Assignment, AssignmentSubmission, Classroom, User};
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
        $data = $request->validated();
        $data['teacher_id'] = $request->user()->id;
        $data['status'] = ($data['visibility'] ?? 'private') === 'private' ? 'draft' : 'published';
        $assignment = Assignment::create($data);
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
        $data = $request->validated();
        if (isset($data['visibility'])) {
            $data['status'] = $data['visibility'] === 'private' ? 'draft' : 'published';
        }
        $assignment->update($data);
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

    public function share(Request $request, Assignment $assignment)
    {
        $this->gate($request, $assignment);

        $data = $request->validate([
            'visibility'             => 'required|in:public,private,class,selected',
            'target_classroom_ids'   => 'array',
            'target_classroom_ids.*' => 'integer|exists:classrooms,id',
            'target_student_ids'     => 'array',
            'target_student_ids.*'   => 'integer|exists:users,id',
        ]);

        $assignment->update(['visibility' => $data['visibility']]);

        if ($data['visibility'] === 'selected') {
            $assignment->shares()->delete();

            foreach ($data['target_classroom_ids'] ?? [] as $classroomId) {
                $assignment->shares()->create(['target_type' => Classroom::class, 'target_id' => $classroomId]);
            }

            foreach ($data['target_student_ids'] ?? [] as $userId) {
                $assignment->shares()->create(['target_type' => User::class, 'target_id' => $userId]);
            }
        } else {
            $assignment->shares()->delete();
        }

        return $this->success(null, 'Cập nhật chia sẻ thành công');
    }

    public function uploadThumbnail(Request $request, Assignment $assignment)
    {
        $this->gate($request, $assignment);
        $request->validate(['thumbnail' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120']);
        if ($assignment->thumbnail) \Illuminate\Support\Facades\Storage::disk('public')->delete($assignment->thumbnail);
        $path = $request->file('thumbnail')->store('thumbnails/assignments', 'public');
        $assignment->update(['thumbnail' => $path]);
        return $this->success(['thumbnail_url' => asset("storage/{$path}")], 'Cập nhật ảnh bìa thành công');
    }

    private function gate(Request $request, Assignment $assignment): void
    {
        abort_unless($assignment->teacher_id === $request->user()->id, 403, 'Không có quyền');
    }
}
