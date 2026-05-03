<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SchoolYear extends Model
{
    protected $fillable = ['name', 'start_date', 'end_date'];
    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];
    protected $appends = ['status'];

    public function getStatusAttribute(): string
    {
        if (!$this->start_date || !$this->end_date) return 'active';
        $today = now()->startOfDay();
        if ($today->lt($this->start_date)) return 'upcoming';
        if ($today->gt($this->end_date))   return 'ended';
        return 'active';
    }

    public function classrooms() { return $this->hasMany(Classroom::class); }
}
