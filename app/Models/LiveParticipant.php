<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LiveParticipant extends Model
{
    protected $fillable = ['live_session_id', 'user_id', 'joined_at', 'left_at', 'is_muted', 'is_cam_off', 'is_hand_raised'];
    protected $casts = ['joined_at' => 'datetime', 'left_at' => 'datetime', 'is_muted' => 'boolean', 'is_cam_off' => 'boolean', 'is_hand_raised' => 'boolean'];
    public function liveSession() { return $this->belongsTo(LiveSession::class); }
    public function user()        { return $this->belongsTo(User::class); }
}
