<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('live_sessions', function (Blueprint $table) {
            // Drop foreign key constraint so we can make the column nullable
            $table->dropForeign('live_sessions_subject_id_foreign');

            $table->unsignedBigInteger('subject_id')->nullable()->change();
            $table->foreign('subject_id')->references('id')->on('subjects')->nullOnDelete();

            $table->dateTime('scheduled_at')->nullable()->change();

            // Flag: permanent room tied to a classroom (not a one-off scheduled session)
            $table->boolean('is_permanent')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('live_sessions', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->unsignedBigInteger('subject_id')->nullable(false)->change();
            $table->foreign('subject_id')->references('id')->on('subjects');
            $table->dateTime('scheduled_at')->nullable(false)->change();
            $table->dropColumn('is_permanent');
        });
    }
};
