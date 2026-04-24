<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LiveAttendance extends Model
{
    protected $fillable = ['live_session_id', 'student_id', 'joined_at', 'left_at', 'duration_seconds', 'present', 'confirmed_by'];
    protected $casts = ['joined_at' => 'datetime', 'left_at' => 'datetime', 'present' => 'boolean'];
    public function liveSession() { return $this->belongsTo(LiveSession::class); }
    public function student()     { return $this->belongsTo(User::class, 'student_id'); }
    public function confirmedBy() { return $this->belongsTo(User::class, 'confirmed_by'); }
}
