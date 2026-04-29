<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('content_shares', function (Blueprint $table) {
            $table->id();
            // The shared content (Exam, Lesson, or Assignment)
            $table->morphs('shareable');
            // The target (Classroom or User/student)
            $table->string('target_type');
            $table->unsignedBigInteger('target_id');
            $table->index(['target_type', 'target_id']);
            $table->unique(
                ['shareable_type', 'shareable_id', 'target_type', 'target_id'],
                'content_shares_unique'
            );
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_shares');
    }
};
