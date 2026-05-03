<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreQuestionBankRequest;
use App\Models\{Assignment, AssignmentQuestion, Exam, ExamQuestion, QuestionBank};
use Illuminate\Http\Request;

class QuestionBankController extends Controller
{
    // GET /teacher/question-bank
    // Filters: subject_id, grade_id, type, difficulty, chapter_tag, search
    public function index(Request $request)
    {
        $questions = QuestionBank::where('teacher_id', $request->user()->id)
            ->when($request->subject_id,  fn($q) => $q->bySubject($request->subject_id))
            ->when($request->grade_id,    fn($q) => $q->byGrade($request->grade_id))
            ->when($request->type,        fn($q) => $q->byType($request->type))
            ->when($request->difficulty,  fn($q) => $q->byDifficulty($request->difficulty))
            ->when($request->chapter_tag, fn($q) => $q->where('chapter_tag', $request->chapter_tag))
            ->when($request->search, fn($q) =>
                $q->where('content', 'like', "%{$request->search}%")
            )
            ->with(['subject', 'grade', 'lesson'])
            ->latest()
            ->paginate(30);

        return $this->success($questions);
    }

    // GET /teacher/question-bank/public
    // Ngân hàng công khai — giáo viên khác chia sẻ
    public function publicBank(Request $request)
    {
        $questions = QuestionBank::public()
            ->when($request->subject_id,  fn($q) => $q->bySubject($request->subject_id))
            ->when($request->grade_id,    fn($q) => $q->byGrade($request->grade_id))
            ->when($request->type,        fn($q) => $q->byType($request->type))
            ->when($request->difficulty,  fn($q) => $q->byDifficulty($request->difficulty))
            ->when($request->search, fn($q) =>
                $q->where('content', 'like', "%{$request->search}%")
            )
            ->with(['subject', 'grade', 'teacher:id,name'])
            ->latest()
            ->paginate(30);

        return $this->success($questions);
    }

    // POST /teacher/question-bank
    public function store(StoreQuestionBankRequest $request)
    {
        $question = QuestionBank::create([
            ...$request->validated(),
            'teacher_id' => $request->user()->id,
        ]);

        return $this->success($question->load(['subject', 'grade', 'lesson']), 'Đã thêm vào ngân hàng câu hỏi', 201);
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

        $questions = QuestionBank::whereIn('id', $request->question_ids)->get();
        $nextIndex = $exam->questions()->max('order_index') ?? 0;

        $imported = [];
        foreach ($questions as $q) {
            $examQuestion = ExamQuestion::create([
                'exam_id'          => $exam->id,
                'question_bank_id' => $q->id,
                'grade_id'         => $q->grade_id,
                'lesson_id'        => $q->lesson_id,
                'type'             => $q->type,
                'difficulty'       => $q->difficulty,
                'chapter_tag'      => $q->chapter_tag,
                'content'          => $q->content,
                'media_type'       => $q->media_type,
                'media_path'       => $q->media_path,
                'audio_path'       => $q->audio_path ?? null,
                'options'          => $q->options,
                'correct_answer'   => $q->correct_answer,
                'explanation'      => $q->explanation,
                'sub_questions'    => $q->sub_questions,
                'metadata'         => $q->metadata,
                'points'           => $q->default_points,
                'order_index'      => ++$nextIndex,
            ]);
            $imported[] = $examQuestion;
        }

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

        $questions = QuestionBank::whereIn('id', $request->question_ids)->get();
        $nextIndex = $assignment->questions()->max('order_index') ?? 0;

        $imported = [];
        foreach ($questions as $q) {
            $imported[] = AssignmentQuestion::create([
                'assignment_id'    => $assignment->id,
                'question_bank_id' => $q->id,
                'grade_id'         => $q->grade_id,
                'lesson_id'        => $q->lesson_id,
                'type'             => $q->type,
                'difficulty'       => $q->difficulty,
                'chapter_tag'      => $q->chapter_tag,
                'content'          => $q->content,
                'media_type'       => $q->media_type,
                'media_path'       => $q->media_path,
                'audio_path'       => $q->audio_path ?? null,
                'options'          => $q->options,
                'correct_answer'   => $q->correct_answer,
                'explanation'      => $q->explanation,
                'sub_questions'    => $q->sub_questions,
                'metadata'         => $q->metadata,
                'points'           => $q->default_points,
                'order_index'      => ++$nextIndex,
            ]);
        }

        return $this->success($imported, count($imported) . ' câu hỏi đã được thêm vào bài tập', 201);
    }

    private function gate(Request $request, QuestionBank $q): void
    {
        abort_unless($q->teacher_id === $request->user()->id, 403, 'Không có quyền');
    }
}
