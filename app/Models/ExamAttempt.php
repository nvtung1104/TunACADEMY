<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ExamAttempt extends Model
{
    protected $fillable = [
        'exam_id', 'student_id', 'started_at', 'submitted_at', 'expires_at',
        'score', 'total_correct', 'status', 'violation_count', 'ip_address', 'user_agent',
    ];
    protected $casts = ['started_at' => 'datetime', 'submitted_at' => 'datetime', 'expires_at' => 'datetime'];
    public function exam()           { return $this->belongsTo(Exam::class); }
    public function student()        { return $this->belongsTo(User::class, 'student_id'); }
    public function answers()        { return $this->hasMany(StudentAnswer::class, 'attempt_id'); }
    public function proctoringLogs() { return $this->hasMany(ProctoringLog::class, 'attempt_id'); }
    public function isExpired(): bool { return now()->gt($this->expires_at); }
    public function remainingSeconds(): int { return max(0, now()->diffInSeconds($this->expires_at, false)); }
}
