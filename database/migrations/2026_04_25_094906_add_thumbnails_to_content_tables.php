<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->string('avatar', 500)->nullable()->after('icon');
        });
        Schema::table('classrooms', function (Blueprint $table) {
            $table->string('cover_image', 500)->nullable()->after('status');
        });
        Schema::table('lessons', function (Blueprint $table) {
            $table->string('thumbnail', 500)->nullable()->after('title');
        });
        Schema::table('exams', function (Blueprint $table) {
            $table->string('thumbnail', 500)->nullable()->after('title');
        });
        Schema::table('assignments', function (Blueprint $table) {
            $table->string('thumbnail', 500)->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('subjects',    fn($t) => $t->dropColumn('avatar'));
        Schema::table('classrooms',  fn($t) => $t->dropColumn('cover_image'));
        Schema::table('lessons',     fn($t) => $t->dropColumn('thumbnail'));
        Schema::table('exams',       fn($t) => $t->dropColumn('thumbnail'));
        Schema::table('assignments', fn($t) => $t->dropColumn('thumbnail'));
    }
};
