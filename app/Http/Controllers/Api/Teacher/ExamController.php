<?php
namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreExamRequest;
use App\Http\Requests\Teacher\StoreQuestionRequest;
use App\Http\Resources\Exam\{ExamResource, ExamAttemptResource};
use App\Models\{Exam, ExamAttempt, ExamQuestion};
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        $exams = Exam::with(['classroom', 'subject'])
            ->where('teacher_id', $request->user()->id)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()->paginate(20);
        return ExamResource::collection($exams);
    }

    public function store(StoreExamRequest $request)
    {
        $exam = Exam::create([...$request->validated(), 'teacher_id' => $request->user()->id]);
        return $this->success(new ExamResource($exam->load(['classroom', 'subject'])), 'Tạo bài kiểm tra thành công', 201);
    }

    public function show(Request $request, Exam $exam)
    {
        $this->gate($request, $exam);
        return $this->success(new ExamResource($exam->load(['classroom', 'subject', 'questions'])));
    }

    public function update(StoreExamRequest $request, Exam $exam)
    {
        $this->gate($request, $exam);
        $exam->update($request->validated());
        return $this->success(new ExamResource($exam->fresh()), 'Cập nhật thành công');
    }

    public function destroy(Request $request, Exam $exam)
    {
        $this->gate($request, $exam);
        $exam->delete();
        return $this->success(null, 'Xóa bài kiểm tra thành công');
    }

    public function publish(Request $request, Exam $exam)
    {
        $this->gate($request, $exam);
        abort_if($exam->questions()->count() === 0, 422, 'Bài kiểm tra chưa có câu hỏi');
        $exam->update(['status' => 'published']);
        return $this->success(new ExamResource($exam->fresh()), 'Đã đăng bài kiểm tra');
    }

    public function storeQuestion(StoreQuestionRequest $request, Exam $exam)
    {
        $this->gate($request, $exam);
        $question = $exam->questions()->create($request->validated());
        return $this->success($question, 'Thêm câu hỏi thành công', 201);
    }

    public function updateQuestion(StoreQuestionRequest $request, Exam $exam, ExamQuestion $question)
    {
        $this->gate($request, $exam);
        $question->update($request->validated());
        return $this->success($question->fresh(), 'Cập nhật câu hỏi thành công');
    }

    public function destroyQuestion(Request $request, Exam $exam, ExamQuestion $question)
    {
        $this->gate($request, $exam);
        $question->delete();
        return $this->success(null, 'Xóa câu hỏi thành công');
    }

    public function results(Request $request, Exam $exam)
    {
        $this->gate($request, $exam);
        $attempts = $exam->attempts()->with('student')->whereNotNull('submitted_at')->latest('submitted_at')->paginate(30);
        return ExamAttemptResource::collection($attempts);
    }

    public function studentResult(Request $request, Exam $exam, $student)
    {
        $this->gate($request, $exam);
        $attempt = $exam->attempts()->with(['student', 'answers.question'])->where('student_id', $student)->latest()->firstOrFail();

        return $this->success(new ExamAttemptResource($attempt));
    }

    public function attempts(Request $request, Exam $exam)
    {
        $this->gate($request, $exam);

        $attempts = $exam->attempts()
            ->with('student')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest('started_at')
            ->paginate(30);

        return ExamAttemptResource::collection($attempts);
    }

    public function attemptLogs(Request $request, Exam $exam, ExamAttempt $attempt)
    {
        $this->gate($request, $exam);
        abort_unless($attempt->exam_id === $exam->id, 404, 'Không tìm thấy bài làm');

        $logs = $attempt->proctoringLogs()->orderBy('occurred_at')->get()->map(fn($log) => [
            'id'              => $log->id,
            'event_type'      => $log->event_type,
            'description'     => $log->description,
            'screenshot_path' => $log->screenshot_path ? asset('storage/' . $log->screenshot_path) : null,
            'occurred_at'     => $log->occurred_at,
        ]);

        return $this->success([
            'attempt' => new ExamAttemptResource($attempt->load('student')),
            'logs'    => $logs,
        ]);
    }

    private function gate(Request $request, Exam $exam): void
    {
        abort_unless($exam->teacher_id === $request->user()->id, 403, 'Không có quyền');
    }
}
