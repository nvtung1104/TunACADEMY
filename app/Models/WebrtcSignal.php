<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class WebrtcSignal extends Model
{
    protected $fillable = ['live_session_id', 'from_user_id', 'to_user_id', 'type', 'payload', 'processed'];
    protected $casts = ['payload' => 'array', 'processed' => 'boolean'];
    public function fromUser() { return $this->belongsTo(User::class, 'from_user_id'); }
    public function toUser()   { return $this->belongsTo(User::class, 'to_user_id'); }
}
