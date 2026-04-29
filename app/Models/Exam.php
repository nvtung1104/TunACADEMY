<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    // type values:
    //   quiz_15, quiz_30, quiz_45  → bài kiểm tra có thời gian cố định (chống gian lận)
    //   practice_exam              → đề thi ôn tập, học sinh vào tự do, không giới hạn thời gian mở

    // visibility values:
    //   public   → mọi học sinh đều thấy
    //   private  → chỉ giáo viên thấy (nháp)
    //   class    → lớp giáo viên đang dạy
    //   selected → chọn lớp cụ thể / học sinh cụ thể (xem bảng content_shares)

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
    ];

    public function classroom() { return $this->belongsTo(Classroom::class); }
    public function subject()   { return $this->belongsTo(Subject::class); }
    public function teacher()   { return $this->belongsTo(User::class, 'teacher_id'); }
    public function questions() { return $this->hasMany(ExamQuestion::class)->orderBy('order_index'); }
    public function attempts()  { return $this->hasMany(ExamAttempt::class); }
    public function shares()    { return $this->morphMany(ContentShare::class, 'shareable'); }

    public function isOpen(): bool
    {
        if ($this->type === 'practice_exam') {
            return $this->status === 'published';
        }
        return $this->status === 'published'
            && $this->opened_at && $this->closed_at
            && now()->between($this->opened_at, $this->closed_at);
    }

    public function isPracticeExam(): bool { return $this->type === 'practice_exam'; }

    public function getDurationAttribute(): int
    {
        return match ($this->type) {
            'quiz_15' => 15,
            'quiz_30' => 30,
            'quiz_45' => 45,
            default   => $this->duration_minutes ?? 0,
        };
    }

    public function scopePublished($q) { return $q->where('status', 'published'); }
}
