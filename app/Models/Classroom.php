<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Classroom extends Model
{
    protected $fillable = ['name', 'grade_id', 'school_year_id', 'homeroom_teacher_id', 'max_students', 'status', 'cover_image'];
    public function grade()           { return $this->belongsTo(Grade::class); }
    public function schoolYear()      { return $this->belongsTo(SchoolYear::class); }
    public function homeroomTeacher() { return $this->belongsTo(User::class, 'homeroom_teacher_id'); }
    public function students()
    {
        return $this->belongsToMany(User::class, 'classroom_students', 'classroom_id', 'student_id')
            ->withPivot('joined_at', 'left_at', 'status')->wherePivot('status', 'active');
    }
    public function subjectTeachers() { return $this->hasMany(ClassroomSubjectTeacher::class); }
    public function lessons()         { return $this->hasMany(Lesson::class); }
    public function exams()           { return $this->hasMany(Exam::class); }
    public function assignments()     { return $this->hasMany(Assignment::class); }
    public function liveSessions()    { return $this->hasMany(LiveSession::class); }
    public function studentCount(): int { return $this->students()->count(); }
}
