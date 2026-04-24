<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['name' => 'Toán', 'code' => 'MATH', 'color' => '#3B82F6'],
            ['name' => 'Ngữ văn', 'code' => 'LITERATURE', 'color' => '#EF4444'],
            ['name' => 'Tiếng Anh', 'code' => 'ENGLISH', 'color' => '#10B981'],
            ['name' => 'Vật lý', 'code' => 'PHYSICS', 'color' => '#8B5CF6'],
            ['name' => 'Hóa học', 'code' => 'CHEMISTRY', 'color' => '#F59E0B'],
            ['name' => 'Sinh học', 'code' => 'BIOLOGY', 'color' => '#22C55E'],
            ['name' => 'Lịch sử', 'code' => 'HISTORY', 'color' => '#F97316'],
            ['name' => 'Địa lý', 'code' => 'GEOGRAPHY', 'color' => '#06B6D4'],
            ['name' => 'Tin học', 'code' => 'INFORMATICS', 'color' => '#6366F1'],
        ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate(
                ['code' => $subject['code']],
                [
                    'name' => $subject['name'],
                    'color' => $subject['color'],
                    'icon' => null,
                    'applicable_grades' => range(1, 12),
                    'status' => 'active',
                ]
            );
        }
    }
}