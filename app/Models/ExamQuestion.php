<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamQuestion extends Model
{
    protected $fillable = [
        'exam_id', 'question_bank_id',
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

    public function exam()         { return $this->belongsTo(Exam::class); }
    public function bankQuestion() { return $this->belongsTo(QuestionBank::class, 'question_bank_id'); }
    public function grade()        { return $this->belongsTo(Grade::class); }
    public function lesson()       { return $this->belongsTo(Lesson::class); }
    public function answers()      { return $this->hasMany(StudentAnswer::class, 'question_id'); }

    public function isAutoGraded(): bool
    {
        return in_array($this->type, QuestionBank::AUTO_GRADED);
    }

    public function hasSubQuestions(): bool
    {
        return in_array($this->type, QuestionBank::HAS_SUB_QUESTIONS);
    }
}

