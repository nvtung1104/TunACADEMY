<?php
namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreLessonRequest;
use App\Http\Resources\Lesson\LessonResource;
use App\Models\{Lesson, LessonMaterial};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    public function storeMaterial(Request $request, Lesson $lesson)
    {
        $this->gate($request, $lesson);

        $request->validate([
            'file'      => 'required|file|max:51200',
            'file_name' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $path = $file->store('lesson-materials/' . $lesson->id, 'public');

        $material = $lesson->materials()->create([
            'file_name'  => $request->input('file_name', $file->getClientOriginalName()),
            'file_path'  => $path,
            'file_type'  => $file->extension(),
            'mime_type'  => $file->getMimeType(),
            'file_size'  => $file->getSize(),
        ]);

        return $this->success([
            'id'        => $material->id,
            'file_name' => $material->file_name,
            'file_type' => $material->file_type,
            'file_size' => $material->file_size,
            'url'       => $material->url,
        ], 'Tải lên tài liệu thành công', 201);
    }

    public function destroyMaterial(Request $request, Lesson $lesson, LessonMaterial $material)
    {
        $this->gate($request, $lesson);
        abort_unless($material->lesson_id === $lesson->id, 404, 'Tài liệu không thuộc bài học này');

        Storage::disk('public')->delete($material->file_path);
        $material->delete();

        return $this->success(null, 'Đã xóa tài liệu');
    }

    private function gate(Request $request, Lesson $lesson): void
    {
        abort_unless($lesson->teacher_id === $request->user()->id, 403, 'Không có quyền');
    }
}
