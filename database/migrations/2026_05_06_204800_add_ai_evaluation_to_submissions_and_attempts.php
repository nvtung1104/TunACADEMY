<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->json('ai_evaluation')->nullable()->after('feedback');
            $table->timestamp('ai_evaluated_at')->nullable()->after('ai_evaluation');
        });

        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->json('ai_evaluation')->nullable()->after('total_correct');
            $table->timestamp('ai_evaluated_at')->nullable()->after('ai_evaluation');
        });
    }

    public function down(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->dropColumn(['ai_evaluation', 'ai_evaluated_at']);
        });

        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropColumn(['ai_evaluation', 'ai_evaluated_at']);
        });
    }
};
