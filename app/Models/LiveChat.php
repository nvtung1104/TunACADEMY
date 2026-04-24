<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LiveChat extends Model
{
    protected $fillable = ['live_session_id', 'user_id', 'message', 'type'];
    public function liveSession() { return $this->belongsTo(LiveSession::class); }
    public function user()        { return $this->belongsTo(User::class); }
}
