<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreQuestionBankRequest;
use App\Models\{Assignment, Exam, QuestionBank};
use App\Services\QuestionService;
use Illuminate\Http\Request;

class QuestionBankController extends Controller
{
    public function __construct(protected QuestionService $questionService)
    {
    }
    // GET /teacher/question-bank
    // Filters: subject_id, grade_id, type, difficulty, chapter_tag, search
    public function index(Request $request)
    {
        $search = $request->search ? str_replace(['%', '_'], ['\\%', '\\_'], $request->search) : null;

        $questions = QuestionBank::where('teacher_id', $request->user()->id)
            ->when($request->subject_id,  fn($q) => $q->bySubject($request->subject_id))
            ->when($request->grade_id,    fn($q) => $q->byGrade($request->grade_id))
            ->when($request->type,        fn($q) => $q->byType($request->type))
            ->when($request->difficulty,  fn($q) => $q->byDifficulty($request->difficulty))
            ->when($request->lesson_id,   fn($q) => $q->where('lesson_id', $request->lesson_id))
            ->when($request->chapter_tag, fn($q) => $q->where('chapter_tag', $request->chapter_tag))
            ->when($request->exam_id,     fn($q) => $q->whereHas('examQuestions', fn($eq) => $eq->where('exam_id', $request->exam_id)))
            ->when($request->assignment_id, fn($q) => $q->whereHas('assignmentQuestions', fn($aq) => $aq->where('assignment_id', $request->assignment_id)))
            ->when($request->assigned_status === 'assigned', fn($q) => $q->where(fn($sub) => $sub->has('examQuestions')->orHas('assignmentQuestions')))
            ->when($request->assigned_status === 'unassigned', fn($q) => $q->doesntHave('examQuestions')->doesntHave('assignmentQuestions'))
            ->when($search, fn($q) => $q->where('content', 'like', "%{$search}%"))
            ->with(['subject', 'grade', 'lesson', 'exams:exams.id,title', 'assignments:assignments.id,title'])
            ->latest()
            ->paginate(30);

        return $this->success($questions);
    }

    // GET /teacher/question-bank/public
    // Ngân hàng công khai — giáo viên khác chia sẻ
    public function publicBank(Request $request)
    {
        $search = $request->search ? str_replace(['%', '_'], ['\\%', '\\_'], $request->search) : null;

        $questions = QuestionBank::public()
            ->when($request->subject_id,  fn($q) => $q->bySubject($request->subject_id))
            ->when($request->grade_id,    fn($q) => $q->byGrade($request->grade_id))
            ->when($request->type,        fn($q) => $q->byType($request->type))
            ->when($request->difficulty,  fn($q) => $q->byDifficulty($request->difficulty))
            ->when($search, fn($q) => $q->where('content', 'like', "%{$search}%"))
            ->with(['subject', 'grade', 'teacher:id,name'])
            ->latest()
            ->paginate(30);

        return $this->success($questions);
    }

    // POST /teacher/question-bank
    public function store(StoreQuestionBankRequest $request)
    {
        $question = \Illuminate\Support\Facades\DB::transaction(function() use ($request) {
            $validated = $request->validated();
            $assignType = $validated['assign_to_type'] ?? null;
            $assignId = $validated['assign_to_id'] ?? null;
            unset($validated['assign_to_type'], $validated['assign_to_id']);

            $q = QuestionBank::create([
                ...$validated,
                'teacher_id' => $request->user()->id,
            ]);

            if ($assignType === 'exam' && $assignId) {
                $exam = Exam::where('id', $assignId)
                    ->where('teacher_id', $request->user()->id)
                    ->firstOrFail();
                $this->questionService->importQuestionsToExam($exam, [$q->id]);
            } elseif ($assignType === 'assignment' && $assignId) {
                $assignment = Assignment::where('id', $assignId)
                    ->where('teacher_id', $request->user()->id)
                    ->firstOrFail();
                $this->questionService->importQuestionsToAssignment($assignment, [$q->id]);
            }

            return $q;
        });

        return $this->success($question->load(['subject', 'grade', 'lesson', 'exams:exams.id,title', 'assignments:assignments.id,title']), 'Đã thêm vào ngân hàng câu hỏi', 201);
    }

    // GET /teacher/question-bank/{question}
    public function show(Request $request, QuestionBank $questionBank)
    {
        $this->gate($request, $questionBank);
        return $this->success($questionBank->load(['subject', 'grade', 'lesson']));
    }

    // PUT /teacher/question-bank/{question}
    public function update(StoreQuestionBankRequest $request, QuestionBank $questionBank)
    {
        $this->gate($request, $questionBank);
        $questionBank->update($request->validated());
        return $this->success($questionBank->fresh(), 'Cập nhật thành công');
    }

    // DELETE /teacher/question-bank/{question}
    public function destroy(Request $request, QuestionBank $questionBank)
    {
        $this->gate($request, $questionBank);
        $questionBank->delete();
        return $this->success(null, 'Đã xóa câu hỏi');
    }

