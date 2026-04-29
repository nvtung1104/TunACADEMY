<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\ExamQuestion;
use Illuminate\Database\Seeder;

class ExamQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $exam = Exam::where('type', 'quiz_15')->first();
        if (!$exam) return;

        $questions = [
            // 1. Trắc nghiệm chọn 1 đáp án (multiple_choice)
            [
                'type'           => 'multiple_choice',
                'difficulty'     => 'easy',
                'chapter_tag'    => 'Đại số',
                'content'        => '<p>Hàm số bậc nhất có dạng nào sau đây?</p>',
                'options'        => [
                    ['id' => 'A', 'text' => 'y = ax + b (a ≠ 0)'],
                    ['id' => 'B', 'text' => 'y = ax² + bx + c'],
                    ['id' => 'C', 'text' => 'y = a/x'],
                    ['id' => 'D', 'text' => 'y = √x'],
                ],
                'correct_answer' => 'A',
                'explanation'    => 'Hàm số bậc nhất có dạng y = ax + b, với a ≠ 0.',
                'points'         => 1,
            ],

            // 2. Chọn nhiều đáp án (multiple_select)
            [
                'type'           => 'multiple_select',
                'difficulty'     => 'medium',
                'chapter_tag'    => 'Đại số',
                'content'        => '<p>Phương trình nào sau đây có nghiệm x = 2?</p>',
                'options'        => [
                    ['id' => 'A', 'text' => '2x - 4 = 0'],
                    ['id' => 'B', 'text' => 'x + 1 = 3'],
                    ['id' => 'C', 'text' => 'x² = 4'],
                    ['id' => 'D', 'text' => '3x = 5'],
                ],
                'correct_answer' => ['A', 'B', 'C'],
                'explanation'    => 'A: x=2✓, B: x=2✓, C: x=±2 (có nghiệm x=2)✓, D: x=5/3✗',
                'points'         => 2,
            ],

            // 3. Đúng / Sai (true_false)
            [
                'type'           => 'true_false',
                'difficulty'     => 'easy',
                'chapter_tag'    => 'Đại số',
                'content'        => '<p>Hàm số y = 2x + 1 đồng biến trên ℝ.</p>',
                'options'        => [
                    ['id' => 'T', 'text' => 'Đúng'],
                    ['id' => 'F', 'text' => 'Sai'],
                ],
                'correct_answer' => 'T',
                'explanation'    => 'Vì hệ số a = 2 > 0 nên hàm đồng biến trên ℝ.',
                'points'         => 0.5,
            ],

            // 4. Điền vào chỗ trống (fill_blank)
            [
                'type'           => 'fill_blank',
                'difficulty'     => 'medium',
                'chapter_tag'    => 'Đại số',
                'content'        => '<p>Giải phương trình: 3x − 6 = 0 → x = ___</p>',
                'options'        => null,
                'correct_answer' => ['2'],
                'metadata'       => ['case_sensitive' => false, 'alternatives' => [['2', '2.0']]],
                'explanation'    => '3x = 6 → x = 2.',
                'points'         => 1,
            ],

            // 5. Tính toán với sai số (calculation)
            [
                'type'           => 'calculation',
                'difficulty'     => 'medium',
                'chapter_tag'    => 'Hình học',
                'content'        => '<p>Tính diện tích hình tròn bán kính r = 5 cm (lấy π ≈ 3.14). Kết quả tính bằng cm².</p>',
                'options'        => null,
                'correct_answer' => ['value' => 78.5, 'tolerance' => 0.5, 'unit' => 'cm²'],
                'metadata'       => ['tolerance' => 0.5, 'unit' => 'cm²', 'accept_range' => true],
                'explanation'    => 'S = π.r² = 3.14 × 25 = 78.5 cm²',
                'points'         => 1.5,
            ],

            // 6. Sắp xếp thứ tự (ordering)
            [
                'type'           => 'ordering',
                'difficulty'     => 'medium',
                'chapter_tag'    => 'Đại số',
                'content'        => '<p>Sắp xếp các bước giải phương trình bậc nhất ax + b = 0 theo đúng thứ tự:</p>',
                'options'        => [
                    ['id' => '1', 'text' => 'ax = −b'],
                    ['id' => '2', 'text' => 'Kiểm tra a ≠ 0'],
                    ['id' => '3', 'text' => 'x = −b/a'],
                    ['id' => '4', 'text' => 'ax + b = 0'],
                ],
                'correct_answer' => ['4', '2', '1', '3'],
                'explanation'    => 'Bước 1: viết pt → Bước 2: kiểm tra a ≠ 0 → Bước 3: ax = -b → Bước 4: x = -b/a.',
                'points'         => 1,
            ],

            // 7. Nối cặp (matching)
            [
                'type'           => 'matching',
                'difficulty'     => 'medium',
                'chapter_tag'    => 'Hình học',
                'content'        => '<p>Nối công thức với tên gọi tương ứng:</p>',
                'options'        => [
                    'left'  => [
                        ['id' => 'A', 'text' => 'S = πr²'],
                        ['id' => 'B', 'text' => 'S = a²'],
                        ['id' => 'C', 'text' => 'P = 2(a + b)'],
                        ['id' => 'D', 'text' => 'S = (a × h) / 2'],
                    ],
                    'right' => [
                        ['id' => '1', 'text' => 'Diện tích hình tròn'],
                        ['id' => '2', 'text' => 'Diện tích hình vuông'],
                        ['id' => '3', 'text' => 'Chu vi hình chữ nhật'],
                        ['id' => '4', 'text' => 'Diện tích tam giác'],
                    ],
                ],
                'correct_answer' => ['A' => '1', 'B' => '2', 'C' => '3', 'D' => '4'],
                'points'         => 2,
            ],

            // 8. Nhiều bước (multi_step)
            [
                'type'           => 'multi_step',
                'difficulty'     => 'hard',
                'chapter_tag'    => 'Đại số',
                'content'        => '<p>Một người đi từ A đến B. Nửa đường đầu đi với vận tốc 40 km/h, nửa đường sau đi với vận tốc 60 km/h. Tổng quãng đường AB = 120 km.</p>',
                'options'        => null,
                'correct_answer' => null,
                'sub_questions'  => [
                    [
                        'id'             => 1,
                        'type'           => 'calculation',
                        'content'        => 'Tính thời gian đi nửa đường đầu (60 km với v = 40 km/h)?',
                        'correct_answer' => ['value' => 1.5, 'tolerance' => 0.01, 'unit' => 'giờ'],
                        'points'         => 1,
                    ],
                    [
                        'id'             => 2,
                        'type'           => 'calculation',
                        'content'        => 'Tính thời gian đi nửa đường sau (60 km với v = 60 km/h)?',
                        'correct_answer' => ['value' => 1.0, 'tolerance' => 0.01, 'unit' => 'giờ'],
                        'points'         => 1,
                    ],
                    [
                        'id'             => 3,
                        'type'           => 'calculation',
                        'content'        => 'Tính vận tốc trung bình trên cả đoạn đường AB?',
                        'correct_answer' => ['value' => 48, 'tolerance' => 0.1, 'unit' => 'km/h'],
                        'points'         => 2,
                        'explanation'    => 'v_tb = 120 / (1.5 + 1) = 120/2.5 = 48 km/h',
                    ],
                ],
                'points'         => 4,
            ],

            // 9. Tự luận (essay)
            [
                'type'           => 'essay',
                'difficulty'     => 'hard',
                'chapter_tag'    => 'Hình học',
                'content'        => '<p>Chứng minh rằng trong tam giác ABC vuông tại A, ta có: BC² = AB² + AC².</p>',
                'options'        => null,
                'correct_answer' => null,
                'explanation'    => 'Định lý Pythagore — chứng minh bằng diện tích hoặc hình chiếu.',
                'points'         => 5,
            ],
        ];

        foreach ($questions as $index => $data) {
            ExamQuestion::updateOrCreate(
                ['exam_id' => $exam->id, 'order_index' => $index + 1],
                [
                    ...$data,
                    'order_index' => $index + 1,
                    'exam_id'     => $exam->id,
                ]
            );
        }
    }
}
