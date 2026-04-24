<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 100);                  // new_assignment | exam_result...
            $table->string('title');
            $table->text('message')->nullable();
            $table->json('data')->nullable();             // {assignment_id:5, url:'/...'}
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('notifications'); }
};
