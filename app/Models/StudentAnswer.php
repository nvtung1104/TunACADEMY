<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StudentAnswer extends Model
{
    protected $fillable = ['attempt_id', 'question_id', 'answer', 'is_correct', 'points_earned', 'answered_at'];
    protected $casts = ['is_correct' => 'boolean', 'answered_at' => 'datetime'];
    public function attempt()  { return $this->belongsTo(ExamAttempt::class, 'attempt_id'); }
    public function question() { return $this->belongsTo(ExamQuestion::class, 'question_id'); }
}
