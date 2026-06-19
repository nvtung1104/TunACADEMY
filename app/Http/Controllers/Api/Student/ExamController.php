<?php
namespace App\Http\Controllers\Api\Student;

use App\Enums\ExamAttemptStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Exam\{ExamResource, ExamAttemptResource};
use App\Models\{Exam, ExamAttempt, ProctoringLog};
use App\Services\AiSubmissionEvaluationService;
use App\Services\ExamGradingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    public function __construct(
        private readonly AiSubmissionEvaluationService $aiEvaluator,
        private readonly ExamGradingService $gradingService,
    ) {}

    public function index(Request $request)
    {
        $classroomIds = $request->user()->classrooms()->pluck('classrooms.id');
        $exams = Exam::with(['subject', 'classroom'])
            ->withCount('questions')
            ->whereIn('classroom_id', $classroomIds)->published()
            ->where('closed_at', '>', now())
            ->when($request->classroom_id, fn($q) => $q->where('classroom_id', $request->classroom_id))
            ->latest('opened_at')->paginate(20);
        return ExamResource::collection($exams);
    }

    public function show(Request $request, Exam $exam)
    {
        $this->checkAccess($request, $exam);
        $attempt = $exam->attempts()->where('student_id', $request->user()->id)->latest()->first();
        $attemptsCount = $exam->attempts()->where('student_id', $request->user()->id)->whereNotNull('submitted_at')->count();
        return $this->success([
            'exam'    => new ExamResource($exam),
            'attempt' => $attempt ? new ExamAttemptResource($attempt) : null,
            'attempts_count' => $attemptsCount,
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
                'id'            => $q->id,
                'type'          => $q->type,
                'content'       => $q->content,
                'options'       => ($exam->shuffle_options && $q->options) ? collect($q->options)->shuffle()->values() : $q->options,
                'points'        => $q->points,
                'audio_path'    => $q->audio_path,
                'media_type'    => $q->media_type,
                'media_path'    => $q->media_path,
                'sub_questions' => $q->sub_questions,
            ]),
        ], 'Bắt đầu làm bài');
    }

    public function submit(Request $request, Exam $exam)
    {
        // Only accept the answers array — score is always computed server-side
        $request->validate(['answers' => 'required|array']);

        return DB::transaction(function () use ($request, $exam) {
            $attempt = $exam->attempts()
                ->where('student_id', $request->user()->id)
                ->where('status', 'in_progress')
                ->lockForUpdate()
                ->firstOrFail();

            abort_if($attempt->submitted_at !== null, 422, 'Bài thi đã được nộp');
            abort_if($attempt->isExpired(), 422, 'Đã hết thời gian làm bài');

            // Server-side grading only — client score values are ignored
            $gradeResult = $this->gradingService->grade($attempt, $exam, $request->answers);

            $aiEvaluation = $this->aiEvaluator->evaluateExam([
                'exam' => [
                    'id'          => $exam->id,
                    'title'       => $exam->title,
                    'description' => $exam->description,
                    'subject'     => $exam->subject?->name,
                ],
                'student' => [
                    'id'   => $request->user()->id,
                    'name' => $request->user()->name,
                ],
                'grading_context' => [
                    'base_score'    => $gradeResult['score'],
                    'total_correct' => $gradeResult['totalCorrect'],
                    'total_points'  => $gradeResult['totalPoints'],
                    'earned_points' => $gradeResult['earnedPoints'],
                ],
                'answers' => $gradeResult['answerRows'],
            ]);

            // If AI returns a score use it; otherwise keep the server-computed score.
            // Either way the score comes from a trusted source, never the client.
            $finalScore = is_numeric($aiEvaluation['score'] ?? null)
                ? (float) $aiEvaluation['score']
                : $gradeResult['score'];

            $attempt->update([
                'submitted_at'    => now(),
                'score'           => $finalScore,
                'total_correct'   => $gradeResult['totalCorrect'],
                'status'          => 'graded',
                'ai_evaluation'   => $aiEvaluation,
                'ai_evaluated_at' => $aiEvaluation ? now() : null,
            ]);

            return $this->success([
                'score'           => $finalScore,
                'total_correct'   => $gradeResult['totalCorrect'],
                'ai_evaluation'   => $aiEvaluation,
                'violation_count' => $attempt->violation_count,
            ], 'Nộp bài thành công');
        });
    }

    public function result(Request $request, Exam $exam)
    {
        $this->checkAccess($request, $exam);
        abort_if(!$exam->show_result, 403, 'Kết quả bài thi chưa được giáo viên công bố');
        $attempt = $exam->attempts()->with('answers.question')->where('student_id', $request->user()->id)->whereNotNull('submitted_at')->latest()->firstOrFail();
        return $this->success(new ExamAttemptResource($attempt));
    }

    public function logViolation(Request $request, Exam $exam, ExamAttempt $attempt)
    {
        abort_unless($attempt->exam_id === $exam->id && $attempt->student_id === $request->user()->id, 403, 'Không có quyền');
        abort_unless($attempt->status === ExamAttemptStatus::InProgress, 422, 'Bài thi đã kết thúc');

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
            return $this->error('Bài thi đã bị kết thúc do vi phạm quy định', 422);
        }

        return $this->success(['violation_count' => $attempt->fresh()->violation_count], 'Đã ghi nhận vi phạm');
    }

    private function checkAccess(Request $request, Exam $exam): void
    {
        $classroomIds = $request->user()->classrooms()->pluck('classrooms.id');
        abort_unless($classroomIds->contains($exam->classroom_id), 403, 'Không có quyền truy cập');
    }
}
