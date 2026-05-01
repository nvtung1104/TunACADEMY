<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Admin\ClassroomController as AdminClassroomController;
use App\Http\Controllers\Api\Admin\SchoolYearController;
use App\Http\Controllers\Api\Admin\GradeController;
use App\Http\Controllers\Api\Admin\SubjectController;
use App\Http\Controllers\Api\Admin\ReportController;
use App\Http\Controllers\Api\Admin\ContentController as AdminContentController;
use App\Http\Controllers\Api\Teacher\ClassroomController as TeacherClassroomController;
use App\Http\Controllers\Api\Teacher\SubjectController as TeacherSubjectController;
use App\Http\Controllers\Api\Teacher\LessonController;
use App\Http\Controllers\Api\Teacher\ExamController as TeacherExamController;
use App\Http\Controllers\Api\Teacher\AssignmentController as TeacherAssignmentController;
use App\Http\Controllers\Api\Teacher\QuestionBankController;
use App\Http\Controllers\Api\Student\ClassroomController as StudentClassroomController;
use App\Http\Controllers\Api\Student\LessonController as StudentLessonController;
use App\Http\Controllers\Api\Student\ExamController as StudentExamController;
use App\Http\Controllers\Api\Student\AssignmentController as StudentAssignmentController;
use App\Http\Controllers\Api\Student\LiveController as StudentLiveController;
use App\Http\Controllers\Api\Live\WebRTCController;
use App\Http\Controllers\Api\Notification\NotificationController;
use App\Http\Controllers\Api\AiChatController;
use App\Http\Controllers\Api\BookmarkController;
use App\Http\Controllers\Api\Public\PublicController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ─── Public (no auth) ────────────────────────────────────────────────────────

Route::prefix('public')->group(function () {
    Route::get('/grades', [PublicController::class, 'grades']);
    Route::get('/subjects', [PublicController::class, 'subjects']);
    Route::get('/lessons', [PublicController::class, 'lessons']);
    Route::get('/lessons/{lesson}', [PublicController::class, 'lesson']);
    Route::get('/exams', [PublicController::class, 'exams']);
    Route::get('/exams/{exam}', [PublicController::class, 'exam']);
    Route::get('/assignments', [PublicController::class, 'assignments']);
    Route::get('/assignments/{assignment}', [PublicController::class, 'assignment']);
    Route::get('/classrooms', [PublicController::class, 'classrooms']);
});

