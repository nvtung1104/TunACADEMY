<?php

namespace Database\Seeders;

use App\Models\SchoolYear;
use Illuminate\Database\Seeder;

class SchoolYearSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name' => '2024-2025',
                'start_date' => '2024-09-01',
                'end_date' => '2025-05-31',
                'status' => 'locked',
            ],
            [
                'name' => '2025-2026',
                'start_date' => '2025-09-01',
                'end_date' => '2026-05-31',
                'status' => 'active',
            ],
            [
                'name' => '2026-2027',
                'start_date' => '2026-09-01',
                'end_date' => '2027-05-31',
                'status' => 'upcoming',
            ],
        ];

        foreach ($items as $item) {
            SchoolYear::updateOrCreate(
                ['name' => $item['name']],
                $item
            );
        }
    }
}