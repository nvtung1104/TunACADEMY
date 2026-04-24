<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeacherSubjectSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::whereHas('role', fn($q) => $q->where('name', 'admin'))->first();
        $teachers = User::whereHas('role', fn($q) => $q->where('name', 'teacher'))->get()->values();

        $map = [
            0 => ['MATH', 'PHYSICS'],
            1 => ['LITERATURE', 'HISTORY'],
            2 => ['ENGLISH', 'INFORMATICS'],
        ];

        foreach ($map as $teacherIndex => $subjectCodes) {
            $teacher = $teachers[$teacherIndex] ?? null;
            if (!$teacher) continue;

            foreach ($subjectCodes as $code) {
                $subject = Subject::where('code', $code)->first();

                TeacherSubject::updateOrCreate(
                    [
                        'teacher_id' => $teacher->id,
                        'subject_id' => $subject->id,
                    ],
                    [
                        'verified' => true,
                        'verified_by' => $admin?->id,
                        'verified_at' => now(),
                    ]
                );
            }
        }
    }
}