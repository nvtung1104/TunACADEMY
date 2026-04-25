<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Score extends Model
{
    protected $fillable = ['student_id', 'subject_id', 'school_year_id', 'classroom_id', 'assignment_avg', 'exam_avg', 'final_score', 'classification', 'notes'];
    public function student()    { return $this->belongsTo(User::class, 'student_id'); }
    public function subject()    { return $this->belongsTo(Subject::class); }
    public function schoolYear() { return $this->belongsTo(SchoolYear::class); }
}
