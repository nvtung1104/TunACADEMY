<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ai_chat_rate_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->integer('daily_count')->default(0);
            $table->integer('hourly_count')->default(0);
            $table->timestamp('last_request_at')->nullable();
            $table->unique(['student_id', 'date']);
        });
    }
    public function down(): void { Schema::dropIfExists('ai_chat_rate_limits'); }
};
