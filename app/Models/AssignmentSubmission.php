<?php
namespace App\Models;

use App\Enums\SubmissionStatus;
use Illuminate\Database\Eloquent\Model;

class AssignmentSubmission extends Model
{
    protected $fillable = [
        'assignment_id', 'student_id', 'content', 'file_paths',
        'submitted_at', 'is_late', 'status', 'score', 'feedback', 'ai_evaluation', 'ai_evaluated_at', 'graded_at', 'graded_by',
    ];
    protected $casts = [
        'submitted_at'   => 'datetime',
        'graded_at'      => 'datetime',
        'ai_evaluated_at' => 'datetime',
        'is_late'        => 'boolean',
        'file_paths'     => 'array',
        'ai_evaluation'  => 'array',
        'status'         => SubmissionStatus::class,
    ];
    public function assignment() { return $this->belongsTo(Assignment::class); }
    public function student()    { return $this->belongsTo(User::class, 'student_id'); }
    public function gradedBy()   { return $this->belongsTo(User::class, 'graded_by'); }
}
