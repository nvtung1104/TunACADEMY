<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('live_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('live_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['host', 'co_host', 'student'])->default('student');
            $table->dateTime('joined_at');
            $table->dateTime('left_at')->nullable();
            $table->integer('total_time_seconds')->default(0);
            $table->boolean('mic_enabled')->default(false);
            $table->boolean('cam_enabled')->default(false);
            $table->boolean('is_present')->default(false); // tham gia >= 50%
            $table->json('device_info')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('live_participants'); }
};
