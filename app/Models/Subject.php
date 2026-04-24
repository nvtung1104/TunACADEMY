<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Subject extends Model
{
    protected $fillable = ['name', 'code', 'color', 'icon', 'applicable_grades', 'status'];
    protected $casts = ['applicable_grades' => 'array'];
    public function teachers()
    {
        return $this->belongsToMany(User::class, 'teacher_subjects', 'subject_id', 'teacher_id')
            ->withPivot('verified', 'verified_at')->withTimestamps();
    }
    public function lessons()     { return $this->hasMany(Lesson::class); }
    public function exams()       { return $this->hasMany(Exam::class); }
    public function assignments() { return $this->hasMany(Assignment::class); }
    public function liveSessions(){ return $this->hasMany(LiveSession::class); }
}
