<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);                   // Toán, Ngữ văn...
            $table->string('code', 20)->unique();          // MATH, VAN, ENG...
            $table->string('color', 7)->default('#3B82F6'); // HEX màu giao diện
            $table->string('icon', 50)->nullable();
            $table->json('applicable_grades')->nullable(); // [1,2,3,...,12]
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('subjects'); }
};
