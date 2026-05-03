<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LiveParticipant extends Model
{
    protected $fillable = ['session_id', 'user_id', 'role', 'joined_at', 'left_at', 'total_time_seconds', 'mic_enabled', 'cam_enabled', 'is_present', 'device_info'];
    protected $casts = ['joined_at' => 'datetime', 'left_at' => 'datetime', 'mic_enabled' => 'boolean', 'cam_enabled' => 'boolean', 'is_present' => 'boolean', 'device_info' => 'array'];
    public function liveSession() { return $this->belongsTo(LiveSession::class, 'session_id'); }
    public function user()        { return $this->belongsTo(User::class); }
}
