<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            // Drop existing FK to allow nullable change
            $table->dropForeign(['classroom_id']);

            // Make classroom_id nullable (exam can exist without a specific class)
            $table->unsignedBigInteger('classroom_id')->nullable()->change();
            $table->foreign('classroom_id')->references('id')->on('classrooms')->nullOnDelete();

            // Make time window nullable (practice_exam has no fixed schedule)
            $table->dateTime('opened_at')->nullable()->change();
            $table->dateTime('closed_at')->nullable()->change();

            // Distinguish quiz types vs practice exam
            $table->enum('type', ['quiz_15', 'quiz_30', 'quiz_45', 'practice_exam'])
                ->default('quiz_15')
                ->after('title');

            // Sharing visibility
            $table->enum('visibility', ['public', 'private', 'class', 'selected'])
                ->default('private')
                ->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropForeign(['classroom_id']);
            $table->unsignedBigInteger('classroom_id')->nullable(false)->change();
            $table->foreign('classroom_id')->references('id')->on('classrooms')->cascadeOnDelete();

            $table->dateTime('opened_at')->nullable(false)->change();
            $table->dateTime('closed_at')->nullable(false)->change();

            $table->dropColumn(['type', 'visibility']);
        });
    }
};
