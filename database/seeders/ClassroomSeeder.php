<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Grade;
use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClassroomSeeder extends Seeder
{
    public function run(): void
    {
        $schoolYear = SchoolYear::where('status', 'active')->first();
        $teachers = User::whereHas('role', fn($q) => $q->where('name', 'teacher'))->get()->values();

        $items = [
            ['name' => '10A1', 'grade_level' => 10, 'teacher_index' => 0],
            ['name' => '11A1', 'grade_level' => 11, 'teacher_index' => 1],
            ['name' => '12A1', 'grade_level' => 12, 'teacher_index' => 2],
        ];

        foreach ($items as $item) {
            $grade = Grade::where('level', $item['grade_level'])->first();
            $teacher = $teachers[$item['teacher_index']] ?? null;

            Classroom::updateOrCreate(
                ['name' => $item['name'], 'school_year_id' => $schoolYear->id],
                [
                    'grade_id' => $grade->id,
                    'homeroom_teacher_id' => $teacher?->id,
                    'max_students' => 40,
                    'status' => 'active',
                ]
            );
        }
    }
}