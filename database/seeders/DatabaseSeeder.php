<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            SchoolYearSeeder::class,
            GradeSeeder::class,
            SubjectSeeder::class,
            ClassroomSeeder::class,
            ClassroomStudentSeeder::class,
            TeacherSubjectSeeder::class,
            ClassroomSubjectTeacherSeeder::class,
            LessonSeeder::class,
            LessonMaterialSeeder::class,
            SlideSeeder::class,
            StudyProgressSeeder::class,
            AssignmentSeeder::class,
            AssignmentSubmissionSeeder::class,
            ExamSeeder::class,
            ExamQuestionSeeder::class,
            ExamAttemptSeeder::class,
            StudentAnswerSeeder::class,
            ProctoringLogSeeder::class,
            ScoreSeeder::class,
            LiveSessionSeeder::class,
            LiveParticipantSeeder::class,
            LiveAttendanceSeeder::class,
            BreakoutRoomSeeder::class,
            BreakoutRoomMemberSeeder::class,
            LiveChatSeeder::class,
            NotificationSeeder::class,
            UserDeviceSeeder::class,
            NotificationLogSeeder::class,
            AiChatSessionSeeder::class,
            AiChatLogSeeder::class,
            AiChatRateLimitSeeder::class,
            WebrtcSignalSeeder::class,
        ]);
    }
}