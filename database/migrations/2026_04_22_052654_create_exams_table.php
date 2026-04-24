<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects');
            $table->foreignId('teacher_id')->constrained('users');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('duration_minutes');
            $table->dateTime('opened_at');
            $table->dateTime('closed_at');
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('shuffle_options')->default(false);
            $table->boolean('proctoring_enabled')->default(false);
            $table->integer('max_violations')->default(3);
            $table->enum('show_result', ['immediate', 'after_close', 'manual'])->default('immediate');
            $table->boolean('allow_retake')->default(false);
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('exams'); }
};
