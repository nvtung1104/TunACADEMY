<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add tracking fields to question_bank table
     */
    public function up(): void
    {
        Schema::table('question_bank', function (Blueprint $table) {
            // Add audio_path if not exists
            if (!Schema::hasColumn('question_bank', 'audio_path')) {
                $table->string('audio_path', 500)->nullable()->after('media_path');
            }
            
            // Add tracking fields if not exists
            if (!Schema::hasColumn('question_bank', 'used_in_exams_count')) {
                $table->unsignedInteger('used_in_exams_count')->default(0)->after('is_public');
            }
            
            if (!Schema::hasColumn('question_bank', 'used_in_assignments_count')) {
                $table->unsignedInteger('used_in_assignments_count')->default(0)->after('used_in_exams_count');
            }
            
            if (!Schema::hasColumn('question_bank', 'last_used_at')) {
                $table->timestamp('last_used_at')->nullable()->after('used_in_assignments_count');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('question_bank', function (Blueprint $table) {
            $table->dropColumn(['audio_path', 'used_in_exams_count', 'used_in_assignments_count', 'last_used_at']);
        });
    }
};
