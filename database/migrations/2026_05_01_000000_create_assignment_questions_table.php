<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private const TYPES = [
        'multiple_choice', 'multiple_select', 'true_false',
        'fill_blank', 'short_answer', 'essay',
        'reading', 'listening', 'speaking', 'writing',
        'ordering', 'matching', 'drag_drop',
        'calculation', 'multi_step',
        'data_analysis', 'map_analysis', 'chart_analysis',
        'experiment', 'case_study',
        'code_fill', 'code_debug', 'code_output', 'code_runner',
    ];

    public function up(): void
    {
        Schema::create('assignment_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->cascadeOnDelete();
            $table->foreignId('question_bank_id')->nullable()->constrained('question_bank')->nullOnDelete();
            $table->foreignId('grade_id')->nullable()->constrained('grades')->nullOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained('lessons')->nullOnDelete();
            $table->enum('type', self::TYPES)->default('multiple_choice');
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->string('chapter_tag', 100)->nullable();
            $table->longText('content');
            $table->enum('media_type', ['image', 'audio', 'video'])->nullable();
            $table->string('media_path', 500)->nullable();
            $table->string('audio_path', 500)->nullable();
            $table->json('options')->nullable();
            $table->json('correct_answer')->nullable();
            $table->text('explanation')->nullable();
            $table->json('sub_questions')->nullable();
            $table->json('metadata')->nullable();
            $table->decimal('points', 5, 2)->default(1);
            $table->unsignedInteger('order_index')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_questions');
    }
};
