<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('breakout_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('live_sessions')->cascadeOnDelete();
            $table->string('name', 100);                  // Nhóm 1, Nhóm A...
            $table->integer('room_index');
            $table->text('topic')->nullable();            // chủ đề thảo luận
            $table->integer('duration_minutes')->default(10);
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->enum('status', ['waiting', 'active', 'ended'])->default('waiting');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('breakout_rooms'); }
};
