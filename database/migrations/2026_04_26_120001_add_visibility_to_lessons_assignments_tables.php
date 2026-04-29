<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropForeign(['classroom_id']);
            $table->unsignedBigInteger('classroom_id')->nullable()->change();
            $table->foreign('classroom_id')->references('id')->on('classrooms')->nullOnDelete();

            $table->enum('visibility', ['public', 'private', 'class', 'selected'])
                ->default('private')
                ->after('status');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropForeign(['classroom_id']);
            $table->unsignedBigInteger('classroom_id')->nullable()->change();
            $table->foreign('classroom_id')->references('id')->on('classrooms')->nullOnDelete();

            // deadline nullable for flexible assignments
            $table->dateTime('deadline')->nullable()->change();

            $table->enum('visibility', ['public', 'private', 'class', 'selected'])
                ->default('private')
                ->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropForeign(['classroom_id']);
            $table->unsignedBigInteger('classroom_id')->nullable(false)->change();
            $table->foreign('classroom_id')->references('id')->on('classrooms')->cascadeOnDelete();
            $table->dropColumn('visibility');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropForeign(['classroom_id']);
            $table->unsignedBigInteger('classroom_id')->nullable(false)->change();
            $table->foreign('classroom_id')->references('id')->on('classrooms')->cascadeOnDelete();
            $table->dateTime('deadline')->nullable(false)->change();
            $table->dropColumn('visibility');
        });
    }
};
