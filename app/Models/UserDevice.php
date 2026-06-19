<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class UserDevice extends Model
{
    protected $fillable = ['user_id', 'device_token', 'platform', 'device_name', 'last_active_at'];
    protected $casts = ['last_active_at' => 'datetime'];
    public function user() { return $this->belongsTo(User::class); }
}
