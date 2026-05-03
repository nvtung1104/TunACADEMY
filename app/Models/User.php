<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'phone',
        'date_of_birth',
        'gender',
        'frame',
        'address',
        'qualification',
        'parent_name',
        'parent_phone',
        'parent_email',
        'parent_address',
        'status',
        'must_change_password',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'must_change_password' => 'boolean',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function homeroomClassrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'homeroom_teacher_id');
    }

    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'classroom_students', 'student_id', 'classroom_id')
            ->withPivot('joined_at', 'left_at', 'status')
            ->wherePivot('status', 'active');
    }

    public function classroomStudents(): HasMany
    {
        return $this->hasMany(ClassroomStudent::class, 'student_id');
    }

    public function teacherSubjects(): HasMany
    {
        return $this->hasMany(TeacherSubject::class, 'teacher_id');
    }

    public function verifiedTeacherSubjects(): HasMany
    {
        return $this->hasMany(TeacherSubject::class, 'verified_by');
    }

    public function assignedClassroomSubjects(): HasMany
    {
        return $this->hasMany(ClassroomSubjectTeacher::class, 'teacher_id');
    }

    public function classroomSubjectAssignmentsCreated(): HasMany
    {
        return $this->hasMany(ClassroomSubjectTeacher::class, 'assigned_by');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'teacher_id');
    }

    public function studyProgresses(): HasMany
    {
        return $this->hasMany(StudyProgress::class, 'student_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'teacher_id');
    }

    public function assignmentSubmissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class, 'student_id');
    }

    public function gradedAssignmentSubmissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class, 'graded_by');
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'teacher_id');
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class, 'student_id');
    }

    public function proctoringLogs(): HasMany
    {
        return $this->hasMany(ProctoringLog::class, 'student_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(Score::class, 'student_id');
    }

    public function liveSessions(): HasMany
    {
        return $this->hasMany(LiveSession::class, 'teacher_id');
    }

    public function liveParticipants(): HasMany
    {
        return $this->hasMany(LiveParticipant::class, 'user_id');
    }

    public function liveAttendances(): HasMany
    {
        return $this->hasMany(LiveAttendance::class, 'student_id');
    }

    public function confirmedLiveAttendances(): HasMany
    {
        return $this->hasMany(LiveAttendance::class, 'confirmed_by');
    }

    public function liveChats(): HasMany
    {
        return $this->hasMany(LiveChat::class, 'user_id');
    }

    public function breakoutRoomMembers(): HasMany
    {
        return $this->hasMany(BreakoutRoomMember::class, 'student_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function userDevices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    public function aiChatSessions(): HasMany
    {
        return $this->hasMany(AiChatSession::class, 'student_id');
    }

    public function aiChatRateLimits(): HasMany
    {
        return $this->hasMany(AiChatRateLimit::class, 'student_id');
    }

    public function sentWebrtcSignals(): HasMany
    {
        return $this->hasMany(WebrtcSignal::class, 'from_user_id');
    }

    public function receivedWebrtcSignals(): HasMany
    {
        return $this->hasMany(WebrtcSignal::class, 'to_user_id');
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? asset("storage/{$this->avatar}") : null;
    }

    public function bookmarks(): HasMany { return $this->hasMany(Bookmark::class); }

    public function isAdmin(): bool    { return $this->hasRole('admin'); }
    public function isTeacher(): bool  { return $this->hasRole('teacher'); }
    public function isStudent(): bool  { return $this->hasRole('student'); }
}