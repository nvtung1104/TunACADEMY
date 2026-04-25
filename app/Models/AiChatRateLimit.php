<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AiChatRateLimit extends Model
{
    public $timestamps = false;
    protected $fillable = ['student_id', 'date', 'daily_count', 'hourly_count', 'last_request_at'];
    protected $casts = ['date' => 'date', 'last_request_at' => 'datetime'];
    public function student() { return $this->belongsTo(User::class, 'student_id'); }
}
