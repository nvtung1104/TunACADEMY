<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects');
            $table->foreignId('teacher_id')->constrained('users');
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('content')->nullable();       // HTML/Markdown
            $table->string('video_path', 500)->nullable();
            $table->string('audio_path', 500)->nullable();
            $table->integer('order_index')->default(0);
            $table->integer('view_count')->default(0);
            $table->enum('status', ['draft', 'published', 'hidden'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('lessons'); }
};