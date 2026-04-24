<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 20);                    // 10A1, 12A2...
            $table->foreignId('grade_id')->constrained('grades');
            $table->foreignId('school_year_id')->constrained('school_years');
            $table->foreignId('homeroom_teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('max_students')->default(40);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('classrooms'); }
};
