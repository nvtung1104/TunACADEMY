<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ClassroomStudent extends Model
{
    public $timestamps = false;
    protected $fillable = ['classroom_id', 'student_id', 'joined_at', 'left_at', 'status'];
    protected $casts = ['joined_at' => 'date', 'left_at' => 'date'];
    public function classroom() { return $this->belongsTo(Classroom::class); }
    public function student()   { return $this->belongsTo(User::class, 'student_id'); }
}
