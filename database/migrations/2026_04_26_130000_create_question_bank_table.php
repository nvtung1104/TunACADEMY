<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('question_bank', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects');
            $table->foreignId('grade_id')->nullable()->constrained('grades')->nullOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained('lessons')->nullOnDelete();

            $table->enum('type', [
                'multiple_choice',  // Trắc nghiệm 1 đáp án
                'multiple_select',  // Chọn nhiều đáp án
                'true_false',       // Đúng / Sai
                'fill_blank',       // Điền vào chỗ trống
                'short_answer',     // Trả lời ngắn
                'essay',            // Tự luận
                'reading',          // Đọc hiểu (có đoạn văn + câu hỏi con)
                'listening',        // Nghe (có audio + câu hỏi con)
                'speaking',         // Nói (ghi âm)
                'writing',          // Viết đoạn văn / bài luận
                'ordering',         // Sắp xếp thứ tự
                'matching',         // Nối cặp
                'drag_drop',        // Kéo thả phân loại
                'calculation',      // Tính toán (hỗ trợ sai số)
                'multi_step',       // Nhiều bước (bài toán lớn chia nhỏ)
                'data_analysis',    // Phân tích bảng số liệu
                'map_analysis',     // Đọc bản đồ
                'chart_analysis',   // Phân tích biểu đồ
                'experiment',       // Câu hỏi thí nghiệm
                'case_study',       // Tình huống thực tế
                'code_fill',        // Điền code vào chỗ trống
                'code_debug',       // Tìm và sửa lỗi code
                'code_output',      // Đọc output của đoạn code
                'code_runner',      // Viết code chạy test case
            ]);

            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->string('chapter_tag', 100)->nullable();

            // Nội dung câu hỏi (HTML, có thể chứa ảnh inline)
            $table->longText('content');

            // Media đính kèm độc lập (ảnh, audio, video cho câu hỏi)
            $table->enum('media_type', ['image', 'audio', 'video'])->nullable();
            $table->string('media_path', 500)->nullable();

            // Đáp án lựa chọn — cấu trúc linh hoạt theo type:
            // multiple_choice/select/true_false: [{id:'A', text:'...'}]
            // ordering: [{id:'1', text:'...'}]  (thứ tự bị xáo trộn khi thi)
            // matching: {left:[{id,text}], right:[{id,text}]}
            // drag_drop: [{id, text, correct_category}]
            // reading/listening/multi_step/case_study: null (dùng sub_questions)
            // code_fill: {template:'code...', blanks:[{id,position}]}
            // code_debug/output/runner: null
            $table->json('options')->nullable();

            // Đáp án đúng — cấu trúc linh hoạt:
            // multiple_choice/true_false: "A"
            // multiple_select/ordering/fill_blank: ["A","C"] / ["2","1","3"]
            // matching: {"A":"1","B":"2"}
            // drag_drop: {"A":"cat1","B":"cat2"}
            // calculation: {"value":3.14, "tolerance":0.01, "unit":"m"}
            // code_fill: ["answer1","answer2"]
            // code_output: "10"
            // essay/speaking/writing/short_answer: null (giáo viên chấm)
            $table->json('correct_answer')->nullable();

            $table->text('explanation')->nullable();

            // Câu hỏi con — dùng cho: reading, listening, multi_step,
            // data_analysis, map_analysis, chart_analysis, case_study
            // Mỗi item: {id, type, content, options, correct_answer, points}
            $table->json('sub_questions')->nullable();

            // Metadata bổ sung theo type:
            // calculation: {tolerance:0.01, unit:"m/s", accept_range:true}
            // fill_blank:  {case_sensitive:false, alternatives:[["x=2","x = 2"]]}
            // code_runner: {language:"javascript", test_cases:[{input:"5",expected:"120"}]}
            // code_debug:  {buggy_code:"...", language:"python"}
            // code_output: {code:"...", language:"javascript"}
            // speaking:    {prompt_audio_path:"...", time_limit_seconds:60}
            $table->json('metadata')->nullable();

            $table->decimal('default_points', 5, 2)->default(1.00);

            // Cho phép giáo viên khác trong hệ thống dùng câu hỏi này
            $table->boolean('is_public')->default(false);

            $table->index(['teacher_id', 'subject_id']);
            $table->index(['type', 'difficulty']);
            $table->index(['subject_id', 'grade_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_bank');
    }
};
