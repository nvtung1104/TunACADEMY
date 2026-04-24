<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('channel', ['email', 'zalo', 'push', 'in_app']);
            $table->string('type', 100);
            $table->json('payload')->nullable();
            $table->enum('status', ['success', 'failed', 'pending'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('notification_logs'); }
};
