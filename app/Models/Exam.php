<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Exam extends Model
{
    protected $fillable = [
        'classroom_id', 'subject_id', 'teacher_id', 'title', 'description',
        'duration_minutes', 'opened_at', 'closed_at',
        'shuffle_questions', 'shuffle_options', 'proctoring_enabled', 'max_violations',
        'show_result', 'allow_retake', 'status',
    ];
    protected $casts = [
        'opened_at' => 'datetime', 'closed_at' => 'datetime',
        'shuffle_questions' => 'boolean', 'shuffle_options' => 'boolean',
        'proctoring_enabled' => 'boolean', 'allow_retake' => 'boolean',
    ];
    public function classroom() { return $this->belongsTo(Classroom::class); }
    public function subject()   { return $this->belongsTo(Subject::class); }
    public function teacher()   { return $this->belongsTo(User::class, 'teacher_id'); }
    public function questions() { return $this->hasMany(ExamQuestion::class)->orderBy('order_index'); }
    public function attempts()  { return $this->hasMany(ExamAttempt::class); }
    public function isOpen(): bool   { return $this->status === 'published' && now()->between($this->opened_at, $this->closed_at); }
    public function scopePublished($q) { return $q->where('status', 'published'); }
}
