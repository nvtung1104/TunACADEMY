<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SchoolYear extends Model
{
    protected $fillable = ['name', 'start_date', 'end_date', 'status'];
    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];
    public function classrooms() { return $this->hasMany(Classroom::class); }
}
