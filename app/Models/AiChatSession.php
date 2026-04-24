<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AiChatSession extends Model
{
    protected $fillable = ['student_id', 'subject_id', 'title', 'context'];
    public function student() { return $this->belongsTo(User::class, 'student_id'); }
    public function subject() { return $this->belongsTo(Subject::class); }
    public function logs()    { return $this->hasMany(AiChatLog::class, 'session_id'); }
}
