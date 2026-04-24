<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AiChatRateLimit extends Model
{
    protected $fillable = ['student_id', 'date', 'message_count', 'token_count'];
    protected $casts = ['date' => 'date'];
    public function student() { return $this->belongsTo(User::class, 'student_id'); }
}
