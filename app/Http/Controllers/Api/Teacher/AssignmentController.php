<?php
namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\{StoreAssignmentRequest, StoreQuestionRequest};
use App\Models\{Assignment, AssignmentQuestion, AssignmentSubmission, Classroom, User};
use App\Services\QuestionService;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function __construct(protected QuestionService $questionService)
    {
    }
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
        return $this->success($assignment->load(['classroom', 'subject', 'questions', 'submissions.student']));
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
        abort_unless($submission->assignment_id === $assignment->id, 404, 'Không tìm thấy bài nộp');
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

        $teacherName = $request->user()->name;

        if ($data['visibility'] === 'class') {
            if (empty($data['target_classroom_ids'])) {
                return $this->error('Vui lòng chọn lớp học để chia sẻ', 422);
            }
            $classroomId = $data['target_classroom_ids'][0];
            $assignment->update([
                'classroom_id' => $classroomId,
                'visibility' => 'class',
                'status' => 'published'
            ]);
            $assignment->shares()->delete();

            $classroom = Classroom::find($classroomId);
            if ($classroom) {
                $students = $classroom->students;
                foreach ($students as $student) {
                    \App\Models\Notification::create([
                        'user_id' => $student->id,
                        'type' => 'assignment_shared',
                        'title' => "Bài tập mới cho lớp {$classroom->name}",
                        'message' => "Giáo viên {$teacherName} đã giao bài tập \"{$assignment->title}\" cho lớp {$classroom->name}.",
                        'data' => [
                            'assignment_id' => $assignment->id,
                            'type' => 'assignment_shared',
                            'title' => $assignment->title,
                            'teacher_name' => $teacherName,
                        ]
                    ]);
                }
            }
        } elseif ($data['visibility'] === 'public') {
            $assignment->update([
                'classroom_id' => null,
                'visibility' => 'public',
                'status' => 'published'
            ]);
            $assignment->shares()->delete();

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
                    'type' => 'assignment_shared',
                    'title' => "Bài tập mới công khai",
                    'message' => "Giáo viên {$teacherName} đã chia sẻ công khai bài tập \"{$assignment->title}\". Hãy hoàn thành ngay!",
                    'data' => [
                        'assignment_id' => $assignment->id,
                        'type' => 'assignment_shared',
                        'title' => $assignment->title,
                        'teacher_name' => $teacherName,
                    ]
                ]);
            }
        } else {
            $assignment->update([
                'classroom_id' => null,
                'visibility' => $data['visibility'],
                'status' => $data['visibility'] === 'private' ? 'draft' : 'published'
            ]);
            $assignment->shares()->delete();

            if ($data['visibility'] === 'selected') {
                $shares = [];
                foreach ($data['target_classroom_ids'] ?? [] as $classroomId) {
                    $assignment->shares()->create(['target_type' => Classroom::class, 'target_id' => $classroomId]);
                }
                foreach ($data['target_student_ids'] ?? [] as $userId) {
                    $assignment->shares()->create(['target_type' => User::class, 'target_id' => $userId]);
                }
            }
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

    public function storeQuestion(StoreQuestionRequest $request, Assignment $assignment)
    {
        $this->gate($request, $assignment);
        $question = $this->questionService->createAssignmentQuestion($assignment, $request->validated());
        return $this->success($question, 'Thêm câu hỏi thành công', 201);
    }

    public function updateQuestion(StoreQuestionRequest $request, Assignment $assignment, AssignmentQuestion $question)
    {
        $this->gate($request, $assignment);
        abort_unless($question->assignment_id === $assignment->id, 404, 'Không tìm thấy câu hỏi');
        $question->update($request->validated());
        return $this->success($question->fresh(), 'Cập nhật câu hỏi thành công');
    }

    public function destroyQuestion(Request $request, Assignment $assignment, AssignmentQuestion $question)
    {
        $this->gate($request, $assignment);
        abort_unless($question->assignment_id === $assignment->id, 404, 'Không tìm thấy câu hỏi');
        $question->delete();
        return $this->success(null, 'Xóa câu hỏi thành công');
    }

    public function reorderQuestions(Request $request, Assignment $assignment)
    {
        $this->gate($request, $assignment);
        $request->validate([
            'question_ids'   => 'required|array',
            'question_ids.*' => 'integer|exists:assignment_questions,id',
        ]);

        $this->questionService->reorderAssignmentQuestions($assignment, $request->question_ids);
        return $this->success(null, 'Đã cập nhật thứ tự câu hỏi');
    }

    public function duplicateQuestion(Request $request, Assignment $assignment, AssignmentQuestion $question)
    {
        $this->gate($request, $assignment);
        abort_unless($question->assignment_id === $assignment->id, 404, 'Không tìm thấy câu hỏi');

        $duplicated = $this->questionService->duplicateAssignmentQuestion($question);
        return $this->success($duplicated, 'Đã sao chép câu hỏi', 201);
    }

    public function saveQuestionToBank(Request $request, Assignment $assignment, AssignmentQuestion $question)
    {
        $this->gate($request, $assignment);
        abort_unless($question->assignment_id === $assignment->id, 404, 'Không tìm thấy câu hỏi');

        $request->validate(['is_public' => 'nullable|boolean']);

        $bankQuestion = $this->questionService->saveAssignmentQuestionToBank(
            $question,
            $request->user()->id,
            $request->boolean('is_public', false)
        );

        // Link the assignment question to the bank question
        $question->update(['question_bank_id' => $bankQuestion->id]);

        return $this->success($bankQuestion, 'Đã lưu câu hỏi vào ngân hàng', 201);
    }

    private function gate(Request $request, Assignment $assignment): void
    {
        abort_unless($assignment->teacher_id === $request->user()->id, 403, 'Không có quyền');
    }
}
