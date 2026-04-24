<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('study_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnDelete();
            $table->integer('view_count')->default(0);
            $table->integer('total_time_seconds')->default(0);
            $table->timestamp('last_viewed_at')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
            $table->unique(['student_id', 'lesson_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('study_progress'); }
};
