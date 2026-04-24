<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects');
            $table->foreignId('school_year_id')->constrained('school_years');
            $table->decimal('assignment_avg', 5, 2)->nullable();
            $table->decimal('exam_avg', 5, 2)->nullable();
            $table->decimal('final_score', 5, 2)->nullable();
            $table->enum('classification', ['excellent', 'good', 'average', 'below_average', 'fail'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'subject_id', 'school_year_id'], 'unique_student_subject_year');
        });
    }
    public function down(): void { Schema::dropIfExists('scores'); }
};