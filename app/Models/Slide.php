<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Slide extends Model
{
    protected $fillable = ['lesson_id', 'title', 'content', 'order_index', 'background_color', 'image_path'];
    public function lesson() { return $this->belongsTo(Lesson::class); }
}
