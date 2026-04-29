<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Assignment extends Model
{
    protected $fillable = [
        'classroom_id', 'subject_id', 'teacher_id', 'title', 'thumbnail', 'description', 'type',
        'deadline', 'max_score', 'allow_late', 'attachment_paths', 'status', 'visibility',
    ];
    protected $casts = ['deadline' => 'datetime', 'allow_late' => 'boolean', 'attachment_paths' => 'array'];
    public function classroom()   { return $this->belongsTo(Classroom::class); }
    public function subject()     { return $this->belongsTo(Subject::class); }
    public function teacher()     { return $this->belongsTo(User::class, 'teacher_id'); }
    public function submissions() { return $this->hasMany(AssignmentSubmission::class); }
    public function shares()      { return $this->morphMany(ContentShare::class, 'shareable'); }
    public function scopePublished($q) { return $q->where('status', 'published'); }
    public function isOverdue(): bool { return $this->deadline && now()->gt($this->deadline); }
}
