<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\LessonMaterial;
use Illuminate\Database\Seeder;

class LessonMaterialSeeder extends Seeder
{
    public function run(): void
    {
        $lesson = Lesson::first();

        LessonMaterial::updateOrCreate(
            [
                'lesson_id' => $lesson->id,
                'file_name' => 'tai-lieu-bai-giang.pdf',
            ],
            [
                'file_path' => 'materials/tai-lieu-bai-giang.pdf',
                'file_type' => 'pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 102400,
                'download_count' => 0,
            ]
        );
    }
}