<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionBank extends Model
{
    protected $table = 'question_bank';

    protected $fillable = [
        'teacher_id', 'subject_id', 'grade_id', 'lesson_id',
        'type', 'difficulty', 'chapter_tag',
        'content', 'media_type', 'media_path',
        'options', 'correct_answer', 'explanation',
        'sub_questions', 'metadata',
        'default_points', 'is_public',
    ];

    protected $casts = [
        'options'        => 'array',
        'correct_answer' => 'array',
        'sub_questions'  => 'array',
        'metadata'       => 'array',
        'is_public'      => 'boolean',
    ];

    // Types graded automatically
    public const AUTO_GRADED = [
        'multiple_choice', 'multiple_select', 'true_false',
        'fill_blank', 'ordering', 'matching', 'drag_drop',
        'calculation', 'code_output',
    ];

    // Types that require teacher grading
    public const TEACHER_GRADED = [
        'essay', 'short_answer', 'speaking', 'writing',
        'code_debug', 'code_runner',
    ];

    // Types that have sub-questions
    public const HAS_SUB_QUESTIONS = [
        'reading', 'listening', 'multi_step',
        'data_analysis', 'map_analysis', 'chart_analysis',
        'case_study', 'experiment',
    ];

    public function teacher()    { return $this->belongsTo(User::class, 'teacher_id'); }
    public function subject()    { return $this->belongsTo(Subject::class); }
    public function grade()      { return $this->belongsTo(Grade::class); }
    public function lesson()     { return $this->belongsTo(Lesson::class); }
    public function examQuestions() { return $this->hasMany(ExamQuestion::class); }

    public function isAutoGraded(): bool  { return in_array($this->type, self::AUTO_GRADED); }
    public function hasSubQuestions(): bool { return in_array($this->type, self::HAS_SUB_QUESTIONS); }

    public function scopePublic($q)         { return $q->where('is_public', true); }
    public function scopeByType($q, $type)  { return $q->where('type', $type); }
    public function scopeBySubject($q, $id) { return $q->where('subject_id', $id); }
    public function scopeByGrade($q, $id)   { return $q->where('grade_id', $id); }
    public function scopeByDifficulty($q, $d) { return $q->where('difficulty', $d); }
}
