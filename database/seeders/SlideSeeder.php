<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Slide;
use Illuminate\Database\Seeder;

class SlideSeeder extends Seeder
{
    public function run(): void
    {
        $lesson = Lesson::first();

        Slide::updateOrCreate(
            ['lesson_id' => $lesson->id],
            [
                'original_file_path' => 'slides/bai-giang-1.pptx',
                'converted_paths' => [
                    'slides/converted/page1.png',
                    'slides/converted/page2.png',
                ],
                'page_count' => 2,
                'status' => 'ready',
            ]
        );
    }
}