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
        // This migration is no longer needed as the fields were added in 2026_04_26_130001_update_exam_questions_rich_types.php
        // Keeping it empty to maintain migration history
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
