<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\ClassroomSubjectTeacher;
use App\Models\SchoolYear;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClassroomSubjectTeacherSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::role('admin')->first();
        $schoolYear = SchoolYear::where('status', 'active')->first();

        $classroom = Classroom::where('name', '10A1')->first();
        $teachers = User::role('teacher')->get()->values();

        $assignments = [
            ['subject_code' => 'MATH', 'teacher_index' => 0],
            ['subject_code' => 'LITERATURE', 'teacher_index' => 1],
            ['subject_code' => 'ENGLISH', 'teacher_index' => 2],
        ];

        foreach ($assignments as $assignment) {
            $subject = Subject::where('code', $assignment['subject_code'])->first();
            $teacher = $teachers[$assignment['teacher_index']] ?? null;

            ClassroomSubjectTeacher::updateOrCreate(
                [
                    'classroom_id' => $classroom->id,
                    'subject_id' => $subject->id,
                    'school_year_id' => $schoolYear->id,
                ],
                [
                    'teacher_id' => $teacher->id,
                    'assigned_by' => $admin->id,
                    'assigned_at' => now(),
                ]
            );
        }
    }
}