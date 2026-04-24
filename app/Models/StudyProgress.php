<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StudyProgress extends Model
{
    protected $fillable = ['lesson_id', 'student_id', 'watched_seconds', 'completed', 'last_position', 'completed_at'];
    protected $casts = ['completed' => 'boolean', 'completed_at' => 'datetime'];
    public function lesson()  { return $this->belongsTo(Lesson::class); }
    public function student() { return $this->belongsTo(User::class, 'student_id'); }
}
