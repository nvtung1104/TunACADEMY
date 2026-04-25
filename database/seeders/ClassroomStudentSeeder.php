<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClassroomStudentSeeder extends Seeder
{
    public function run(): void
    {
        $classroom = Classroom::where('name', '10A1')->first();
        $students = User::role('student')->get();

        foreach ($students as $student) {
            ClassroomStudent::updateOrCreate(
                [
                    'classroom_id' => $classroom->id,
                    'student_id' => $student->id,
                ],
                [
                    'joined_at' => now()->toDateString(),
                    'left_at' => null,
                    'status' => 'active',
                ]
            );
        }
    }
}