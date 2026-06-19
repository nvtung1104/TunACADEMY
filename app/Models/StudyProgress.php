<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StudyProgress extends Model
{
    protected $fillable = ['lesson_id', 'student_id', 'total_time_seconds', 'is_completed', 'last_viewed_at', 'view_count'];
    protected $casts = ['is_completed' => 'boolean', 'last_viewed_at' => 'datetime'];
    public function lesson()  { return $this->belongsTo(Lesson::class); }
    public function student() { return $this->belongsTo(User::class, 'student_id'); }
}
