<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\QuestionBank;
use App\Models\Grade;
use Illuminate\Database\Seeder;

class ExamQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $exam = Exam::where('type', 'quiz_15')->first();
        if (!$exam) return;

        // Xóa các câu hỏi cũ trong đề thi để tránh trùng lặp
        ExamQuestion::where('exam_id', $exam->id)->delete();

        $questions = [
            [
                'type'           => 'multiple_choice',
                'difficulty'     => 'easy',
                'chapter_tag'    => 'Khảo sát hàm số',
                'content'        => '<p><strong>Câu 1.</strong> Cho hàm số $y=f(x)$ có đạo hàm trên $K$ ($K$ là một khoảng, đoạn hoặc nửa khoảng). Khẳng định nào sau đây đúng?</p>',
                'options'        => [
                    ['id' => 'A', 'text' => 'Nếu $f\'(x) \geq 0, \forall x \in K$ thì hàm số $f(x)$ đồng biến trên $K$.'],
                    ['id' => 'B', 'text' => 'Nếu $f\'(x) > 0, \forall x \in K$ thì hàm số $f(x)$ nghịch biến trên $K$.'],
                    ['id' => 'C', 'text' => 'Nếu $f\'(x) > 0, \forall x \in K$ thì hàm số $f(x)$ đồng biến trên $K$.'],
                    ['id' => 'D', 'text' => 'Nếu $f\'(x) \leq 0, \forall x \in K$ thì hàm số $f(x)$ nghịch biến trên $K$.'],
                ],
                'correct_answer' => 'C',
                'explanation'    => '<p>Nếu $f\'(x) > 0, \forall x \in K$ thì hàm số đồng biến trên $K$.</p><p><strong>Chú ý:</strong> Đáp án A không đúng vì nếu $f\'(x) = 0$ với mọi $x \in K$ thì hàm số là hàm hằng nên không đồng biến trên $K$.</p>',
                'points'         => 1,
            ],
            [
                'type'           => 'multiple_choice',
                'difficulty'     => 'medium',
                'chapter_tag'    => 'Khảo sát hàm số',
                'content'        => '<p><strong>Câu 2.</strong> Hàm số $y = -\frac{1}{3}x^3 + x + 1$ đồng biến trên khoảng nào?</p>',
                'options'        => [
                    ['id' => 'A', 'text' => '$( -1 ; +\infty )$'],
                    ['id' => 'B', 'text' => '$( -1 ; 1 )$'],
                    ['id' => 'C', 'text' => '$( -\infty ; 1 )$'],
                    ['id' => 'D', 'text' => '$( -\infty ; -1 )$ và $( 1 ; +\infty )$'],
                ],
                'correct_answer' => 'B',
                'explanation'    => '<p>Ta có: $y\' = -x^2 + 1$</p><p>$y\' = 0 \Leftrightarrow -x^2 + 1 = 0 \Leftrightarrow x = \pm 1$.</p><p>Ta có bảng biến thiên cho thấy:</p><ul><li>$y\' > 0$ trên khoảng $(-1; 1)$.</li><li>$y\' < 0$ trên $(-\infty; -1)$ và $(1; +\infty)$.</li></ul><p>Vậy hàm số đồng biến trên khoảng $(-1; 1)$.</p>',
                'points'         => 1,
            ],
            [
                'type'           => 'multiple_choice',
                'difficulty'     => 'medium',
                'chapter_tag'    => 'Khảo sát hàm số',
                'content'        => '<p><strong>Câu 3.</strong> Cho hàm số $y = -x^3 + 3x^2 - 3x + 1$, mệnh đề nào sau đây là đúng?</p>',
                'options'        => [
                    ['id' => 'A', 'text' => 'Hàm số luôn nghịch biến.'],
                    ['id' => 'B', 'text' => 'Hàm số luôn đồng biến.'],
                    ['id' => 'C', 'text' => 'Hàm số đạt cực đại tại $x = 1$.'],
                    ['id' => 'D', 'text' => 'Hàm số đạt cực tiểu tại $x = 1$.'],
                ],
                'correct_answer' => 'A',
                'explanation'    => '<p>Ta có: $y\' = -3x^2 + 6x - 3 = -3(x - 1)^2 \leq 0, \forall x \in \mathbb{R}$.</p><p>Vì đạo hàm luôn âm với mọi $x \neq 1$ và chỉ bằng 0 tại $x = 1$, theo định lý mở rộng về tính đơn điệu, hàm số luôn nghịch biến trên $\mathbb{R}$.</p>',
                'points'         => 1,
            ],
            [
                'type'           => 'multiple_choice',
                'difficulty'     => 'medium',
                'chapter_tag'    => 'Cực trị & Giá trị lớn nhất nhỏ nhất',
                'content'        => '<p><strong>Câu 4.</strong> Giá trị nhỏ nhất của hàm số $y = x^3 - 3x^2$ trên đoạn $[-1; 1]$ là:</p>',
                'options'        => [
                    ['id' => 'A', 'text' => '$-2$'],
                    ['id' => 'B', 'text' => '$0$'],
                    ['id' => 'C', 'text' => '$-5$'],
                    ['id' => 'D', 'text' => '$-4$'],
                ],
                'correct_answer' => 'D',
                'explanation'    => '<p>Ta có: $y\' = 3x^2 - 6x$</p><p>$y\' = 0 \Leftrightarrow 3x^2 - 6x = 0 \Leftrightarrow x = 0$ (thuộc $[-1; 1]$) hoặc $x = 2$ (không thuộc $[-1; 1]$).</p><p>Tính giá trị của hàm số tại các điểm biên và điểm cực trị:</p><ul><li>$y(0) = 0$</li><li>$y(-1) = (-1)^3 - 3(-1)^2 = -1 - 3 = -4$</li><li>$y(1) = 1^3 - 3(1)^2 = -2$</li></ul><p>So sánh các giá trị trên, ta được giá trị nhỏ nhất của hàm số trên đoạn $[-1; 1]$ là $-4$ (đạt được tại $x = -1$).</p>',
                'points'         => 1,
            ],
            [
                'type'           => 'multiple_choice',
                'difficulty'     => 'medium',
                'chapter_tag'    => 'Khảo sát hàm số',
                'content'        => '<p><strong>Câu 5.</strong> Hàm số $y = \frac{-2x + 1}{x - 1}$ đồng biến trên khoảng nào dưới đây:</p>',
                'options'        => [
                    ['id' => 'A', 'text' => '$( -5 ; 1 )$ hoặc $( -\infty ; 1 )$'],
                    ['id' => 'B', 'text' => '$\mathbb{R} \setminus \{1\}$'],
                    ['id' => 'C', 'text' => '$( 0 ; +\infty )$'],
                    ['id' => 'D', 'text' => '$\mathbb{R}$'],
                ],
                'correct_answer' => 'A',
                'explanation'    => '<p>Tập xác định: $D = \mathbb{R} \setminus \{1\}$.</p><p>Ta có đạo hàm: $y\' = \frac{-2(x-1) - (-2x+1)(1)}{(x - 1)^2} = \frac{-2x + 2 + 2x - 1}{(x - 1)^2} = \frac{1}{(x - 1)^2} > 0, \forall x \in D$.</p><p>Vì đạo hàm $y\'$ luôn dương trên từng khoảng xác định nên hàm số đồng biến trên các khoảng $(-\infty; 1)$ và $(1; +\infty)$. Trong các phương án đề bài đưa ra, hàm số đồng biến trên $(-\infty; 1)$.</p>',
                'points'         => 1,
            ],
            [
                'type'           => 'multiple_choice',
                'difficulty'     => 'easy',
                'chapter_tag'    => 'Tiệm cận đồ thị hàm số',
                'content'        => '<p><strong>Câu 6.</strong> Tâm đối xứng của đồ thị hàm số $y = \frac{3x + 1}{x + 1}$ là:</p>',
                'options'        => [
                    ['id' => 'A', 'text' => '$( 3 ; -1 )$'],
                    ['id' => 'B', 'text' => '$( -1 ; 3 )$'],
                    ['id' => 'C', 'text' => '$( 3 ; 1 )$'],
                    ['id' => 'D', 'text' => '$( 1 ; 3 )$'],
                ],
                'correct_answer' => 'B',
                'explanation'    => '<p>Đồ thị hàm số bậc nhất trên bậc nhất $y = \frac{ax + b}{cx + d}$ có tiệm cận đứng là $x = -d/c = -1$ và tiệm cận ngang là $y = a/c = 3$.</p><p>Tâm đối xứng của đồ thị là giao điểm của đường tiệm cận đứng và tiệm cận ngang, tức là điểm $I(-1; 3)$.</p>',
                'points'         => 1,
            ],
            [
                'type'           => 'multiple_choice',
                'difficulty'     => 'medium',
                'chapter_tag'    => 'Cực trị & Giá trị lớn nhất nhỏ nhất',
                'content'        => '<p><strong>Câu 7.</strong> Số điểm cực trị của đồ thị hàm số $y = x^4 - x^3$ là:</p>',
                'options'        => [
                    ['id' => 'A', 'text' => '$1$'],
                    ['id' => 'B', 'text' => '$0$'],
                    ['id' => 'C', 'text' => '$3$'],
                    ['id' => 'D', 'text' => '$2$'],
                ],
                'correct_answer' => 'A',
                'explanation'    => '<p>Ta có: $y\' = 4x^3 - 3x^2$</p><p>$y\' = 0 \Leftrightarrow x^2(4x - 3) = 0 \Leftrightarrow x = 0$ (nghiệm bội chẵn) hoặc $x = \frac{3}{4}$ (nghiệm bội lẻ).</p><p>Do đạo hàm $y\'$ chỉ đổi dấu khi qua nghiệm bội lẻ $x = \frac{3}{4}$ (và không đổi dấu khi đi qua $x = 0$), nên đồ thị hàm số chỉ có duy nhất 1 điểm cực trị.</p>',
                'points'         => 1,
            ],
            [
                'type'           => 'multiple_choice',
                'difficulty'     => 'medium',
                'chapter_tag'    => 'Cực trị & Giá trị lớn nhất nhỏ nhất',
                'content'        => '<p><strong>Câu 8.</strong> Giá trị lớn nhất của hàm số $y = x^3 - 6x^2 + 12x + 5$ trên đoạn $[0; 3]$ là:</p>',
                'options'        => [
                    ['id' => 'A', 'text' => '$14$'],
                    ['id' => 'B', 'text' => '$13$'],
                    ['id' => 'C', 'text' => '$5$'],
                    ['id' => 'D', 'text' => '$10$'],
                ],
                'correct_answer' => 'A',
                'explanation'    => '<p>Ta có: $y\' = 3x^2 - 12x + 12 = 3(x - 2)^2 \geq 0, \forall x$.</p><p>$y\' = 0 \Leftrightarrow x = 2 \in [0; 3]$.</p><p>Tính giá trị của hàm số tại hai biên và điểm cực trị:</p><ul><li>$y(0) = 5$</li><li>$y(2) = 2^3 - 6(2^2) + 12(2) + 5 = 8 - 24 + 24 + 5 = 13$</li><li>$y(3) = 3^3 - 6(3^2) + 12(3) + 5 = 27 - 54 + 36 + 5 = 14$</li></ul><p>So sánh các giá trị trên, ta được giá trị lớn nhất của hàm số trên đoạn $[0; 3]$ là $14$ (đạt được tại $x = 3$).</p>',
                'points'         => 1,
            ],
            [
                'type'           => 'multiple_choice',
                'difficulty'     => 'hard',
                'chapter_tag'    => 'Tiếp tuyến đồ thị hàm số',
                'content'        => '<p><strong>Câu 9.</strong> Có bao nhiêu tiếp tuyến với đồ thị hàm số $y = \frac{2x + 3}{x - 1}$, biết tiếp tuyến song song với đường thẳng $y = -5x - 3$?</p>',
                'options'        => [
                    ['id' => 'A', 'text' => '$1$'],
                    ['id' => 'B', 'text' => '$0$'],
                    ['id' => 'C', 'text' => '$2$'],
                    ['id' => 'D', 'text' => '$3$'],
                ],
                'correct_answer' => 'A',
                'explanation'    => '<p>Tiếp tuyến song song với đường thẳng $y = -5x - 3$ nên có hệ số góc tiếp tuyến là $k = -5$.</p><p>Ta có đạo hàm: $y\' = \frac{-2 - 3}{(x - 1)^2} = \frac{-5}{(x - 1)^2}$.</p><p>Giải phương trình hoành độ tiếp điểm: $y\'(x_0) = -5 \Leftrightarrow \frac{-5}{(x_0 - 1)^2} = -5 \Leftrightarrow (x_0 - 1)^2 = 1 \Leftrightarrow x_0 = 2$ hoặc $x_0 = 0$.</p><ul><li>Với $x_0 = 2 \Rightarrow y_0 = \frac{2(2) + 3}{2 - 1} = 7$. Phương trình tiếp tuyến là: $y = -5(x - 2) + 7 \Leftrightarrow y = -5x + 17$ (thỏa mãn).</li><li>Với $x_0 = 0 \Rightarrow y_0 = \frac{2(0) + 3}{0 - 1} = -3$. Phương trình tiếp tuyến là: $y = -5(x - 0) - 3 \Leftrightarrow y = -5x - 3$ (loại vì trùng với đường thẳng $y = -5x - 3$ ban đầu).</li></ul><p>Như vậy, chỉ có đúng 1 tiếp tuyến thỏa mãn yêu cầu đề bài.</p>',
                'points'         => 1,
            ],
            [
                'type'           => 'multiple_choice',
                'difficulty'     => 'medium',
                'chapter_tag'    => 'Cực trị & Giá trị lớn nhất nhỏ nhất',
                'content'        => '<p><strong>Câu 10.</strong> Giá trị cực tiểu của hàm số $y = x^3 - 3x^2 - 9x + 2$ là:</p>',
                'options'        => [
                    ['id' => 'A', 'text' => '$-20$'],
                    ['id' => 'B', 'text' => '$7$'],
                    ['id' => 'C', 'text' => '$-25$'],
                    ['id' => 'D', 'text' => '$3$'],
                ],
                'correct_answer' => 'C',
                'explanation'    => '<p>Ta có: $y\' = 3x^2 - 6x - 9$</p><p>$y\' = 0 \Leftrightarrow 3(x^2 - 2x - 3) = 0 \Leftrightarrow x = 3$ hoặc $x = -1$.</p><p>Bảng xét dấu đạo hàm cho thấy $y\'$ đổi dấu từ âm sang dương khi đi qua $x = 3$. Do đó, cực tiểu đạt được tại điểm cực tiểu $x = 3$.</p><p>Giá trị cực tiểu của hàm số tương ứng là: $y(3) = 3^3 - 3(3^2) - 9(3) + 2 = 27 - 27 - 27 + 2 = -25$.</p>',
                'points'         => 1,
            ],
        ];

        $gradeId = Grade::where('level', 12)->first()?->id ?? 12;

        foreach ($questions as $index => $data) {
            // 1. Tạo hoặc cập nhật câu hỏi trong Ngân hàng câu hỏi (QuestionBank)
            $bankQuestion = QuestionBank::updateOrCreate(
                [
                    'teacher_id' => $exam->teacher_id,
                    'subject_id' => $exam->subject_id,
                    'content'    => $data['content'],
                ],
                [
                    'grade_id'       => $gradeId,
                    'type'           => $data['type'],
                    'difficulty'     => $data['difficulty'],
                    'chapter_tag'    => $data['chapter_tag'],
                    'options'        => $data['options'],
                    'correct_answer' => [$data['correct_answer']], // correct_answer lưu dạng array trên QuestionBank
                    'explanation'    => $data['explanation'],
                    'default_points' => $data['points'],
                    'is_public'      => true,
                ]
            );

            // 2. Tạo câu hỏi liên kết trực tiếp vào đề kiểm tra 15 phút (ExamQuestion)
            ExamQuestion::create([
                'exam_id'          => $exam->id,
                'question_bank_id' => $bankQuestion->id,
                'order_index'      => $index + 1,
                'type'             => $data['type'],
                'difficulty'       => $data['difficulty'],
                'chapter_tag'      => $data['chapter_tag'],
                'content'          => $data['content'],
                'options'          => $data['options'],
                'correct_answer'   => $data['correct_answer'],
                'explanation'      => $data['explanation'],
                'points'           => $data['points'],
            ]);
        }
    }
}
