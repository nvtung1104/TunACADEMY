<?php
namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\{StoreExamRequest, StoreQuestionRequest};
use App\Http\Resources\Exam\{ExamResource, ExamAttemptResource};
use App\Models\{Classroom, Exam, ExamAttempt, ExamQuestion, User};
use App\Services\QuestionService;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function __construct(protected QuestionService $questionService)
    {
    }
    public function index(Request $request)
    {
        $exams = Exam::with(['classroom', 'subject'])
            ->withCount('questions')
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
        $question = $this->questionService->createExamQuestion($exam, $request->validated());
        return $this->success($question, 'Thêm câu hỏi thành công', 201);
    }

    public function updateQuestion(StoreQuestionRequest $request, Exam $exam, ExamQuestion $question)
    {
        $this->gate($request, $exam);
        abort_unless($question->exam_id === $exam->id, 404, 'Không tìm thấy câu hỏi');
        $question->update($request->validated());
        return $this->success($question->fresh(), 'Cập nhật câu hỏi thành công');
    }

    public function destroyQuestion(Request $request, Exam $exam, ExamQuestion $question)
    {
        $this->gate($request, $exam);
        abort_unless($question->exam_id === $exam->id, 404, 'Không tìm thấy câu hỏi');
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

    public function share(Request $request, Exam $exam)
    {
        $this->gate($request, $exam);

        $data = $request->validate([
            'visibility'             => 'required|in:public,private,class,selected',
            'target_classroom_ids'   => 'array',
            'target_classroom_ids.*' => 'integer|exists:classrooms,id',
            'target_student_ids'     => 'array',
            'target_student_ids.*'   => 'integer|exists:users,id',
        ]);

        $teacherName = $request->user()->name;

        if ($data['visibility'] === 'class') {
            if (empty($data['target_classroom_ids'])) {
                return $this->error('Vui lòng chọn lớp học để chia sẻ', 422);
            }
            $classroomId = $data['target_classroom_ids'][0];
            $exam->update([
                'classroom_id' => $classroomId,
                'visibility' => 'class'
            ]);
            $exam->shares()->delete();

            $classroom = Classroom::find($classroomId);
            if ($classroom) {
                $students = $classroom->students;
                foreach ($students as $student) {
                    \App\Models\Notification::create([
                        'user_id' => $student->id,
                        'type' => 'exam_shared',
                        'title' => "Đề thi mới cho lớp {$classroom->name}",
                        'message' => "Giáo viên {$teacherName} đã giao đề thi \"{$exam->title}\" cho lớp {$classroom->name}.",
                        'data' => [
                            'exam_id' => $exam->id,
                            'type' => 'exam_shared',
                            'title' => $exam->title,
                            'teacher_name' => $teacherName,
                        ]
                    ]);
                }
            }
        } elseif ($data['visibility'] === 'public') {
            $exam->update([
                'classroom_id' => null,
                'visibility' => 'public'
            ]);
            $exam->shares()->delete();

            $teacherId = $request->user()->id;
            $classroomIds = Classroom::where('homeroom_teacher_id', $teacherId)->pluck('id')
                ->merge(\App\Models\ClassroomSubjectTeacher::where('teacher_id', $teacherId)->pluck('classroom_id'))
                ->unique();

            $studentIds = \DB::table('classroom_students')
                ->whereIn('classroom_id', $classroomIds)
                ->where('status', 'active')
                ->pluck('student_id')
                ->unique();

            foreach ($studentIds as $studentId) {
                \App\Models\Notification::create([
                    'user_id' => $studentId,
                    'type' => 'exam_shared',
                    'title' => "Đề thi mới công khai",
                    'message' => "Giáo viên {$teacherName} đã chia sẻ công khai đề thi \"{$exam->title}\". Hãy thử sức ngay!",
                    'data' => [
                        'exam_id' => $exam->id,
                        'type' => 'exam_shared',
                        'title' => $exam->title,
                        'teacher_name' => $teacherName,
                    ]
                ]);
            }
        } else {
            $exam->update([
                'classroom_id' => null,
                'visibility' => $data['visibility']
            ]);
            $exam->shares()->delete();

            if ($data['visibility'] === 'selected') {
                $shares = [];
                foreach ($data['target_classroom_ids'] ?? [] as $classroomId) {
                    $shares[] = [
                        'target_type' => Classroom::class,
                        'target_id'   => $classroomId,
                    ];
                }
                foreach ($data['target_student_ids'] ?? [] as $userId) {
                    $shares[] = [
                        'target_type' => User::class,
                        'target_id'   => $userId,
                    ];
                }
                foreach ($shares as $share) {
                    $exam->shares()->create($share);
                }
            }
        }

        return $this->success(null, 'Cập nhật chia sẻ thành công');
    }

    public function uploadThumbnail(Request $request, Exam $exam)
    {
        $this->gate($request, $exam);
        $request->validate(['thumbnail' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120']);
        if ($exam->thumbnail) \Illuminate\Support\Facades\Storage::disk('public')->delete($exam->thumbnail);
        $path = $request->file('thumbnail')->store('thumbnails/exams', 'public');
        $exam->update(['thumbnail' => $path]);
        return $this->success(['thumbnail_url' => asset("storage/{$path}")], 'Cập nhật ảnh bìa thành công');
    }

    public function reorderQuestions(Request $request, Exam $exam)
    {
        $this->gate($request, $exam);
        $request->validate([
            'question_ids'   => 'required|array',
            'question_ids.*' => 'integer|exists:exam_questions,id',
        ]);

        $this->questionService->reorderExamQuestions($exam, $request->question_ids);
        return $this->success(null, 'Đã cập nhật thứ tự câu hỏi');
    }

    public function duplicateQuestion(Request $request, Exam $exam, ExamQuestion $question)
    {
        $this->gate($request, $exam);
        abort_unless($question->exam_id === $exam->id, 404, 'Không tìm thấy câu hỏi');

        $duplicated = $this->questionService->duplicateExamQuestion($question);
        return $this->success($duplicated, 'Đã sao chép câu hỏi', 201);
    }

    public function saveQuestionToBank(Request $request, Exam $exam, ExamQuestion $question)
    {
        $this->gate($request, $exam);
        abort_unless($question->exam_id === $exam->id, 404, 'Không tìm thấy câu hỏi');

        $request->validate(['is_public' => 'nullable|boolean']);

        $bankQuestion = $this->questionService->saveExamQuestionToBank(
            $question,
            $request->user()->id,
            $request->boolean('is_public', false)
        );

        // Link the exam question to the bank question
        $question->update(['question_bank_id' => $bankQuestion->id]);

        return $this->success($bankQuestion, 'Đã lưu câu hỏi vào ngân hàng', 201);
    }

    private function gate(Request $request, Exam $exam): void
    {
        abort_unless($exam->teacher_id === $request->user()->id, 403, 'Không có quyền');
    }
}
