<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_bank', function (Blueprint $table) {
            $table->unsignedInteger('used_in_exams_count')->default(0)->after('is_public');
            $table->unsignedInteger('used_in_assignments_count')->default(0)->after('used_in_exams_count');
            $table->timestamp('last_used_at')->nullable()->after('used_in_assignments_count');
        });
    }

    public function down(): void
    {
        Schema::table('question_bank', function (Blueprint $table) {
            $table->dropColumn(['used_in_exams_count', 'used_in_assignments_count', 'last_used_at']);
        });
    }
};
