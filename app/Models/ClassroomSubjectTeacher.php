<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ClassroomSubjectTeacher extends Model
{
    protected $fillable = ['classroom_id', 'subject_id', 'teacher_id', 'school_year_id', 'assigned_by', 'assigned_at'];
    protected $casts = ['assigned_at' => 'datetime'];
    public function classroom()   { return $this->belongsTo(Classroom::class); }
    public function subject()     { return $this->belongsTo(Subject::class); }
    public function teacher()     { return $this->belongsTo(User::class, 'teacher_id'); }
    public function schoolYear()  { return $this->belongsTo(SchoolYear::class); }
    public function assignedBy()  { return $this->belongsTo(User::class, 'assigned_by'); }
}
