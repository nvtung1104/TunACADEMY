<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AiChatSession extends Model
{
    protected $fillable = ['student_id', 'subject_id', 'lesson_id', 'message_count', 'total_tokens', 'started_at', 'ended_at'];
    protected $casts = ['started_at' => 'datetime', 'ended_at' => 'datetime'];
    public function student() { return $this->belongsTo(User::class, 'student_id'); }
    public function subject() { return $this->belongsTo(Subject::class); }
    public function logs()    { return $this->hasMany(AiChatLog::class, 'session_id'); }
}
