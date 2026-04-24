<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AssignmentSubmission extends Model
{
    protected $fillable = [
        'assignment_id', 'student_id', 'content', 'file_paths',
        'submitted_at', 'is_late', 'status', 'score', 'feedback', 'graded_at', 'graded_by',
    ];
    protected $casts = [
        'submitted_at' => 'datetime', 'graded_at' => 'datetime',
        'is_late' => 'boolean', 'file_paths' => 'array',
    ];
    public function assignment() { return $this->belongsTo(Assignment::class); }
    public function student()    { return $this->belongsTo(User::class, 'student_id'); }
    public function gradedBy()   { return $this->belongsTo(User::class, 'graded_by'); }
}
