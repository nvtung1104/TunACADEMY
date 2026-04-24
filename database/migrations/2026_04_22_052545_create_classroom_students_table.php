<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('classroom_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->date('joined_at');
            $table->date('left_at')->nullable();
            $table->enum('status', ['active', 'transferred', 'graduated'])->default('active');
            $table->unique(['classroom_id', 'student_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('classroom_students'); }
};
