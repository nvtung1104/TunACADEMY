<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\SchoolYear;
use App\Models\Score;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class ScoreSeeder extends Seeder
{
    public function run(): void
    {
        $classroom = Classroom::where('name', '10A1')->first();
        $schoolYear = SchoolYear::where('status', 'active')->first();
        $subject = Subject::where('code', 'MATH')->first();
        $students = User::role('student')->get();

        foreach ($students as $index => $student) {
            $final = 7.5 + ($index % 3) * 0.5;

            Score::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'school_year_id' => $schoolYear->id,
                ],
                [
                    'classroom_id' => $classroom->id,
                    'assignment_avg' => 7.5,
                    'exam_avg' => 8.0,
                    'final_score' => $final,
                    'classification' => $final >= 8 ? 'good' : 'average',
                    'notes' => 'Điểm mẫu',
                ]
            );
        }
    }
}