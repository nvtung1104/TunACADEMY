<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LiveChat extends Model
{
    protected $fillable = ['session_id', 'user_id', 'message', 'type', 'file_path', 'is_pinned', 'target_room', 'breakout_room_id'];
    public function liveSession() { return $this->belongsTo(LiveSession::class, 'session_id'); }
    public function user()        { return $this->belongsTo(User::class); }
}
