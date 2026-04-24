<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('live_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('live_sessions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('absent');
            $table->dateTime('join_time')->nullable();
            $table->integer('total_time_seconds')->default(0);
            $table->decimal('attendance_percent', 5, 2)->default(0);
            $table->text('note')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            $table->unique(['session_id', 'student_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('live_attendances'); }
};
