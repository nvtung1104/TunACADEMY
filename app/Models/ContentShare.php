<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentShare extends Model
{
    public $timestamps = false;
    public $table = 'content_shares';

    protected $fillable = ['shareable_type', 'shareable_id', 'target_type', 'target_id'];

    public function shareable()
    {
        return $this->morphTo();
    }

    public function target()
    {
        return $this->morphTo();
    }
}
