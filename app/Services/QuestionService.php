<?php

namespace App\Services;

use App\Models\{Assignment, AssignmentQuestion, Exam, ExamQuestion, QuestionBank};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class QuestionService
{
    // ── Public API ────────────────────────────────────────────────────────────

    public function importQuestionsToExam(Exam $exam, array $questionIds): Collection
    {
        return $this->importFromBank(
            parent: $exam,
            questionClass: ExamQuestion::class,
            foreignKey: 'exam_id',
            questionIds: $questionIds,
            usageColumn: 'used_in_exams_count',
        );
    }

    public function importQuestionsToAssignment(Assignment $assignment, array $questionIds): Collection
    {
        return $this->importFromBank(
            parent: $assignment,
            questionClass: AssignmentQuestion::class,
            foreignKey: 'assignment_id',
            questionIds: $questionIds,
            usageColumn: 'used_in_assignments_count',
        );
    }

    public function createExamQuestion(Exam $exam, array $data): ExamQuestion
    {
        $nextIndex = $exam->questions()->max('order_index') ?? 0;
        return ExamQuestion::create(['exam_id' => $exam->id, 'order_index' => $nextIndex + 1, ...$data]);
    }

    public function createAssignmentQuestion(Assignment $assignment, array $data): AssignmentQuestion
    {
        $nextIndex = $assignment->questions()->max('order_index') ?? 0;
        return AssignmentQuestion::create(['assignment_id' => $assignment->id, 'order_index' => $nextIndex + 1, ...$data]);
    }

    public function reorderExamQuestions(Exam $exam, array $questionIds): void
    {
        $this->reorderQuestions(ExamQuestion::class, 'exam_id', $exam->id, $questionIds);
    }

    public function reorderAssignmentQuestions(Assignment $assignment, array $questionIds): void
    {
        $this->reorderQuestions(AssignmentQuestion::class, 'assignment_id', $assignment->id, $questionIds);
    }

    public function duplicateExamQuestion(ExamQuestion $question): ExamQuestion
    {
        $nextIndex = $question->exam->questions()->max('order_index') ?? 0;
        return ExamQuestion::create([
            'exam_id'     => $question->exam_id,
            'order_index' => $nextIndex + 1,
            ...$this->questionContentFields($question),
        ]);
    }

    public function duplicateAssignmentQuestion(AssignmentQuestion $question): AssignmentQuestion
    {
        $nextIndex = $question->assignment->questions()->max('order_index') ?? 0;
        return AssignmentQuestion::create([
            'assignment_id' => $question->assignment_id,
            'order_index'   => $nextIndex + 1,
            ...$this->questionContentFields($question),
        ]);
    }

    public function saveExamQuestionToBank(ExamQuestion $question, int $teacherId, bool $isPublic = false): QuestionBank
    {
        return QuestionBank::create([
            'teacher_id'    => $teacherId,
            'subject_id'    => $question->exam->subject_id,
            'default_points' => $question->points,
            'is_public'     => $isPublic,
            ...$this->questionContentFields($question),
        ]);
    }

    public function saveAssignmentQuestionToBank(AssignmentQuestion $question, int $teacherId, bool $isPublic = false): QuestionBank
    {
        return QuestionBank::create([
            'teacher_id'    => $teacherId,
            'subject_id'    => $question->assignment->subject_id,
            'default_points' => $question->points,
            'is_public'     => $isPublic,
            ...$this->questionContentFields($question),
        ]);
    }

    // ── Static helpers ────────────────────────────────────────────────────────

    public static function getQuestionTypesByCategory(): array
    {
        return [
            'multiple_choice' => [
                'multiple_choice' => 'Chọn 1 đáp án',
                'multiple_select' => 'Chọn nhiều đáp án',
                'true_false'      => 'Đúng / Sai',
            ],
            'essay' => [
                'fill_blank'   => 'Điền vào chỗ trống',
                'short_answer' => 'Trả lời ngắn',
                'essay'        => 'Tự luận',
                'calculation'  => 'Tính toán (có sai số)',
            ],
            'interactive' => [
                'ordering'  => 'Sắp xếp thứ tự',
                'matching'  => 'Nối cặp',
                'drag_drop' => 'Kéo thả',
            ],
            'language' => [
                'reading'   => 'Đọc hiểu',
                'listening' => 'Nghe hiểu',
                'speaking'  => 'Nói',
                'writing'   => 'Viết',
            ],
        ];
    }

    public static function getAllQuestionTypes(): array
    {
        return [
            'multiple_choice', 'multiple_select', 'true_false',
            'fill_blank', 'short_answer', 'essay', 'calculation',
            'ordering', 'matching', 'drag_drop',
            'reading', 'listening', 'speaking', 'writing',
        ];
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Import questions from bank into an exam or assignment.
     * Single query for usage tracking (increment + last_used_at in one UPDATE).
     */
    private function importFromBank(
        Model $parent,
        string $questionClass,
        string $foreignKey,
        array $questionIds,
        string $usageColumn,
    ): Collection {
        // Enforce: Must match subject
        $mismatchedSubject = QuestionBank::whereIn('id', $questionIds)
            ->where('subject_id', '!=', $parent->subject_id)
            ->exists();

        abort_if(
            $mismatchedSubject,
            422,
            'Một hoặc nhiều câu hỏi có môn học không khớp với môn học của đề/bài tập.'
        );

        // Enforce: No duplicates in the same exam/assignment
        $alreadyAssigned = $questionClass::where($foreignKey, $parent->id)
            ->whereIn('question_bank_id', $questionIds)
            ->exists();

        abort_if(
            $alreadyAssigned,
            422,
            'Một hoặc nhiều câu hỏi đã chọn đã tồn tại trong đề/bài tập này.'
        );

        $questions = QuestionBank::whereIn('id', $questionIds)->get();
        $nextIndex = $parent->questions()->max('order_index') ?? 0;
        $imported  = collect();

        foreach ($questions as $q) {
            $imported->push($questionClass::create([
                $foreignKey    => $parent->id,
                'order_index'  => ++$nextIndex,
                'points'       => $q->default_points,
                ...$this->bankContentFields($q),
            ]));

            // Single query: increment count and set last_used_at together
            $q->update([
                $usageColumn  => DB::raw("{$usageColumn} + 1"),
                'last_used_at' => now(),
            ]);
        }

        return $imported;
    }

    /**
     * Reorder questions for an exam or assignment (shared logic).
     */
    private function reorderQuestions(string $modelClass, string $foreignKey, int $parentId, array $questionIds): void
    {
        foreach ($questionIds as $index => $questionId) {
            $modelClass::where($foreignKey, $parentId)
                ->where('id', $questionId)
                ->update(['order_index' => $index + 1]);
        }
    }

    /**
     * Content fields shared between ExamQuestion, AssignmentQuestion, and QuestionBank.
     * Used for duplication and save-to-bank operations.
     */
    private function questionContentFields(Model $source): array
    {
        return [
            'question_bank_id' => $source->question_bank_id ?? null,
            'grade_id'         => $source->grade_id,
            'lesson_id'        => $source->lesson_id,
            'type'             => $source->type,
            'difficulty'       => $source->difficulty,
            'chapter_tag'      => $source->chapter_tag,
            'content'          => $source->content,
            'media_type'       => $source->media_type,
            'media_path'       => $source->media_path,
            'audio_path'       => $source->audio_path,
            'options'          => $source->options,
            'correct_answer'   => $source->correct_answer,
            'explanation'      => $source->explanation,
            'sub_questions'    => $source->sub_questions,
            'metadata'         => $source->metadata,
        ];
    }

    /**
     * Content fields from a QuestionBank record (maps default_points → points for import).
     */
    private function bankContentFields(QuestionBank $q): array
    {
        return [
            'question_bank_id' => $q->id,
            'grade_id'         => $q->grade_id,
            'lesson_id'        => $q->lesson_id,
            'type'             => $q->type,
            'difficulty'       => $q->difficulty,
            'chapter_tag'      => $q->chapter_tag,
            'content'          => $q->content,
            'media_type'       => $q->media_type,
            'media_path'       => $q->media_path,
            'audio_path'       => $q->audio_path,
            'options'          => $q->options,
            'correct_answer'   => $q->correct_answer,
            'explanation'      => $q->explanation,
            'sub_questions'    => $q->sub_questions,
            'metadata'         => $q->metadata,
        ];
    }
}
