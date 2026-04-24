<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('lesson_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path', 500);
            $table->string('file_type', 50);               // pdf | word | ppt | image | video | audio
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->default(0); // bytes
            $table->integer('download_count')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('lesson_materials'); }
};
