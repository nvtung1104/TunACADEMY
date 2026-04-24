<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('level')->unsigned();      // 1 → 12
            $table->string('name', 20);                    // Lớp 1, Lớp 2...
            $table->integer('order_index')->default(0);
        });
    }
    public function down(): void { Schema::dropIfExists('grades'); }
};
