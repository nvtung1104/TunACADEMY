<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ExamQuestion extends Model
{
    protected $fillable = [
        'exam_id', 'type', 'content', 'options', 'correct_answer',
        'points', 'audio_path', 'explanation', 'order_index', 'chapter_tag',
    ];
    protected $casts = ['options' => 'array'];
    public function exam()    { return $this->belongsTo(Exam::class); }
    public function answers() { return $this->hasMany(StudentAnswer::class, 'question_id'); }
}
