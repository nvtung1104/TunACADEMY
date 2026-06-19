<?php

namespace App\Models;

use App\Enums\ExamType;
use App\Enums\ShowResult;
use App\Enums\Visibility;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = [
        'classroom_id', 'subject_id', 'teacher_id', 'title', 'thumbnail', 'description',
        'type', 'duration_minutes', 'opened_at', 'closed_at',
        'shuffle_questions', 'shuffle_options', 'proctoring_enabled', 'max_violations',
        'show_result', 'allow_retake', 'status', 'visibility',
    ];

    protected $casts = [
        'opened_at'          => 'datetime',
        'closed_at'          => 'datetime',
        'shuffle_questions'  => 'boolean',
        'shuffle_options'    => 'boolean',
        'proctoring_enabled' => 'boolean',
        'allow_retake'       => 'boolean',
        'type'               => ExamType::class,
        'visibility'         => Visibility::class,
        'show_result'        => ShowResult::class,
    ];

    public function classroom() { return $this->belongsTo(Classroom::class); }
    public function subject()   { return $this->belongsTo(Subject::class); }
    public function teacher()   { return $this->belongsTo(User::class, 'teacher_id'); }
    public function questions() { return $this->hasMany(ExamQuestion::class)->orderBy('order_index'); }
    public function attempts()  { return $this->hasMany(ExamAttempt::class); }
    public function shares()    { return $this->morphMany(ContentShare::class, 'shareable'); }

    public function isOpen(): bool
    {
        if ($this->type === ExamType::PracticeExam) {
            return $this->status === 'published';
        }
        return $this->status === 'published'
            && $this->opened_at && $this->closed_at
            && now()->between($this->opened_at, $this->closed_at);
    }

    public function isPracticeExam(): bool { return $this->type === ExamType::PracticeExam; }

    public function getDurationAttribute(): int
    {
        if ($this->type instanceof ExamType && $this->type->isTimeLimited()) {
            return $this->type->durationMinutes();
        }
        return $this->duration_minutes ?? 0;
    }

    public function scopePublished($q) { return $q->where('status', 'published'); }
}
