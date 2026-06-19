<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // 15 simplified question types
    private const TYPES = [
        'multiple_choice', 'multiple_select', 'true_false',
        'fill_blank', 'short_answer', 'essay', 'calculation',
        'ordering', 'matching', 'drag_drop',
        'reading', 'listening', 'speaking', 'writing',
    ];

    public function up(): void
    {
        // First, update any invalid data to valid types
        // Map old types to new types
        $typeMapping = [
            'multi_step' => 'essay',
            'data_analysis' => 'reading',
            'map_analysis' => 'reading',
            'chart_analysis' => 'reading',
            'experiment' => 'reading',
            'case_study' => 'essay',
            'code_fill' => 'fill_blank',
            'code_debug' => 'essay',
            'code_output' => 'short_answer',
            'code_runner' => 'essay',
        ];

        foreach ($typeMapping as $oldType => $newType) {
            DB::table('question_bank')->where('type', $oldType)->update(['type' => $newType]);
            DB::table('exam_questions')->where('type', $oldType)->update(['type' => $newType]);
            DB::table('assignment_questions')->where('type', $oldType)->update(['type' => $newType]);
        }

        // Now update the ENUM columns
        $types = implode("','", self::TYPES);
        DB::statement("ALTER TABLE question_bank MODIFY COLUMN type ENUM('{$types}') NOT NULL");
        DB::statement("ALTER TABLE exam_questions MODIFY COLUMN type ENUM('{$types}') NOT NULL");
        DB::statement("ALTER TABLE assignment_questions MODIFY COLUMN type ENUM('{$types}') NOT NULL DEFAULT 'multiple_choice'");
    }

    public function down(): void
    {
        // Restore to 24 types (from previous migration)
        $oldTypes = [
            'multiple_choice', 'multiple_select', 'true_false',
            'fill_blank', 'short_answer', 'essay',
            'reading', 'listening', 'speaking', 'writing',
            'ordering', 'matching', 'drag_drop',
            'calculation', 'multi_step',
            'data_analysis', 'map_analysis', 'chart_analysis',
            'experiment', 'case_study',
            'code_fill', 'code_debug', 'code_output', 'code_runner',
        ];
        
        $types = implode("','", $oldTypes);
        DB::statement("ALTER TABLE question_bank MODIFY COLUMN type ENUM('{$types}') NOT NULL");
        DB::statement("ALTER TABLE exam_questions MODIFY COLUMN type ENUM('{$types}') NOT NULL");
        DB::statement("ALTER TABLE assignment_questions MODIFY COLUMN type ENUM('{$types}') NOT NULL DEFAULT 'multiple_choice'");
    }
};
