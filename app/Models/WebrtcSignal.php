<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class WebrtcSignal extends Model
{
    protected $fillable = ['session_id', 'from_user_id', 'to_user_id', 'signal_type', 'payload', 'processed_at'];
    protected $casts = ['payload' => 'array', 'processed_at' => 'datetime'];
    public function fromUser() { return $this->belongsTo(User::class, 'from_user_id'); }
    public function toUser()   { return $this->belongsTo(User::class, 'to_user_id'); }
}
