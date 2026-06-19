<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AiChatLog extends Model
{
    protected $fillable = ['session_id', 'role', 'content', 'tokens_used', 'response_time_ms', 'flagged'];
    protected $casts = ['flagged' => 'boolean'];
    public function session() { return $this->belongsTo(AiChatSession::class, 'session_id'); }
}
