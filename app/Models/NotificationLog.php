<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    protected $fillable = [
        'user_id',
        'channel',
        'type',
        'payload',
        'status',
        'error_message',
        'sent_at',
        'retry_count',
    ];

    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
        'retry_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}