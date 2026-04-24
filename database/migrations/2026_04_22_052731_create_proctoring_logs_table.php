<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('proctoring_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('exam_attempts')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->enum('violation_type', ['tab_switch', 'camera_off', 'fullscreen_exit', 'multi_tab']);
            $table->dateTime('occurred_at');
            $table->integer('duration_seconds')->default(0);
            $table->json('details')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('proctoring_logs'); }
};
