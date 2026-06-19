<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    protected $fillable = [
        'title',
        'classroom_id',
        'student_id',
        'teacher_id',
        'day_of_week',
        'start_time',
        'end_time',
        'color',
        'note',
        'last_notified_at',
    ];

    protected $casts = [
        'day_of_week'      => 'integer',
        'last_notified_at' => 'datetime',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
