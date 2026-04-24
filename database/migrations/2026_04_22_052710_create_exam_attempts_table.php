<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('started_at');
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('expires_at');
            $table->decimal('score', 5, 2)->nullable();
            $table->integer('total_correct')->default(0);
            $table->enum('status', ['in_progress', 'submitted', 'graded', 'banned'])->default('in_progress');
            $table->integer('violation_count')->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('exam_attempts'); }
};
