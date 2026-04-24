<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('live_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects');
            $table->foreignId('teacher_id')->constrained('users');
            $table->foreignId('lesson_id')->nullable()->constrained('lessons')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('room_code', 20)->unique();    // mã phòng 6 ký tự
            $table->dateTime('scheduled_at');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->integer('duration_minutes')->default(45);
            $table->integer('max_participants')->default(50);
            $table->boolean('allow_screen_share')->default(true);
            $table->boolean('allow_student_mic')->default(true);
            $table->boolean('allow_student_cam')->default(true);
            $table->boolean('chat_enabled')->default(true);
            $table->boolean('recording_enabled')->default(false);
            $table->string('recording_path', 500)->nullable();
            $table->enum('status', ['scheduled', 'live', 'ended', 'cancelled'])->default('scheduled');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('live_sessions'); }
};
