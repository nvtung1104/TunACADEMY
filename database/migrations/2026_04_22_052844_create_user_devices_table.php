<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('device_token');                 // FCM Registration Token
            $table->enum('platform', ['web', 'android', 'ios'])->default('web');
            $table->string('device_name', 100)->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('user_devices'); }
};