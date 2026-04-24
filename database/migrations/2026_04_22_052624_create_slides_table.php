<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('slides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnDelete();
            $table->string('original_file_path', 500);     // file .pptx/.pdf gốc
            $table->json('converted_paths')->nullable();   // ['page1.png','page2.png'...]
            $table->integer('page_count')->default(0);
            $table->enum('status', ['processing', 'ready', 'failed'])->default('processing');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('slides'); }
};
