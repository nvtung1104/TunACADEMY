<?php

namespace Database\Seeders;

use App\Models\Grade;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            Grade::updateOrCreate(
                ['level' => $i],
                [
                    'name' => 'Lớp ' . $i,
                    'order_index' => $i,
                ]
            );
        }
    }
}