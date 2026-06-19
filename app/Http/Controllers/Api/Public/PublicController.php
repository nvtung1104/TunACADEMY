<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\Lesson;
use App\Models\Subject;
use App\Services\AiSubmissionEvaluationService;
use App\Services\ExamGradingService;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function __construct(private readonly ExamGradingService $grader) {}

    public function grades()
    {
        $grades = Grade::orderBy('order_index')->get(['id', 'level', 'name']);
        return $this->success($grades);
    }

    public function subjects()
    {
        $subjects = Subject::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'color', 'icon']);
        return $this->success($subjects);
    }

    public function lessons(Request $request)
    {
        $query = Lesson::published()
            ->with([
                'classroom:id,name,grade_id',
                'classroom.grade:id,name,level',
                'subject:id,name,code,color',
                'teacher:id,name',
            ])
            ->when($request->grade_id, fn($q) => $q->whereHas(
                'classroom', fn($q) => $q->where('grade_id', $request->grade_id)
            ))
            ->when($request->subject_id, fn($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->search, fn($q) => $q->where('title', 'like', "%{$request->search}%"));

        return response()->json($query->orderByDesc('published_at')->paginate(12));
    }

    public function exams(Request $request)
    {
        $query = Exam::published()
            ->with([
                'classroom:id,name,grade_id',
                'classroom.grade:id,name,level',
                'subject:id,name,code,color',
                'teacher:id,name',
            ])
            ->when($request->grade_id, fn($q) => $q->whereHas(
                'classroom', fn($q) => $q->where('grade_id', $request->grade_id)
            ))
            ->when($request->subject_id, fn($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->search, fn($q) => $q->where('title', 'like', "%{$request->search}%"));

        return response()->json($query->orderByDesc('opened_at')->paginate(12));
    }

    public function assignments(Request $request)
    {
        $query = Assignment::published()
            ->with([
                'classroom:id,name,grade_id',
                'classroom.grade:id,name,level',
                'subject:id,name,code,color',
                'teacher:id,name',
            ])
            ->when($request->grade_id, fn($q) => $q->whereHas(
                'classroom', fn($q) => $q->where('grade_id', $request->grade_id)
            ))
            ->when($request->subject_id, fn($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->search, fn($q) => $q->where('title', 'like', "%{$request->search}%"));

        return response()->json($query->orderByDesc('deadline')->paginate(12));
    }

    public function lesson($id)
    {
        $lesson = Lesson::published()
            ->with([
                'classroom:id,name,grade_id',
                'classroom.grade:id,name,level',
                'subject:id,name,code,color',
                'teacher:id,name',
                'materials',
                'questions',
            ])
            ->findOrFail($id);

        $lesson->increment('view_count');

        return $this->success($lesson);
    }

    public function exam($id)
    {
        $exam = Exam::published()
            ->withCount('questions')
            ->with([
                'classroom:id,name,grade_id',
                'classroom.grade:id,name,level',
                'subject:id,name,code,color',
                'teacher:id,name',
            ])
            ->findOrFail($id);

        return $this->success($exam);
    }

    public function assignment($id)
    {
        $assignment = Assignment::published()
            ->withCount('questions')
            ->with([
                'classroom:id,name,grade_id',
                'classroom.grade:id,name,level',
                'subject:id,name,code,color',
                'teacher:id,name',
            ])
            ->findOrFail($id);

        return $this->success($assignment);
    }

    // ── Public exam take & submit ────────────────────────────────────────────

    public function examTake($id)
    {
        $exam = Exam::published()
            ->with(['questions' => fn($q) => $q->orderBy('order_index')])
            ->findOrFail($id);

        $questions = $exam->shuffle_questions ? $exam->questions->shuffle() : $exam->questions;

        return $this->success([
            'exam' => [
                'id'               => $exam->id,
                'title'            => $exam->title,
                'description'      => $exam->description,
                'duration_minutes' => $exam->duration_minutes,
                'subject'          => $exam->subject,
            ],
            'questions' => $questions->map(fn($q) => [
                'id'         => $q->id,
                'type'       => $q->type,
                'content'    => $q->content,
                'options'    => ($exam->shuffle_options && $q->options) ? collect($q->options)->shuffle()->values() : $q->options,
                'points'     => $q->points,
                'audio_path' => $q->audio_path,
            ])->values(),
        ]);
    }

    public function examSubmit(Request $request, $id)
    {
        $exam = Exam::published()->with('questions')->findOrFail($id);
        $request->validate(['answers' => 'required|array']);

        $total = $exam->questions->count();
        $weightPerQuestion = $total > 0 ? (10.0 / $total) : 0.0;

        $earnedPoints = 0; $totalPoints = 0; $totalCorrect = 0; $detail = [];
        foreach ($request->answers as $questionId => $studentAnswer) {
            $question = $exam->questions->find($questionId);
            if (!$question) continue;
            $isCorrect     = $this->grader->gradeAnswer($studentAnswer, $question->correct_answer, $question->type);
            $pointsEarned  = $isCorrect ? $weightPerQuestion : 0;
            $totalPoints  += $weightPerQuestion;
            $earnedPoints += $pointsEarned;
            if ($isCorrect) $totalCorrect++;
            $detail[$questionId] = [
                'is_correct'    => $isCorrect,
                'points_earned' => $pointsEarned,
            ];
        }
        $score = $total > 0 ? round(($totalCorrect / $total) * 10, 2) : 0;

        $questions = $exam->questions->map(fn($q) => [
            'id'             => $q->id,
            'correct_answer' => $q->correct_answer,
            'explanation'    => $q->explanation,
        ]);

        return $this->success([
            'score'         => $score,
            'total_correct' => $totalCorrect,
            'total'         => $total,
            'detail'        => $detail,
            'questions'     => $questions,
        ]);
    }

    // ── Public assignment take & submit ──────────────────────────────────────

    public function assignmentTake($id)
    {
        $assignment = Assignment::published()
            ->with(['questions' => fn($q) => $q->orderBy('order_index'), 'subject:id,name,color'])
            ->findOrFail($id);

        return $this->success([
            'assignment' => [
                'id'          => $assignment->id,
                'title'       => $assignment->title,
                'description' => $assignment->description,
                'type'        => $assignment->type,
                'deadline'    => $assignment->deadline,
                'max_score'   => $assignment->max_score,
                'subject'     => $assignment->subject,
            ],
            'questions' => $assignment->questions->map(fn($q) => [
                'id'         => $q->id,
                'type'       => $q->type,
                'content'    => $q->content,
                'options'    => $q->options,
                'points'     => $q->points,
                'audio_path' => $q->audio_path,
            ])->values(),
        ]);
    }

    public function assignmentSubmit(Request $request, $id)
    {
        $assignment = Assignment::published()->with('questions')->findOrFail($id);
        $request->validate(['answers' => 'nullable|array']);

        if (!$request->answers || empty($request->answers)) {
            return $this->success(['score' => null, 'total_correct' => 0, 'total' => 0]);
        }

        $earnedPoints = 0; $totalPoints = 0; $totalCorrect = 0; $detail = [];
        foreach ($request->answers as $questionId => $studentAnswer) {
            $question = $assignment->questions->find($questionId);
            if (!$question) continue;
            $isCorrect     = $this->grader->gradeAnswer($studentAnswer, $question->correct_answer, $question->type);
            $pointsEarned  = $isCorrect ? (float) $question->points : 0;
            $totalPoints  += (float) $question->points;
            $earnedPoints += $pointsEarned;
            if ($isCorrect) $totalCorrect++;
            $detail[$questionId] = [
                'is_correct'    => $isCorrect,
                'points_earned' => $pointsEarned,
            ];
        }
        $total = $assignment->questions->count();
        $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 10, 2) : null;

        $questions = $assignment->questions->map(fn($q) => [
            'id'             => $q->id,
            'correct_answer' => $q->correct_answer,
            'explanation'    => $q->explanation,
        ]);

        return $this->success([
            'score'         => $score,
            'total_correct' => $totalCorrect,
            'total'         => $total,
            'detail'        => $detail,
            'questions'     => $questions,
        ]);
    }

    public function aiReview(Request $request)
    {
        $request->validate([
            'type'   => 'required|in:exam,assignment',
            'id'     => 'required|integer',
            'answers' => 'required|array',
            'detail' => 'nullable|array',
        ]);

        if ($request->type === 'exam') {
            $model = Exam::published()->with(['questions', 'subject:id,name'])->find($request->id);
        } else {
            $model = Assignment::published()->with(['questions', 'subject:id,name'])->find($request->id);
        }

        if (!$model) {
            return $this->error('Không tìm thấy', 404);
        }

        $answers = $request->answers;
        $detail  = $request->detail ?? [];

        $payload = [
            'title'   => $model->title,
            'subject' => $model->subject?->name ?? '',
            'type'    => $request->type === 'exam' ? 'Đề thi' : 'Bài tập',
            'questions' => $model->questions->map(function ($q) use ($answers, $detail) {
                $qId = (string) $q->id;
                return [
                    'id'             => $qId,
                    'content'        => strip_tags($q->content),
                    'type'           => $q->type,
                    'options'        => $q->options,
                    'correct_answer' => $q->correct_answer,
                    'student_answer' => $answers[$qId] ?? ($answers[$q->id] ?? null),
                    'is_correct'     => $detail[$qId]['is_correct'] ?? ($detail[$q->id]['is_correct'] ?? null),
                    'media_type'     => $q->media_type,
                    'media_path'     => $q->media_path,
                ];
            })->values()->toArray(),
        ];

        $result = app(AiSubmissionEvaluationService::class)->reviewSubmission($payload);

        if (!$result) {
            return $this->error('Không thể kết nối AI lúc này. Thử lại sau.', 503);
        }

        return $this->success($result);
    }

    public function classrooms(Request $request)
    {
        $query = Classroom::with([
                'grade:id,name,level',
                'schoolYear:id,name',
                'homeroomTeacher:id,name',
            ])
            ->where('status', 'active')
            ->when($request->grade_id, fn($q) => $q->where('grade_id', $request->grade_id))
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->withCount('students');

        return response()->json($query->orderBy('name')->paginate(12));
    }
}
