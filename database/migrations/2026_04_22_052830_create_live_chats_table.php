<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('live_chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('live_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->enum('type', ['text', 'file', 'system'])->default('text');
            $table->string('file_path', 500)->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->enum('target_room', ['main', 'breakout'])->default('main');
            $table->foreignId('breakout_room_id')->nullable()->constrained('breakout_rooms')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('live_chats'); }
};
