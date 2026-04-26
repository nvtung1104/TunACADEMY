<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Lesson extends Model
{
    protected $fillable = [
        'classroom_id', 'subject_id', 'teacher_id', 'title', 'thumbnail', 'description',
        'content', 'video_path', 'audio_path', 'order_index', 'view_count', 'status', 'published_at',
    ];
    protected $casts = ['published_at' => 'datetime'];
    public function classroom()  { return $this->belongsTo(Classroom::class); }
    public function subject()    { return $this->belongsTo(Subject::class); }
    public function teacher()    { return $this->belongsTo(User::class, 'teacher_id'); }
    public function materials()  { return $this->hasMany(LessonMaterial::class); }
    public function slides()     { return $this->hasMany(Slide::class); }
    public function progress()   { return $this->hasMany(StudyProgress::class); }
    public function liveSessions(){ return $this->hasMany(LiveSession::class); }
    public function scopePublished($q) { return $q->where('status', 'published'); }
}