// ─── Auth ────────────────────────────────────────────────────────────────────

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// ─── Protected ───────────────────────────────────────────────────────────────

Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/avatar', [AuthController::class, 'updateAvatar']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
    });

    // Bookmarks
    Route::prefix('bookmarks')->group(function () {
        Route::get('/', [BookmarkController::class, 'index']);
        Route::post('/toggle', [BookmarkController::class, 'toggle']);
        Route::get('/check', [BookmarkController::class, 'check']);
    });

    // AI Chat
    Route::prefix('ai-chat')->group(function () {
        Route::get('/history', [AiChatController::class, 'history']);
        Route::post('/message', [AiChatController::class, 'sendMessage']);
        Route::delete('/history', [AiChatController::class, 'clearHistory']);
    });

    // Live / WebRTC (teachers + students)
    Route::prefix('live')->group(function () {
        Route::get('/ice-servers', [WebRTCController::class, 'iceServers']);
        Route::post('/signal', [WebRTCController::class, 'signal']);
        Route::post('/rooms/{room}/join', [WebRTCController::class, 'joinRoom']);
        Route::post('/rooms/{room}/leave', [WebRTCController::class, 'leaveRoom']);
    });

    // ─── Admin ───────────────────────────────────────────────────────────────

    Route::middleware('role:admin')->prefix('admin')->as('admin.')->group(function () {

        Route::apiResource('users', AdminUserController::class);
        Route::post('users/{user}/assign-role', [AdminUserController::class, 'assignRole']);
        Route::post('users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus']);

        Route::apiResource('classrooms', AdminClassroomController::class);
        Route::post('classrooms/{classroom}/students', [AdminClassroomController::class, 'addStudent']);
        Route::delete('classrooms/{classroom}/students/{student}', [AdminClassroomController::class, 'removeStudent']);
        Route::post('classrooms/{classroom}/cover', [AdminClassroomController::class, 'uploadCover']);

        Route::apiResource('school-years', SchoolYearController::class);

        Route::get('grades', [GradeController::class, 'index']);
        Route::get('grades/{grade}', [GradeController::class, 'show']);

        Route::apiResource('subjects', SubjectController::class);
        Route::post('subjects/{subject}/avatar', [SubjectController::class, 'uploadAvatar']);

        Route::prefix('content')->group(function () {
            Route::get('/lessons',                                          [AdminContentController::class, 'lessons']);
            Route::post('/lessons',                                         [AdminContentController::class, 'storeLesson']);
            Route::put('/lessons/{lesson}',                                 [AdminContentController::class, 'updateLesson']);
            Route::delete('/lessons/{lesson}',                              [AdminContentController::class, 'deleteLesson']);
            Route::post('/lessons/{lesson}/materials',                      [AdminContentController::class, 'uploadMaterial']);
            Route::delete('/lessons/{lesson}/materials/{material}',         [AdminContentController::class, 'deleteMaterial']);
            Route::get('/exams',                                    [AdminContentController::class, 'exams']);
            Route::post('/exams',                               [AdminContentController::class, 'storeExam']);
            Route::get('/exams/{exam}',                         [AdminContentController::class, 'examDetail']);
            Route::put('/exams/{exam}',                         [AdminContentController::class, 'updateExam']);
            Route::delete('/exams/{exam}',                      [AdminContentController::class, 'deleteExam']);
            Route::get('/exams/{exam}/attempts',                [AdminContentController::class, 'examAttempts']);
            Route::post('/media/audio',                          [AdminContentController::class, 'uploadAudio']);
            Route::get('/exams/{exam}/questions',               [AdminContentController::class, 'examQuestions']);
            Route::post('/exams/{exam}/questions',              [AdminContentController::class, 'storeExamQuestion']);
            Route::put('/exams/{exam}/questions/{question}',    [AdminContentController::class, 'updateExamQuestion']);
            Route::delete('/exams/{exam}/questions/{question}', [AdminContentController::class, 'deleteExamQuestion']);
            Route::get('/assignments',                          [AdminContentController::class, 'assignments']);
            Route::post('/assignments',                         [AdminContentController::class, 'storeAssignment']);
            Route::get('/assignments/{assignment}',             [AdminContentController::class, 'assignmentDetail']);
            Route::put('/assignments/{assignment}',             [AdminContentController::class, 'updateAssignment']);
            Route::delete('/assignments/{assignment}',          [AdminContentController::class, 'deleteAssignment']);
            Route::get('/assignments/{assignment}/submissions',             [AdminContentController::class, 'assignmentSubmissions']);
            Route::get('/assignments/{assignment}/questions',               [AdminContentController::class, 'assignmentQuestions']);
            Route::post('/assignments/{assignment}/questions',              [AdminContentController::class, 'storeAssignmentQuestion']);
            Route::put('/assignments/{assignment}/questions/{question}',    [AdminContentController::class, 'updateAssignmentQuestion']);
            Route::delete('/assignments/{assignment}/questions/{question}', [AdminContentController::class, 'deleteAssignmentQuestion']);
            Route::get('/live-sessions',                 [AdminContentController::class, 'liveSessions']);
            Route::delete('/live-sessions/{liveSession}',[AdminContentController::class, 'deleteLiveSession']);
        });

        Route::prefix('reports')->group(function () {
            Route::get('/overview', [ReportController::class, 'overview']);
            Route::get('/users', [ReportController::class, 'users']);
            Route::get('/exams', [ReportController::class, 'exams']);
            Route::get('/export', [ReportController::class, 'export']);
        });
    });

    // ─── Teacher ─────────────────────────────────────────────────────────────

    Route::middleware('role:teacher')->prefix('teacher')->as('teacher.')->group(function () {

        Route::apiResource('classrooms', TeacherClassroomController::class);
        Route::get('my-subjects', [TeacherSubjectController::class, 'mySubjects']);
        Route::post('classrooms/{classroom}/students', [TeacherClassroomController::class, 'addStudent']);
        Route::delete('classrooms/{classroom}/students/{student}', [TeacherClassroomController::class, 'removeStudent']);
        Route::get('classrooms/{classroom}/room', [TeacherClassroomController::class, 'roomStatus']);
        Route::post('classrooms/{classroom}/room/start', [TeacherClassroomController::class, 'startRoom']);
        Route::post('classrooms/{classroom}/room/end', [TeacherClassroomController::class, 'endRoom']);

        Route::apiResource('lessons', LessonController::class);
        Route::post('lessons/{lesson}/publish', [LessonController::class, 'publish']);
        Route::post('lessons/{lesson}/share', [LessonController::class, 'share']);
        Route::post('lessons/{lesson}/thumbnail', [LessonController::class, 'uploadThumbnail']);
        Route::post('lessons/{lesson}/materials', [LessonController::class, 'storeMaterial']);
        Route::delete('lessons/{lesson}/materials/{material}', [LessonController::class, 'destroyMaterial']);

        Route::apiResource('exams', TeacherExamController::class);
        Route::post('exams/{exam}/thumbnail', [TeacherExamController::class, 'uploadThumbnail']);
        Route::post('exams/{exam}/questions', [TeacherExamController::class, 'storeQuestion']);
        Route::put('exams/{exam}/questions/{question}', [TeacherExamController::class, 'updateQuestion']);
        Route::delete('exams/{exam}/questions/{question}', [TeacherExamController::class, 'destroyQuestion']);
        Route::post('exams/{exam}/publish', [TeacherExamController::class, 'publish']);
        Route::post('exams/{exam}/share', [TeacherExamController::class, 'share']);
        Route::post('exams/{exam}/import-questions', [QuestionBankController::class, 'importToExam']);
        Route::get('exams/{exam}/results', [TeacherExamController::class, 'results']);
        Route::get('exams/{exam}/results/{student}', [TeacherExamController::class, 'studentResult']);
        Route::get('exams/{exam}/attempts', [TeacherExamController::class, 'attempts']);
        Route::get('exams/{exam}/attempts/{attempt}/logs', [TeacherExamController::class, 'attemptLogs']);

        // Ngân hàng câu hỏi
        Route::apiResource('question-bank', QuestionBankController::class);
        Route::get('question-bank-public', [QuestionBankController::class, 'publicBank']);

        Route::apiResource('assignments', TeacherAssignmentController::class);
        Route::post('assignments/{assignment}/share', [TeacherAssignmentController::class, 'share']);
        Route::post('assignments/{assignment}/thumbnail', [TeacherAssignmentController::class, 'uploadThumbnail']);
        Route::get('assignments/{assignment}/submissions', [TeacherAssignmentController::class, 'submissions']);
        Route::post('assignments/{assignment}/submissions/{submission}/grade', [TeacherAssignmentController::class, 'grade']);

    });

    // ─── Student ─────────────────────────────────────────────────────────────

    Route::middleware('role:student')->prefix('student')->as('student.')->group(function () {

        Route::get('classrooms', [StudentClassroomController::class, 'index']);
        Route::get('classrooms/{classroom}', [StudentClassroomController::class, 'show']);
        Route::get('my-rooms', [StudentClassroomController::class, 'myRooms']);

        Route::get('lessons', [StudentLessonController::class, 'index']);
        Route::get('lessons/{lesson}', [StudentLessonController::class, 'show']);
        Route::post('lessons/{lesson}/progress', [StudentLessonController::class, 'updateProgress']);
        Route::get('lessons/{lesson}/materials/{material}/download', [StudentLessonController::class, 'downloadMaterial']);

        Route::get('exams', [StudentExamController::class, 'index']);
        Route::get('exams/{exam}', [StudentExamController::class, 'show']);
        Route::post('exams/{exam}/start', [StudentExamController::class, 'start']);
        Route::post('exams/{exam}/submit', [StudentExamController::class, 'submit']);
        Route::get('exams/{exam}/result', [StudentExamController::class, 'result']);
        Route::post('exams/{exam}/attempts/{attempt}/proctoring', [StudentExamController::class, 'logViolation']);

        Route::get('assignments', [StudentAssignmentController::class, 'index']);
        Route::get('assignments/{assignment}', [StudentAssignmentController::class, 'show']);
        Route::post('assignments/{assignment}/submit', [StudentAssignmentController::class, 'submit']);

        Route::get('live-sessions', [StudentLiveController::class, 'index']);
        Route::get('live-sessions/{session}', [StudentLiveController::class, 'show']);
        Route::post('live-sessions/{session}/join', [StudentLiveController::class, 'join']);
        Route::post('live-sessions/{session}/leave', [StudentLiveController::class, 'leave']);
    });
});
