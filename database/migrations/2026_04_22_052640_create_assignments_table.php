<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects');
            $table->foreignId('teacher_id')->constrained('users');
            $table->string('title');
            $table->longText('description')->nullable();
            $table->enum('type', ['quiz', 'essay', 'fill_blank', 'matching', 'upload', 'listening', 'writing']);
            $table->dateTime('deadline');
            $table->decimal('max_score', 5, 2)->default(10);
            $table->boolean('allow_late')->default(false);
            $table->json('attachment_paths')->nullable();  // file đề bài đính kèm
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('assignments'); }
};
