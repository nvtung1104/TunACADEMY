<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ProctoringLog extends Model
{
    protected $fillable = ['attempt_id', 'student_id', 'violation_type', 'duration_seconds', 'details', 'occurred_at'];
    protected $casts = ['occurred_at' => 'datetime', 'details' => 'array'];
    public function attempt() { return $this->belongsTo(ExamAttempt::class, 'attempt_id'); }
    public function student() { return $this->belongsTo(User::class, 'student_id'); }
}
