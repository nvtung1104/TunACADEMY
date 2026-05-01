<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignmentQuestion extends Model
{
    protected $fillable = [
        'assignment_id', 'question_bank_id',
        'grade_id', 'lesson_id',
        'type', 'difficulty', 'chapter_tag',
        'content', 'media_type', 'media_path', 'audio_path',
        'options', 'correct_answer', 'explanation',
        'sub_questions', 'metadata',
        'points', 'order_index',
    ];

    protected $casts = [
        'options'        => 'array',
        'correct_answer' => 'array',
        'sub_questions'  => 'array',
        'metadata'       => 'array',
    ];

    public function assignment()  { return $this->belongsTo(Assignment::class); }
    public function bankQuestion(){ return $this->belongsTo(QuestionBank::class, 'question_bank_id'); }
    public function grade()       { return $this->belongsTo(Grade::class); }
    public function lesson()      { return $this->belongsTo(Lesson::class); }

    public function isAutoGraded(): bool
    {
        return in_array($this->type, QuestionBank::AUTO_GRADED);
    }
}
