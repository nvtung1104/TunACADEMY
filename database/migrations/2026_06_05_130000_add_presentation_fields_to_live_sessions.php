<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('live_sessions', function (Blueprint $table) {
            $table->foreignId('active_lesson_id')->nullable()->after('lesson_id')->constrained('lessons')->nullOnDelete();
            $table->foreignId('active_assignment_id')->nullable()->after('active_lesson_id')->constrained('assignments')->nullOnDelete();
            $table->string('presentation_type')->default('none')->after('active_assignment_id'); // none, lesson, assignment
        });
    }

    public function down(): void
    {
        Schema::table('live_sessions', function (Blueprint $table) {
            $table->dropForeign(['active_lesson_id']);
            $table->dropForeign(['active_assignment_id']);
            $table->dropColumn(['active_lesson_id', 'active_assignment_id', 'presentation_type']);
        });
    }
};
