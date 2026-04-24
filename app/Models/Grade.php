<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Grade extends Model
{
    public $timestamps = false;
    protected $fillable = ['level', 'name', 'order_index'];
    public function classrooms() { return $this->hasMany(Classroom::class); }
}
