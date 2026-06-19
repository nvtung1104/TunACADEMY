<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Notification;
use App\Models\Schedule;
use App\Models\User;
use App\Services\AiSubmissionEvaluationService;
use App\Mail\ScheduleNotificationMail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SchedulesAndAiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_teacher_can_manage_schedules(): void
    {
        // Find or create a teacher and classroom from seeded data
        $teacher = User::role('teacher')->first() ?: User::factory()->create();
        if (!$teacher->hasRole('teacher')) {
            $teacher->assignRole('teacher');
        }

        $classroom = Classroom::first() ?: Classroom::create([
            'name' => 'Lớp Test',
            'status' => 'active',
        ]);

        Sanctum::actingAs($teacher);

        // Store schedule
        $response = $this->postJson('/api/teacher/schedules', [
            'title' => 'Tiết Toán hình',
            'classroom_id' => $classroom->id,
            'day_of_week' => 2, // Monday
            'start_time' => '08:00',
            'end_time' => '09:30',
            'color' => '#ff0000',
            'note' => 'Ôn tập chương 1',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Tiết Toán hình');

        $scheduleId = $response->json('data.id');

        // List schedules
        $response = $this->getJson("/api/teacher/schedules?classroom_id={$classroom->id}");
        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Update schedule
        $response = $this->putJson("/api/teacher/schedules/{$scheduleId}", [
            'title' => 'Tiết Toán số',
        ]);
        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Tiết Toán số');

        // Delete schedule
        $response = $this->deleteJson("/api/teacher/schedules/{$scheduleId}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('schedules', ['id' => $scheduleId]);
    }

    public function test_student_can_manage_personal_schedules(): void
    {
        $student = User::role('student')->first() ?: User::factory()->create();
        if (!$student->hasRole('student')) {
            $student->assignRole('student');
        }

        Sanctum::actingAs($student);

        // Store personal schedule
        $response = $this->postJson('/api/student/personal-schedules', [
            'title' => 'Tự học tiếng Anh',
            'day_of_week' => 3, // Tuesday
            'start_time' => '19:00',
            'end_time' => '20:30',
            'color' => '#00ff00',
            'note' => 'Học từ vựng mới',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Tự học tiếng Anh');

        $scheduleId = $response->json('data.id');

        // List personal schedules
        $response = $this->getJson('/api/student/personal-schedules');
        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Update personal schedule
        $response = $this->putJson("/api/student/personal-schedules/{$scheduleId}", [
            'title' => 'Tự học IELTS',
        ]);
        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Tự học IELTS');

        // Delete personal schedule
        $response = $this->deleteJson("/api/student/personal-schedules/{$scheduleId}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('schedules', ['id' => $scheduleId]);
    }

    public function test_schedule_check_schedules_command(): void
    {
        Mail::fake();

        $student = User::role('student')->first() ?: User::factory()->create(['email' => 'student-test@tunacademy.com']);
        if (!$student->hasRole('student')) {
            $student->assignRole('student');
        }

        $now = now();
        $dayOfWeek = $now->dayOfWeekIso + 1;
        if ($dayOfWeek > 8) {
            $dayOfWeek = 2;
        }

        // Create a personal schedule for this student starting right now
        $schedule = Schedule::create([
            'title' => 'Lịch tự học thử nghiệm',
            'student_id' => $student->id,
            'day_of_week' => $dayOfWeek,
            'start_time' => $now->format('H:i:s'),
            'end_time' => $now->copy()->addHour()->format('H:i:s'),
            'color' => '#0000ff',
        ]);

        // Run the schedules:check command
        $this->artisan('schedules:check')
            ->expectsOutput('Found 1 schedules to notify.')
            ->expectsOutput("Emailed student: {$student->email}")
            ->expectsOutput("Notified student: {$student->name}")
            ->assertExitCode(0);

        // Check notification is created in database
        $this->assertDatabaseHas('notifications', [
            'user_id' => $student->id,
            'type' => 'personal_schedule',
        ]);

        // Assert mail was sent
        Mail::assertSent(ScheduleNotificationMail::class, function ($mail) use ($student) {
            return $mail->hasTo($student->email);
        });
    }

    public function test_ai_evaluation_service_mock_and_gemini_format(): void
    {
        $service = new AiSubmissionEvaluationService();

        $mockPayload = [
            'subject' => 'Toán học',
            'title' => 'Kiểm tra 15 phút',
            'questions' => [
                [
                    'id' => 1,
                    'is_correct' => false,
                    'student_answer' => 0, // Option A
                    'correct_answer' => 1, // Option B
                    'media_type' => 'image',
                    'media_path' => 'test-image.jpg'
                ],
                [
                    'id' => 2,
                    'is_correct' => true,
                    'student_answer' => 2, // Option C
                    'correct_answer' => 2, // Option C
                    'media_type' => 'text',
                    'media_path' => null
                ]
            ]
        ];

        // Call the mock generator to verify shape
        $result = $service->generateMockReview($mockPayload);

        $this->assertArrayHasKey('question_reviews', $result);
        $this->assertArrayHasKey('competency_comment', $result);
        $this->assertArrayHasKey('study_plan', $result);

        $reviews = $result['question_reviews'];
        $this->assertCount(2, $reviews);

        $firstReview = $reviews[0];
        $this->assertArrayHasKey('question_analysis', $firstReview);
        $this->assertArrayHasKey('options_analysis', $firstReview);
        $this->assertArrayHasKey('image_analysis', $firstReview);
        $this->assertArrayHasKey('error_analysis', $firstReview);
    }
}
