<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LessonMaterial extends Model
{
    protected $fillable = ['lesson_id', 'file_name', 'file_path', 'file_type', 'mime_type', 'file_size', 'download_count'];
    protected $appends  = ['url'];
    public function lesson() { return $this->belongsTo(Lesson::class); }
    public function getUrlAttribute(): string { return asset("storage/{$this->file_path}"); }
}
