<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->enum('type', ['multiple_choice', 'fill_blank', 'essay', 'listening', 'reading', 'arrange']);
            $table->longText('content');                   // HTML (có thể chứa ảnh)
            $table->json('options')->nullable();           // [{id:'A',text:'...'}, ...]
            $table->text('correct_answer')->nullable();    // A | [A,C] | text | json
            $table->decimal('points', 5, 2)->default(1);
            $table->string('audio_path', 500)->nullable(); // câu nghe
            $table->text('explanation')->nullable();       // giải thích đáp án
            $table->integer('order_index')->default(0);
            $table->string('chapter_tag', 100)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('exam_questions'); }
};
