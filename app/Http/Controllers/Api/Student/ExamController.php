<?php
namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\Exam\{ExamResource, ExamAttemptResource};
use App\Models\{Exam, ExamAttempt, ProctoringLog, StudentAnswer};
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        $classroomIds = $request->user()->classrooms()->pluck('classrooms.id');
        $exams = Exam::with(['subject', 'classroom'])
            ->whereIn('classroom_id', $classroomIds)->published()
            ->where('closed_at', '>', now())->latest('opened_at')->paginate(20);
        return ExamResource::collection($exams);
    }

    public function show(Request $request, Exam $exam)
    {
        $this->checkAccess($request, $exam);
        $attempt = $exam->attempts()->where('student_id', $request->user()->id)->latest()->first();
        return $this->success([
            'exam'    => new ExamResource($exam),
            'attempt' => $attempt ? new ExamAttemptResource($attempt) : null,
        ]);
    }

    public function start(Request $request, Exam $exam)
    {
        $this->checkAccess($request, $exam);
        abort_unless($exam->isOpen(), 422, 'Bài kiểm tra chưa mở hoặc đã kết thúc');

        $existing = $exam->attempts()->where('student_id', $request->user()->id)->where('status', 'in_progress')->first();
        if ($existing) return $this->success(new ExamAttemptResource($existing), 'Tiếp tục bài làm');

        abort_if(
            !$exam->allow_retake && $exam->attempts()->where('student_id', $request->user()->id)->whereNotNull('submitted_at')->exists(),
            422, 'Bạn đã nộp bài, không được làm lại'
        );

        $attempt = ExamAttempt::create([
            'exam_id'    => $exam->id,
            'student_id' => $request->user()->id,
            'started_at' => now(),
            'expires_at' => now()->addMinutes($exam->duration_minutes),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $questions = $exam->shuffle_questions ? $exam->questions->shuffle() : $exam->questions;
        return $this->success([
            'attempt'   => new ExamAttemptResource($attempt),
            'questions' => $questions->map(fn($q) => [
                'id'         => $q->id,
                'type'       => $q->type,
                'content'    => $q->content,
                'options'    => ($exam->shuffle_options && $q->options) ? collect($q->options)->shuffle()->values() : $q->options,
                'points'     => $q->points,
                'audio_path' => $q->audio_path,
            ]),
        ], 'Bắt đầu làm bài');
    }

    public function submit(Request $request, Exam $exam)
    {
        $attempt = $exam->attempts()->where('student_id', $request->user()->id)->where('status', 'in_progress')->firstOrFail();
        $request->validate(['answers' => 'required|array']);

        $earnedPoints = 0; $totalPoints = 0; $totalCorrect = 0;
        foreach ($request->answers as $questionId => $studentAnswer) {
            $question = $exam->questions()->find($questionId);
            if (!$question) continue;

            $isCorrect   = $this->gradeAnswer($studentAnswer, $question->correct_answer, $question->type);
            $pointsEarned = $isCorrect ? (float) $question->points : 0;
            $stored       = is_array($studentAnswer) ? json_encode($studentAnswer, JSON_UNESCAPED_UNICODE) : (string) $studentAnswer;

            StudentAnswer::updateOrCreate(
                ['attempt_id' => $attempt->id, 'question_id' => $question->id],
                ['answer' => $stored, 'is_correct' => $isCorrect, 'points_earned' => $pointsEarned, 'answered_at' => now()]
            );
            $totalPoints  += (float) $question->points;
            $earnedPoints += $pointsEarned;
            if ($isCorrect) $totalCorrect++;
        }

        $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 10, 2) : 0;
        $attempt->update(['submitted_at' => now(), 'score' => $score, 'total_correct' => $totalCorrect, 'status' => 'graded']);
        return $this->success(['score' => $score, 'total_correct' => $totalCorrect], 'Nộp bài thành công');
    }

    private function gradeAnswer(mixed $student, mixed $correct, string $type): bool
    {
        // Teacher-graded types — always false (manual)
        if (in_array($type, ['essay', 'speaking', 'writing', 'short_answer', 'reading', 'listening'])) return false;
        if (is_null($correct) || $correct === []) return false;

        // Normalise both sides to string arrays
        $c = array_map('strval', is_array($correct) ? $correct : [$correct]);
        $s = is_array($student)  ? array_map('strval', $student) : [strval($student)];

        return match ($type) {
            'multiple_select' => (function () use ($c, $s) { sort($c); sort($s); return $c === $s; })(),
            'fill_blank'      => count($c) === count($s) && !array_filter(
                                    array_map(fn($i, $ci) => mb_strtolower(trim($ci)) !== mb_strtolower(trim($s[$i] ?? '')),
                                    array_keys($c), $c)),
            'ordering'        => $c === $s,
            'matching'        => json_encode($correct) === json_encode($student),
            default           => trim($c[0] ?? '') === trim($s[0] ?? strval($student)),
        };
    }

    public function result(Request $request, Exam $exam)
    {
        $this->checkAccess($request, $exam);
        $attempt = $exam->attempts()->with('answers.question')->where('student_id', $request->user()->id)->whereNotNull('submitted_at')->latest()->firstOrFail();
        return $this->success(new ExamAttemptResource($attempt));
    }

    public function logViolation(Request $request, Exam $exam, ExamAttempt $attempt)
    {
        abort_unless($attempt->exam_id === $exam->id && $attempt->student_id === $request->user()->id, 403, 'Không có quyền');
        abort_unless($attempt->status === 'in_progress', 422, 'Bài thi đã kết thúc');

        $request->validate([
            'event_type'  => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
        ]);

        ProctoringLog::create([
            'attempt_id'  => $attempt->id,
            'student_id'  => $request->user()->id,
            'event_type'  => $request->event_type,
            'description' => $request->description,
            'occurred_at' => now(),
        ]);

        $attempt->increment('violation_count');

        if ($exam->proctoring_enabled && $exam->max_violations > 0 && $attempt->fresh()->violation_count >= $exam->max_violations) {
            $attempt->update(['status' => 'submitted', 'submitted_at' => now()]);
            return $this->error('Bài thi đã bị kết thúc do vi phạm quy định', [], 422);
        }

        return $this->success(['violation_count' => $attempt->fresh()->violation_count], 'Đã ghi nhận vi phạm');
    }

    private function checkAccess(Request $request, Exam $exam): void
    {
        $classroomIds = $request->user()->classrooms()->pluck('classrooms.id');
        abort_unless($classroomIds->contains($exam->classroom_id), 403, 'Không có quyền truy cập');
    }
}
