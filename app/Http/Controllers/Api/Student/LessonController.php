<?php
namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\Lesson\LessonResource;
use App\Models\{Lesson, LessonMaterial, StudyProgress};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LessonController extends Controller
{
    public function index(Request $request)
    {
        $classroomIds = $request->user()->classroomStudents()->pluck('classroom_id');
        $perPage = min((int) ($request->per_page ?? 20), 200);
        $lessons = Lesson::with(['subject', 'teacher'])
            ->whereIn('classroom_id', $classroomIds)
            ->published()
            ->when($request->classroom_id, fn($q) => $q->where('classroom_id', $request->classroom_id))
            ->when($request->subject_id, fn($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->search, fn($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->orderBy('subject_id')->orderBy('order_index')
            ->paginate($perPage);
        return LessonResource::collection($lessons);
    }

    public function show(Request $request, Lesson $lesson)
    {
        $this->checkAccess($request, $lesson);
        $lesson->increment('view_count');
        $progress = StudyProgress::firstOrCreate(['lesson_id' => $lesson->id, 'student_id' => $request->user()->id]);
        return $this->success([
            'lesson'   => new LessonResource($lesson->load(['materials', 'slides', 'teacher'])),
            'progress' => $progress,
        ]);
    }

    public function updateProgress(Request $request, Lesson $lesson)
    {
        $this->checkAccess($request, $lesson);
        $request->validate([
            'watched_seconds' => 'sometimes|integer|min:0',
            'last_position'   => 'nullable|integer|min:0',
            'completed'       => 'sometimes|boolean',
        ]);
        $data = $request->only('watched_seconds', 'last_position');
        if ($request->has('completed')) {
            $data['is_completed'] = $request->boolean('completed');
            if ($data['is_completed']) {
                $data['last_viewed_at'] = now();
            }
        }
        $progress = StudyProgress::updateOrCreate(
            ['lesson_id' => $lesson->id, 'student_id' => $request->user()->id],
            $data
        );
        return $this->success($progress, 'Cập nhật tiến độ thành công');
    }

    public function downloadMaterial(Request $request, Lesson $lesson, LessonMaterial $material)
    {
        $this->checkAccess($request, $lesson);
        abort_if($material->lesson_id !== $lesson->id, 403);
        abort_unless(Storage::disk('public')->exists($material->file_path), 404, 'File không tồn tại');

        $material->increment('download_count');

        return Storage::disk('public')->download($material->file_path, $material->file_name);
    }

    private function checkAccess(Request $request, Lesson $lesson): void
    {
        abort_if($lesson->status !== 'published', 404, 'Bài học không tồn tại');

        // Public lessons are accessible by any authenticated student
        if (($lesson->visibility ?? 'class') === 'public') return;

        $classroomIds = $request->user()->classroomStudents()->pluck('classroom_id');
        abort_unless($classroomIds->contains($lesson->classroom_id), 403, 'Bạn không có quyền xem bài học này');
    }
}
