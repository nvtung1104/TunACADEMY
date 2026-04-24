<?php
namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreLessonRequest;
use App\Http\Resources\Lesson\LessonResource;
use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function index(Request $request)
    {
        $lessons = Lesson::with(['classroom', 'subject'])
            ->where('teacher_id', $request->user()->id)
            ->when($request->classroom_id, fn($q) => $q->where('classroom_id', $request->classroom_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()->paginate(20);
        return LessonResource::collection($lessons);
    }

    public function store(StoreLessonRequest $request)
    {
        $lesson = Lesson::create([...$request->validated(), 'teacher_id' => $request->user()->id]);
        return $this->success(new LessonResource($lesson->load(['classroom', 'subject'])), 'Tạo bài học thành công', 201);
    }

    public function show(Request $request, Lesson $lesson)
    {
        $this->gate($request, $lesson);
        return $this->success(new LessonResource($lesson->load(['classroom', 'subject', 'materials', 'slides'])));
    }

    public function update(StoreLessonRequest $request, Lesson $lesson)
    {
        $this->gate($request, $lesson);
        $lesson->update($request->validated());
        return $this->success(new LessonResource($lesson->fresh(['classroom', 'subject'])), 'Cập nhật thành công');
    }

    public function destroy(Request $request, Lesson $lesson)
    {
        $this->gate($request, $lesson);
        $lesson->delete();
        return $this->success(null, 'Xóa bài học thành công');
    }

    public function publish(Request $request, Lesson $lesson)
    {
        $this->gate($request, $lesson);
        $lesson->update(['status' => 'published', 'published_at' => now()]);
        return $this->success(new LessonResource($lesson->fresh()), 'Đã đăng bài học');
    }

    private function gate(Request $request, Lesson $lesson): void
    {
        abort_unless($lesson->teacher_id === $request->user()->id, 403, 'Không có quyền');
    }
}
