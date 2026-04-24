<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AiChatLog extends Model
{
    protected $fillable = ['session_id', 'role', 'content', 'tokens_used'];
    public function session() { return $this->belongsTo(AiChatSession::class, 'session_id'); }
}
