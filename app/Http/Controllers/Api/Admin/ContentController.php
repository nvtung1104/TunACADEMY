<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentQuestion;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\Lesson;
use App\Models\LessonMaterial;
use App\Models\LiveSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContentController extends Controller
{
    // ─── Lessons ─────────────────────────────────────────────────────────────

    public function lessons(Request $request)
    {
        $query = Lesson::with(['classroom.grade', 'subject:id,name,color', 'teacher:id,name', 'materials'])
            ->when($request->subject_id, fn($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->status,     fn($q) => $q->where('status', $request->status))
            ->when($request->search,     fn($q) => $q->where('title', 'like', "%{$request->search}%"));
        return response()->json($query->orderByDesc('created_at')->paginate(15));
    }

    public function storeLesson(Request $request)
    {
        $data = $request->validate([
            'classroom_id' => 'nullable|exists:classrooms,id',
            'subject_id'   => 'required|exists:subjects,id',
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'content'      => 'nullable|string',
            'video_path'   => 'nullable|string|max:500',
            'order_index'  => 'sometimes|integer|min:0',
            'status'       => 'sometimes|in:draft,published,hidden',
        ]);

        $data['visibility'] = empty($data['classroom_id']) ? 'public' : 'class';
        $lesson = Lesson::create([...$data, 'teacher_id' => $request->user()->id]);
        return $this->success($lesson->load(['classroom', 'subject', 'materials']), 'Tạo bài học thành công', 201);
    }

    public function updateLesson(Request $request, Lesson $lesson)
    {
        $data = $request->validate([
            'classroom_id' => 'nullable|exists:classrooms,id',
            'subject_id'   => 'required|exists:subjects,id',
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'content'      => 'nullable|string',
            'video_path'   => 'nullable|string|max:500',
            'order_index'  => 'sometimes|integer|min:0',
            'status'       => 'sometimes|in:draft,published,hidden',
        ]);

        $data['visibility'] = empty($data['classroom_id']) ? 'public' : 'class';
        $lesson->update($data);
        return $this->success($lesson->fresh(['classroom', 'subject', 'materials']), 'Cập nhật thành công');
    }

    public function deleteLesson(Lesson $lesson)
    {
        foreach ($lesson->materials as $m) {
            Storage::disk('public')->delete($m->file_path);
        }
        $lesson->delete();
        return $this->success(null, 'Đã xóa bài học');
    }

    public function uploadMaterial(Request $request, Lesson $lesson)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx|max:51200',
        ]);

        $file     = $request->file('file');
        $ext      = strtolower($file->getClientOriginalExtension());
        $fileType = match($ext) {
            'pdf'         => 'pdf',
            'doc', 'docx' => 'word',
            'ppt', 'pptx' => 'ppt',
            default       => 'other',
        };
        $path = $file->store("lessons/{$fileType}", 'public');

        $material = $lesson->materials()->create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $fileType,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);

        return $this->success([
            ...$material->toArray(),
            'url' => asset('storage/' . $path),
        ], 'Tải lên thành công', 201);
    }

    public function deleteMaterial(Lesson $lesson, LessonMaterial $material)
    {
        abort_if($material->lesson_id !== $lesson->id, 403);
        Storage::disk('public')->delete($material->file_path);
        $material->delete();
        return $this->success(null, 'Đã xóa tài liệu');
    }

    // ─── Exams ───────────────────────────────────────────────────────────────

    public function exams(Request $request)
    {
        $query = Exam::with(['classroom.grade', 'subject:id,name,color', 'teacher:id,name'])
            ->when($request->subject_id, fn($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->type,       fn($q) => $q->where('type', $request->type))
            ->when($request->status,     fn($q) => $q->where('status', $request->status))
            ->when($request->search,     fn($q) => $q->where('title', 'like', "%{$request->search}%"));
        return response()->json($query->orderByDesc('created_at')->paginate(15));
    }

    public function storeExam(Request $request)
    {
        $type = $request->input('type', 'quiz_15');
        $isPractice = $type === 'practice_exam';

        $data = $request->validate([
            'subject_id'         => 'required|exists:subjects,id',
            'classroom_id'       => 'nullable|exists:classrooms,id',
            'type'               => 'required|in:quiz_15,quiz_30,quiz_45,practice_exam',
            'title'              => 'required|string|max:255',
            'description'        => 'nullable|string',
            'duration_minutes'   => [$isPractice ? 'nullable' : 'required', 'integer', 'min:5', 'max:300'],
            'opened_at'          => [$isPractice ? 'nullable' : 'required', 'nullable', 'date'],
            'closed_at'          => [$isPractice ? 'nullable' : 'required', 'nullable', 'date'],
            'visibility'         => 'sometimes|in:public,private,class,selected',
            'shuffle_questions'  => 'sometimes|boolean',
            'shuffle_options'    => 'sometimes|boolean',
            'proctoring_enabled' => 'sometimes|boolean',
            'max_violations'     => 'sometimes|integer|min:1|max:20',
            'show_result'        => 'sometimes|in:immediate,after_close,manual',
            'allow_retake'       => 'sometimes|boolean',
            'status'             => 'sometimes|in:draft,published,closed',
        ]);

        $exam = Exam::create([...$data, 'teacher_id' => $request->user()->id]);
        return $this->success($exam->load(['classroom', 'subject']), 'Tạo đề thi thành công', 201);
    }

    public function updateExam(Request $request, Exam $exam)
    {
        $type = $request->input('type', $exam->type);
        $isPractice = $type === 'practice_exam';

        $data = $request->validate([
            'subject_id'         => 'required|exists:subjects,id',
            'classroom_id'       => 'nullable|exists:classrooms,id',
            'type'               => 'required|in:quiz_15,quiz_30,quiz_45,practice_exam',
            'title'              => 'required|string|max:255',
            'description'        => 'nullable|string',
            'duration_minutes'   => [$isPractice ? 'nullable' : 'required', 'integer', 'min:5', 'max:300'],
            'opened_at'          => [$isPractice ? 'nullable' : 'required', 'nullable', 'date'],
            'closed_at'          => [$isPractice ? 'nullable' : 'required', 'nullable', 'date'],
            'visibility'         => 'sometimes|in:public,private,class,selected',
            'shuffle_questions'  => 'sometimes|boolean',
            'shuffle_options'    => 'sometimes|boolean',
            'proctoring_enabled' => 'sometimes|boolean',
            'max_violations'     => 'sometimes|integer|min:1|max:20',
            'show_result'        => 'sometimes|in:immediate,after_close,manual',
            'allow_retake'       => 'sometimes|boolean',
            'status'             => 'sometimes|in:draft,published,closed',
        ]);

        $exam->update($data);
        return $this->success($exam->fresh(['classroom', 'subject']), 'Cập nhật thành công');
    }

    public function examDetail(Exam $exam)
    {
        return $this->success($exam->load(['classroom.grade', 'subject:id,name,color', 'teacher:id,name']));
    }

    public function examAttempts(Request $request, Exam $exam)
    {
        $attempts = $exam->attempts()
            ->with('student:id,name,email')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest('submitted_at')
            ->paginate(50);

        return $this->success($attempts);
    }

    public function deleteExam(Exam $exam)
    {
        $exam->delete();
        return $this->success(null, 'Đã xóa đề thi');
    }

    // ─── Assignments ─────────────────────────────────────────────────────────

    public function assignments(Request $request)
    {
        $query = Assignment::with(['classroom.grade', 'subject:id,name,color', 'teacher:id,name'])
            ->when($request->subject_id, fn($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->status,     fn($q) => $q->where('status', $request->status))
            ->when($request->search,     fn($q) => $q->where('title', 'like', "%{$request->search}%"));
        return response()->json($query->orderByDesc('created_at')->paginate(15));
    }

    public function storeAssignment(Request $request)
    {
        $data = $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id'   => 'required|exists:subjects,id',
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'type'         => 'required|in:quiz,essay,fill_blank,matching,upload,listening,writing',
            'deadline'     => 'required|date',
            'max_score'    => 'sometimes|numeric|min:1|max:100',
            'allow_late'   => 'sometimes|boolean',
            'status'       => 'sometimes|in:draft,published,closed',
        ]);

        $assignment = Assignment::create([...$data, 'teacher_id' => $request->user()->id]);
        return $this->success($assignment->load(['classroom', 'subject']), 'Tạo bài tập thành công', 201);
    }

    public function updateAssignment(Request $request, Assignment $assignment)
    {
        $data = $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id'   => 'required|exists:subjects,id',
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'type'         => 'required|in:quiz,essay,fill_blank,matching,upload,listening,writing',
            'deadline'     => 'required|date',
            'max_score'    => 'sometimes|numeric|min:1|max:100',
            'allow_late'   => 'sometimes|boolean',
            'status'       => 'sometimes|in:draft,published,closed',
        ]);

        $assignment->update($data);
        return $this->success($assignment->fresh(['classroom', 'subject']), 'Cập nhật thành công');
    }

    public function assignmentDetail(Assignment $assignment)
    {
        return $this->success($assignment->load(['classroom.grade', 'subject:id,name,color', 'teacher:id,name']));
    }

    public function assignmentSubmissions(Request $request, Assignment $assignment)
    {
        $submissions = $assignment->submissions()
            ->with('student:id,name,email', 'gradedBy:id,name')
            ->latest('submitted_at')
            ->paginate(50);

        return $this->success($submissions);
    }

    public function deleteAssignment(Assignment $assignment)
    {
        $assignment->delete();
        return $this->success(null, 'Đã xóa bài tập');
    }

    // ─── Live Sessions ───────────────────────────────────────────────────────

    public function liveSessions(Request $request)
    {
        $query = LiveSession::with(['classroom.grade', 'subject:id,name,color', 'teacher:id,name'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->where('title', 'like', "%{$request->search}%"));
        return response()->json($query->orderByDesc('created_at')->paginate(15));
    }

    public function storeLiveSession(Request $request)
    {
        $data = $request->validate([
            'classroom_id'    => 'required|exists:classrooms,id',
            'subject_id'      => 'nullable|exists:subjects,id',
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'scheduled_at'    => 'nullable|date',
            'duration_minutes'=> 'integer|min:15|max:480',
            'max_participants'=> 'integer|min:2|max:500',
        ]);

        $classroom = Classroom::find($data['classroom_id']);
        $data['teacher_id']  = $classroom->teacher_id ?? $request->user()->id;
        $data['room_code']   = Str::upper(Str::random(8));
        $data['status']      = 'scheduled';
        $data['is_permanent']= false;

        $session = LiveSession::create($data);
        return $this->success(
            $session->load(['classroom.grade', 'teacher:id,name', 'subject:id,name,color']),
            'Tạo phòng học thành công', 201
        );
    }

    public function createLiveSessionsForAll(Request $request)
    {
        $classrooms = Classroom::whereDoesntHave('liveSessions', fn($q) => $q->where('is_permanent', true))
            ->get();

        $created = 0;
        foreach ($classrooms as $classroom) {
            LiveSession::create([
                'classroom_id'    => $classroom->id,
                'teacher_id'      => $classroom->teacher_id ?? $request->user()->id,
                'title'           => 'Phòng học ' . $classroom->name,
                'room_code'       => Str::upper(Str::random(8)),
                'status'          => 'scheduled',
                'is_permanent'    => true,
                'duration_minutes'=> 45,
                'max_participants'=> 50,
            ]);
            $created++;
        }

        return $this->success(['created' => $created], "Đã tạo {$created} phòng học");
    }

    public function deleteLiveSession(LiveSession $liveSession)
    {
        $liveSession->delete();
        return $this->success(null, 'Đã xóa phòng học');
    }

    // ─── Exam Questions ──────────────────────────────────────────────────────

    public function examQuestions(Exam $exam)
    {
        return $this->success($exam->questions()->get());
    }

    public function storeExamQuestion(Request $request, Exam $exam)
    {
        $data = $this->validateQuestion($request);
        $data['order_index'] = $exam->questions()->max('order_index') + 1;
        $question = $exam->questions()->create($data);
        return $this->success($question->fresh(), 'Thêm câu hỏi thành công', 201);
    }

    public function updateExamQuestion(Request $request, Exam $exam, ExamQuestion $question)
    {
        abort_if($question->exam_id !== $exam->id, 403);
        $question->update($this->validateQuestion($request));
        return $this->success($question->fresh(), 'Cập nhật câu hỏi thành công');
    }

    public function deleteExamQuestion(Exam $exam, ExamQuestion $question)
    {
        abort_if($question->exam_id !== $exam->id, 403);
        $question->delete();
        return $this->success(null, 'Đã xóa câu hỏi');
    }

    // ─── Assignment Questions ────────────────────────────────────────────────

    public function assignmentQuestions(Assignment $assignment)
    {
        return $this->success($assignment->questions()->get());
    }

    public function storeAssignmentQuestion(Request $request, Assignment $assignment)
    {
        $data = $this->validateQuestion($request);
        $data['order_index'] = $assignment->questions()->max('order_index') + 1;
        $question = $assignment->questions()->create($data);
        return $this->success($question->fresh(), 'Thêm câu hỏi thành công', 201);
    }

    public function updateAssignmentQuestion(Request $request, Assignment $assignment, AssignmentQuestion $question)
    {
        abort_if($question->assignment_id !== $assignment->id, 403);
        $question->update($this->validateQuestion($request));
        return $this->success($question->fresh(), 'Cập nhật câu hỏi thành công');
    }

    public function deleteAssignmentQuestion(Assignment $assignment, AssignmentQuestion $question)
    {
        abort_if($question->assignment_id !== $assignment->id, 403);
        $question->delete();
        return $this->success(null, 'Đã xóa câu hỏi');
    }

    public function uploadAudio(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:mp3,wav,ogg,m4a,aac,webm|max:51200',
        ]);

        $path = $request->file('file')->store('media/audio', 'public');

        return $this->success([
            'path' => $path,
            'url'  => asset('storage/' . $path),
        ], 'Tải lên thành công', 201);
    }

    private function validateQuestion(Request $request): array
    {
        return $request->validate([
            'type'           => 'required|in:multiple_choice,multiple_select,true_false,fill_blank,short_answer,essay,ordering,matching,listening,reading,speaking,writing,calculation,drag_drop,multi_step,data_analysis,map_analysis,chart_analysis,experiment,case_study,code_fill,code_debug,code_output,code_runner',
            'content'        => 'required|string',
            'difficulty'     => 'sometimes|in:easy,medium,hard',
            'chapter_tag'    => 'nullable|string|max:100',
            'options'        => 'nullable',
            'correct_answer' => 'nullable',
            'explanation'    => 'nullable|string',
            'points'         => 'sometimes|numeric|min:0.25|max:100',
            'order_index'    => 'sometimes|integer|min:0',
            'media_type'     => 'nullable|in:image,audio,video',
            'media_path'     => 'nullable|string|max:500',
            'audio_path'     => 'nullable|string|max:500',
            'sub_questions'  => 'nullable|array',
            'metadata'       => 'nullable|array',
        ]);
    }
}
