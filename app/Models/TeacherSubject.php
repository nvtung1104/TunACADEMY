<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TeacherSubject extends Model
{
    protected $fillable = ['teacher_id', 'subject_id', 'verified', 'verified_by', 'verified_at'];
    protected $casts = ['verified' => 'boolean', 'verified_at' => 'datetime'];
    public function teacher()    { return $this->belongsTo(User::class, 'teacher_id'); }
    public function subject()    { return $this->belongsTo(Subject::class); }
    public function verifiedBy() { return $this->belongsTo(User::class, 'verified_by'); }
}
