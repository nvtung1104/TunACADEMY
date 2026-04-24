<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BreakoutRoom extends Model
{
    protected $fillable = ['live_session_id', 'name', 'started_at', 'ended_at'];
    protected $casts = ['started_at' => 'datetime', 'ended_at' => 'datetime'];
    public function liveSession() { return $this->belongsTo(LiveSession::class); }
    public function members()     { return $this->hasMany(BreakoutRoomMember::class); }
}
