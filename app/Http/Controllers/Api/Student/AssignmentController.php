<?php
namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\{Assignment, AssignmentSubmission};
use App\Services\AiSubmissionEvaluationService;
use App\Services\AssignmentGradingService;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function __construct(
        private readonly AiSubmissionEvaluationService $aiEvaluator,
        private readonly AssignmentGradingService $gradingService,
    ) {}

    public function index(Request $request)
    {
        $classroomIds = $request->user()->classrooms()->pluck('classrooms.id');
        $assignments = Assignment::with(['subject', 'classroom'])
            ->whereIn('classroom_id', $classroomIds)
            ->published()
            ->where('visibility', '!=', 'private')
            ->when($request->classroom_id, fn($q) => $q->where('classroom_id', $request->classroom_id))
            ->latest('deadline')->paginate(20);
        return $this->success($assignments);
    }

    public function show(Request $request, Assignment $assignment)
    {
        $this->checkAccess($request, $assignment);
        $submission = $assignment->submissions()->where('student_id', $request->user()->id)->first();
        return $this->success(['assignment' => $assignment->load(['subject', 'questions']), 'submission' => $submission]);
    }

    public function submit(Request $request, Assignment $assignment)
    {
        $this->checkAccess($request, $assignment);
        abort_if(!$assignment->allow_late && $assignment->isOverdue(), 422, 'Đã quá hạn nộp bài');
        abort_if($assignment->submissions()->where('student_id', $request->user()->id)->exists(), 422, 'Bạn đã nộp bài này rồi');
        $request->validate([
            'content'  => 'nullable|string|max:50000',
            'files'    => 'nullable|array|max:5',
            'files.*'  => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,gif,mp3,mp4,zip',
        ]);

        $filePaths = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $filePaths[] = $file->store("submissions/{$assignment->id}", 'public');
            }
        }
        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id'    => $request->user()->id,
            'content'       => $request->content,
            'file_paths'    => $filePaths,
            'submitted_at'  => now(),
            'is_late'       => $assignment->isOverdue(),
        ]);

        $parsedContent     = json_decode((string) $request->content, true);
        $normalizedAnswers = is_array($parsedContent) ? $parsedContent : ['text_content' => (string) $request->content];

        // Server-side grading for structured (auto-graded) question types
        $gradeResult = $this->gradingService->grade($submission, $assignment, $normalizedAnswers);

        $questions = $assignment->questions()->get();
        $aiEvaluation = $this->aiEvaluator->evaluateAssignment([
            'assignment' => [
                'id'          => $assignment->id,
                'title'       => $assignment->title,
                'description' => $assignment->description,
                'type'        => $assignment->type,
                'subject'     => $assignment->subject?->name,
                'max_score'   => $assignment->max_score,
                'questions'   => $questions->map(fn($q) => [
                    'question_id'    => $q->id,
                    'type'           => $q->type,
                    'content'        => $q->content,
                    'correct_answer' => $q->correct_answer,
                    'points'         => (float) ($q->points ?? 0),
                ])->values(),
            ],
            'student' => [
                'id'   => $request->user()->id,
                'name' => $request->user()->name,
            ],
            'submission' => [
                'text_or_answers'    => $normalizedAnswers,
                'has_uploaded_files' => count($filePaths) > 0,
                'file_paths'         => $filePaths,
            ],
        ]);

        // Prefer server-computed score for structured submissions; fall back to AI score
        $score = $gradeResult['score']
            ?? (is_numeric($aiEvaluation['score'] ?? null) ? (float) $aiEvaluation['score'] : null);

        $submission->update([
            'score'           => $score,
            'status'          => $score !== null ? 'graded' : 'submitted',
            'feedback'        => $aiEvaluation['competency_comment'] ?? null,
            'ai_evaluation'   => $aiEvaluation,
            'ai_evaluated_at' => $aiEvaluation ? now() : null,
        ]);

        return $this->success($submission, 'Nộp bài thành công', 201);
    }

    private function checkAccess(Request $request, Assignment $assignment): void
    {
        $classroomIds = $request->user()->classrooms()->pluck('classrooms.id');
        abort_unless($classroomIds->contains($assignment->classroom_id), 403, 'Không có quyền truy cập');
    }
}
