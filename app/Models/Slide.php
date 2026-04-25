<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Slide extends Model
{
    protected $fillable = ['lesson_id', 'original_file_path', 'converted_paths', 'page_count', 'status'];

    protected $casts = ['converted_paths' => 'array'];
    public function lesson() { return $this->belongsTo(Lesson::class); }
}
