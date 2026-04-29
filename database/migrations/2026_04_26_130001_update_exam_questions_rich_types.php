<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    // All 24 supported question types
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
        // MySQL can't change ENUM with ->change(), use raw ALTER
        $types = implode("','", self::TYPES);
        DB::statement("ALTER TABLE exam_questions MODIFY COLUMN type ENUM('{$types}') NOT NULL");

        Schema::table('exam_questions', function (Blueprint $table) {
            // Link to question bank (optional — if imported from bank)
            $table->foreignId('question_bank_id')
                ->nullable()
                ->after('exam_id')
                ->constrained('question_bank')
                ->nullOnDelete();

            // Subject/grade/lesson context (can override exam's subject for mixed exams)
            $table->foreignId('grade_id')->nullable()->after('question_bank_id')->constrained('grades')->nullOnDelete();
            $table->foreignId('lesson_id')->nullable()->after('grade_id')->constrained('lessons')->nullOnDelete();

            // Difficulty
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium')->after('chapter_tag');

            // Standalone media (image for map/chart/biology diagrams, video for context)
            $table->enum('media_type', ['image', 'audio', 'video'])->nullable()->after('audio_path');
            $table->string('media_path', 500)->nullable()->after('media_type');

            // Sub-questions for compound types (reading, listening, multi_step, etc.)
            $table->json('sub_questions')->nullable()->after('explanation');

            // Type-specific metadata (tolerance, test_cases, language, etc.)
            $table->json('metadata')->nullable()->after('sub_questions');
        });
    }

    public function down(): void
    {
        Schema::table('exam_questions', function (Blueprint $table) {
            $table->dropForeign(['question_bank_id']);
            $table->dropForeign(['grade_id']);
            $table->dropForeign(['lesson_id']);
            $table->dropColumn([
                'question_bank_id', 'grade_id', 'lesson_id',
                'difficulty', 'media_type', 'media_path',
                'sub_questions', 'metadata',
            ]);
        });

        DB::statement("ALTER TABLE exam_questions MODIFY COLUMN type ENUM('multiple_choice','fill_blank','essay','listening','reading','arrange') NOT NULL");
    }
};
