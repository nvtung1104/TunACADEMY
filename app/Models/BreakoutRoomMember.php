<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BreakoutRoomMember extends Model
{
    public $timestamps = false;
    protected $fillable = ['breakout_room_id', 'student_id'];
    public function breakoutRoom() { return $this->belongsTo(BreakoutRoom::class); }
    public function student()      { return $this->belongsTo(User::class, 'student_id'); }
}
