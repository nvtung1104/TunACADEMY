<?php
namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\Lesson\LessonResource;
use App\Models\{Lesson, StudyProgress};
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function index(Request $request)
    {
        $classroomIds = $request->user()->classrooms()->pluck('classrooms.id');
        $lessons = Lesson::with(['subject', 'teacher'])
            ->whereIn('classroom_id', $classroomIds)
            ->published()
            ->when($request->classroom_id, fn($q) => $q->where('classroom_id', $request->classroom_id))
            ->orderBy('order_index')->paginate(20);
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
            'watched_seconds' => 'required|integer|min:0',
            'last_position'   => 'nullable|integer|min:0',
            'completed'       => 'sometimes|boolean',
        ]);
        $progress = StudyProgress::updateOrCreate(
            ['lesson_id' => $lesson->id, 'student_id' => $request->user()->id],
            [...$request->only('watched_seconds', 'last_position', 'completed'),
             'completed_at' => $request->completed ? now() : null]
        );
        return $this->success($progress, 'Cập nhật tiến độ thành công');
    }

    private function checkAccess(Request $request, Lesson $lesson): void
    {
        $classroomIds = $request->user()->classrooms()->pluck('classrooms.id');
        abort_unless($classroomIds->contains($lesson->classroom_id), 403, 'Không có quyền xem bài học này');
        abort_if($lesson->status !== 'published', 404, 'Bài học không tồn tại');
    }
}
