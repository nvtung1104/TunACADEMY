<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\Lesson;
use App\Models\Subject;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function grades()
    {
        $grades = Grade::orderBy('order_index')->get(['id', 'level', 'name']);
        return response()->json(['data' => $grades]);
    }

    public function subjects()
    {
        $subjects = Subject::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'color', 'icon']);
        return response()->json(['data' => $subjects]);
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
            ])
            ->findOrFail($id);

        $lesson->increment('view_count');

        return response()->json(['data' => $lesson]);
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

        return response()->json(['data' => $exam]);
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

        return response()->json(['data' => $assignment]);
    }

    // ── Public exam take & submit ────────────────────────────────────────────

    public function examTake($id)
    {
        $exam = Exam::published()
            ->with(['questions' => fn($q) => $q->orderBy('order_index')])
            ->findOrFail($id);

        $questions = $exam->shuffle_questions ? $exam->questions->shuffle() : $exam->questions;

        return response()->json(['data' => [
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
        ]]);
    }

    public function examSubmit(Request $request, $id)
    {
        $exam = Exam::published()->with('questions')->findOrFail($id);
        $request->validate(['answers' => 'required|array']);

        $earnedPoints = 0; $totalPoints = 0; $totalCorrect = 0; $detail = [];
        foreach ($request->answers as $questionId => $studentAnswer) {
            $question = $exam->questions->find($questionId);
            if (!$question) continue;
            $isCorrect     = $this->gradeAnswer($studentAnswer, $question->correct_answer, $question->type);
            $pointsEarned  = $isCorrect ? (float) $question->points : 0;
            $totalPoints  += (float) $question->points;
            $earnedPoints += $pointsEarned;
            if ($isCorrect) $totalCorrect++;
            $detail[$questionId] = [
                'is_correct'     => $isCorrect,
                'correct_answer' => $question->correct_answer,
                'points_earned'  => $pointsEarned,
            ];
        }
        $total = $exam->questions->count();
        $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 10, 2) : 0;

        return response()->json(['data' => [
            'score'         => $score,
            'total_correct' => $totalCorrect,
            'total'         => $total,
            'detail'        => $detail,
        ]]);
    }

    // ── Public assignment take & submit ──────────────────────────────────────

    public function assignmentTake($id)
    {
        $assignment = Assignment::published()
            ->with(['questions' => fn($q) => $q->orderBy('order_index'), 'subject:id,name,color'])
            ->findOrFail($id);

        return response()->json(['data' => [
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
        ]]);
    }

    public function assignmentSubmit(Request $request, $id)
    {
        $assignment = Assignment::published()->with('questions')->findOrFail($id);
        $request->validate(['answers' => 'nullable|array']);

        if (!$request->answers || empty($request->answers)) {
            return response()->json(['data' => ['score' => null, 'total_correct' => 0, 'total' => 0]]);
        }

        $earnedPoints = 0; $totalPoints = 0; $totalCorrect = 0; $detail = [];
        foreach ($request->answers as $questionId => $studentAnswer) {
            $question = $assignment->questions->find($questionId);
            if (!$question) continue;
            $isCorrect     = $this->gradeAnswer($studentAnswer, $question->correct_answer, $question->type);
            $pointsEarned  = $isCorrect ? (float) $question->points : 0;
            $totalPoints  += (float) $question->points;
            $earnedPoints += $pointsEarned;
            if ($isCorrect) $totalCorrect++;
            $detail[$questionId] = [
                'is_correct'     => $isCorrect,
                'correct_answer' => $question->correct_answer,
                'points_earned'  => $pointsEarned,
            ];
        }
        $total = $assignment->questions->count();
        $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 10, 2) : null;

        return response()->json(['data' => [
            'score'         => $score,
            'total_correct' => $totalCorrect,
            'total'         => $total,
            'detail'        => $detail,
        ]]);
    }

    private function gradeAnswer(mixed $student, mixed $correct, string $type): bool
    {
        if (in_array($type, ['essay', 'speaking', 'writing', 'short_answer', 'reading', 'listening'])) return false;
        if (is_null($correct) || $correct === []) return false;
        $c = array_map('strval', is_array($correct) ? $correct : [$correct]);
        $s = is_array($student) ? array_map('strval', $student) : [strval($student)];
        return match ($type) {
            'multiple_select' => (function () use ($c, $s) { sort($c); sort($s); return $c === $s; })(),
            'fill_blank'      => count($c) === count($s) && !array_filter(
                                    array_map(fn($i, $ci) => mb_strtolower(trim($ci)) !== mb_strtolower(trim($s[$i] ?? '')),
                                    array_keys($c), $c)),
            'ordering'  => $c === $s,
            'matching'  => json_encode($correct) === json_encode($student),
            default     => trim($c[0] ?? '') === trim($s[0] ?? strval($student)),
        };
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
