<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LiveAttendance extends Model
{
    protected $fillable = ['session_id', 'student_id', 'join_time', 'total_time_seconds', 'status', 'attendance_percent', 'note', 'confirmed_at', 'confirmed_by'];
    protected $casts = ['join_time' => 'datetime', 'confirmed_at' => 'datetime'];
    public function liveSession() { return $this->belongsTo(LiveSession::class); }
    public function student()     { return $this->belongsTo(User::class, 'student_id'); }
    public function confirmedBy() { return $this->belongsTo(User::class, 'confirmed_by'); }
}