    // POST /teacher/exams/{exam}/import-questions
    // Import nhiều câu hỏi từ ngân hàng vào một đề thi
    public function importToExam(Request $request, Exam $exam)
    {
        abort_unless($exam->teacher_id === $request->user()->id, 403, 'Không có quyền');

        $request->validate([
            'question_ids'   => 'required|array|min:1',
            'question_ids.*' => 'integer|exists:question_bank,id',
        ]);

        $allowedIds = QuestionBank::whereIn('id', $request->question_ids)
            ->where(fn($q) => $q->where('teacher_id', $request->user()->id)->orWhere('is_public', true))
            ->pluck('id')->toArray();

        abort_if(empty($allowedIds), 403, 'Không có quyền truy cập câu hỏi đã chọn');

        // Enforce: Must match subject
        $mismatchedSubject = QuestionBank::whereIn('id', $allowedIds)
            ->where('subject_id', '!=', $exam->subject_id)
            ->exists();

        abort_if(
            $mismatchedSubject,
            422,
            'Một hoặc nhiều câu hỏi có môn học không khớp với môn học của đề thi.'
        );

        // Enforce: No duplicates in the same exam
        $alreadyAssigned = \App\Models\ExamQuestion::where('exam_id', $exam->id)
            ->whereIn('question_bank_id', $allowedIds)
            ->exists();

        abort_if(
            $alreadyAssigned,
            422,
            'Một hoặc nhiều câu hỏi đã chọn đã tồn tại trong đề thi này.'
        );

        $imported = $this->questionService->importQuestionsToExam($exam, $allowedIds);

        return $this->success($imported, count($imported) . ' câu hỏi đã được thêm vào đề thi', 201);
    }

    // POST /teacher/assignments/{assignment}/import-questions
    public function importToAssignment(Request $request, Assignment $assignment)
    {
        abort_unless($assignment->teacher_id === $request->user()->id, 403, 'Không có quyền');

        $request->validate([
            'question_ids'   => 'required|array|min:1',
            'question_ids.*' => 'integer|exists:question_bank,id',
        ]);

        $allowedIds = QuestionBank::whereIn('id', $request->question_ids)
            ->where(fn($q) => $q->where('teacher_id', $request->user()->id)->orWhere('is_public', true))
            ->pluck('id')->toArray();

        abort_if(empty($allowedIds), 403, 'Không có quyền truy cập câu hỏi đã chọn');

        // Enforce: Must match subject
        $mismatchedSubject = QuestionBank::whereIn('id', $allowedIds)
            ->where('subject_id', '!=', $assignment->subject_id)
            ->exists();

        abort_if(
            $mismatchedSubject,
            422,
            'Một hoặc nhiều câu hỏi có môn học không khớp với môn học của bài tập.'
        );

        // Enforce: No duplicates in the same assignment
        $alreadyAssigned = \App\Models\AssignmentQuestion::where('assignment_id', $assignment->id)
            ->whereIn('question_bank_id', $allowedIds)
            ->exists();

        abort_if(
            $alreadyAssigned,
            422,
            'Một hoặc nhiều câu hỏi đã chọn đã tồn tại trong bài tập này.'
        );

        $imported = $this->questionService->importQuestionsToAssignment($assignment, $allowedIds);

        return $this->success($imported, count($imported) . ' câu hỏi đã được thêm vào bài tập', 201);
    }

    // GET /teacher/question-bank/{questionBank}/usage
    // Xem câu hỏi được sử dụng ở đâu
    public function usage(Request $request, QuestionBank $questionBank)
    {
        $this->gate($request, $questionBank);

        // Use DISTINCT at DB level instead of loading all rows then filtering in PHP
        $exams = $questionBank->examQuestions()
            ->select('exam_id')
            ->distinct()
            ->with('exam:id,title,classroom_id,subject_id')
            ->get()
            ->pluck('exam')
            ->filter()
            ->values();

        $assignments = $questionBank->assignmentQuestions()
            ->select('assignment_id')
            ->distinct()
            ->with('assignment:id,title,classroom_id,subject_id')
            ->get()
            ->pluck('assignment')
            ->filter()
            ->values();

        return $this->success([
            'question' => $questionBank,
            'exams' => $exams,
            'assignments' => $assignments,
            'total_usage' => $exams->count() + $assignments->count(),
        ]);
    }

    public function removeFromExam(Request $request, Exam $exam)
    {
        abort_unless($exam->teacher_id === $request->user()->id, 403, 'Không có quyền');

        $request->validate([
            'question_ids'   => 'required|array|min:1',
            'question_ids.*' => 'integer|exists:question_bank,id',
        ]);

        \App\Models\ExamQuestion::where('exam_id', $exam->id)
            ->whereIn('question_bank_id', $request->question_ids)
            ->delete();

        return $this->success(null, 'Đã xóa câu hỏi khỏi đề thi');
    }

    public function removeFromAssignment(Request $request, Assignment $assignment)
    {
        abort_unless($assignment->teacher_id === $request->user()->id, 403, 'Không có quyền');

        $request->validate([
            'question_ids'   => 'required|array|min:1',
            'question_ids.*' => 'integer|exists:question_bank,id',
        ]);

        \App\Models\AssignmentQuestion::where('assignment_id', $assignment->id)
            ->whereIn('question_bank_id', $request->question_ids)
            ->delete();

        return $this->success(null, 'Đã xóa câu hỏi khỏi bài tập');
    }

    private function gate(Request $request, QuestionBank $q): void
    {
        abort_unless($q->teacher_id === $request->user()->id, 403, 'Không có quyền');
    }
}
