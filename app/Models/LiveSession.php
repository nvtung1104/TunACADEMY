<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LiveSession extends Model
{
    protected $fillable = [
        'classroom_id', 'subject_id', 'teacher_id', 'lesson_id', 'title', 'description',
        'room_code', 'scheduled_at', 'started_at', 'ended_at', 'duration_minutes', 'max_participants',
        'allow_screen_share', 'allow_student_mic', 'allow_student_cam',
        'chat_enabled', 'recording_enabled', 'recording_path', 'status', 'is_permanent',
        'active_lesson_id', 'active_assignment_id', 'presentation_type',
    ];
    protected $casts = [
        'scheduled_at' => 'datetime', 'started_at' => 'datetime', 'ended_at' => 'datetime',
        'allow_screen_share' => 'boolean', 'allow_student_mic' => 'boolean',
        'allow_student_cam' => 'boolean', 'chat_enabled' => 'boolean', 'recording_enabled' => 'boolean',
        'is_permanent' => 'boolean',
    ];
    public function classroom()    { return $this->belongsTo(Classroom::class); }
    public function subject()      { return $this->belongsTo(Subject::class); }
    public function teacher()      { return $this->belongsTo(User::class, 'teacher_id'); }
    public function lesson()       { return $this->belongsTo(Lesson::class); }
    public function activeLesson() { return $this->belongsTo(Lesson::class, 'active_lesson_id'); }
    public function activeAssignment() { return $this->belongsTo(Assignment::class, 'active_assignment_id'); }
    public function participants() { return $this->hasMany(LiveParticipant::class, 'session_id'); }
    public function attendances()  { return $this->hasMany(LiveAttendance::class); }
    public function breakoutRooms(){ return $this->hasMany(BreakoutRoom::class); }
    public function chats()        { return $this->hasMany(LiveChat::class, 'session_id'); }
    public function isLive(): bool { return $this->status === 'live'; }
}
