<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class AiSubmissionEvaluationService
{
    private string $model;
    private int $maxTokens;
    private int $timeout;

    public function __construct()
    {
        $this->model     = config('services.openai.model', 'gpt-4o-mini');
        $this->maxTokens = config('services.openai.max_tokens', 1200);
        $this->timeout   = config('services.openai.timeout', 40);
    }

    public function evaluateExam(array $payload): ?array
    {
        $systemPrompt = 'Bạn là trợ lý chấm bài cho hệ thống học trực tuyến. '
            . 'Bạn phải trả về DUY NHẤT JSON hợp lệ, không thêm markdown. '
            . 'JSON phải có các key: score, wrong_answers, mistake_explanations, competency_comment, next_study_method.';

        $userPrompt = [
            'task' => 'Chấm bài thi và phân tích lỗi sai',
            'language' => 'vi',
            'scoring_scale' => '0-10',
            'input' => $payload,
            'rules' => [
                'score là số thực từ 0 đến 10',
                'wrong_answers là mảng, mỗi phần tử gồm question_id và reason',
                'mistake_explanations là mảng giải thích ngắn gọn cho từng lỗi chính',
                'competency_comment là nhận xét năng lực tổng quan',
                'next_study_method là hướng học tiếp theo theo từng bước',
            ],
        ];

        $res = $this->callAndParse($systemPrompt, $userPrompt);
        if (!$res) {
            return $this->generateMockEvaluation($payload);
        }
        return $res;
    }

    public function evaluateAssignment(array $payload): ?array
    {
        $systemPrompt = 'Bạn là trợ lý chấm bài tập cho học sinh phổ thông. '
            . 'Bạn phải trả về DUY NHẤT JSON hợp lệ, không thêm markdown. '
            . 'JSON phải có các key: score, wrong_answers, mistake_explanations, competency_comment, next_study_method.';

        $userPrompt = [
            'task' => 'Chấm bài tập và đưa ra phản hồi học tập cá nhân hoá',
            'language' => 'vi',
            'scoring_scale' => '0-10',
            'input' => $payload,
            'rules' => [
                'Nếu thiếu dữ liệu chấm chi tiết, vẫn chấm ở mức tham chiếu dựa trên nội dung bài làm',
                'wrong_answers có thể dùng question_id hoặc "overall" nếu bài dạng tự luận tổng thể',
                'mistake_explanations tập trung vào lỗi phổ biến của học sinh',
                'competency_comment ngắn gọn, tích cực, thực tế',
                'next_study_method có các bước hành động cụ thể',
            ],
        ];

        $res = $this->callAndParse($systemPrompt, $userPrompt);
        if (!$res) {
            return $this->generateMockEvaluation($payload);
        }
        return $res;
    }

    public function reviewSubmission(array $payload): ?array
    {
        $systemPrompt =
            'Bạn là gia sư AI thông minh cho học sinh K-12 Việt Nam. '
            . 'Nhiệm vụ: đọc bài làm của học sinh, giải thích đáp án từng câu và đề xuất kế hoạch học tập. '
            . 'Trả về DUY NHẤT JSON hợp lệ, không markdown. '
            . 'JSON có đúng 3 key: question_reviews (array), competency_comment (string), study_plan (array of strings).';

        $userPrompt = [
            'task'     => 'Nhận xét bài làm, giải thích đáp án và đưa ra kế hoạch học tập cá nhân hóa',
            'language' => 'vi',
            'data'     => $payload,
            'output_rules' => [
                'question_reviews: mảng, mỗi phần tử gồm: '
                    . 'question_id (string), '
                    . 'is_correct (boolean), '
                    . 'student_answer_text (diễn đạt câu trả lời học sinh thành câu hoàn chỉnh, "Chưa trả lời" nếu bỏ trống), '
                    . 'correct_answer_text (đáp án đúng + lý giải ngắn gọn TẠI SAO đúng), '
                    . 'question_analysis (Nhận xét phân tích câu hỏi ngay dưới câu hỏi, lý thuyết/công thức áp dụng), '
                    . 'options_analysis (Nhận xét từng câu trả lời như công thức hay là sai chỗ nào, không hợp lý chỗ nào cho tất cả phương án), '
                    . 'image_analysis (Nếu câu hỏi có hình ảnh/đồ thị, hãy phân tích kỹ hình ảnh đó. Nếu không có hình ảnh, ghi "Không có hình ảnh"), '
                    . 'error_analysis (Nếu chọn đáp án sai (is_correct là false), đưa ra nhận xét tại sao học sinh lại chọn đáp án sai đó (lỗi tư duy, nhầm lẫn công thức). Nếu đúng, ghi "Học sinh chọn đáp án đúng"). '
                    . 'Bao gồm TẤT CẢ câu hỏi.',
                'competency_comment: 2-3 câu nhận xét tổng quan, chỉ rõ điểm mạnh và điểm cần cải thiện, giọng khích lệ.',
                'study_plan: mảng 3-5 hành động học tập cụ thể, sắp xếp theo mức độ ưu tiên. Mỗi bước là 1 câu bắt đầu bằng động từ.',
            ],
        ];

        $apiKey = config('services.gemini.api_key') ?? env('GEMINI_API_KEY');
        if (!$apiKey) {
            return $this->generateMockReview($payload);
        }

        try {
            $client = new Client(['timeout' => 60, 'connect_timeout' => 10]);

            $parts = [];
            $parts[] = ['text' => json_encode($userPrompt, JSON_UNESCAPED_UNICODE)];

            // Attach image files if any question contains an image
            foreach ($payload['questions'] ?? [] as $q) {
                if (($q['media_type'] ?? '') === 'image' && !empty($q['media_path'])) {
                    $fullPath = storage_path('app/public/' . $q['media_path']);
                    if (file_exists($fullPath)) {
                        $mimeType = mime_content_type($fullPath) ?: 'image/jpeg';
                        $base64 = base64_encode(file_get_contents($fullPath));
                        $parts[] = [
                            'text' => "Hình ảnh cho câu hỏi ID " . $q['id'] . ":"
                        ];
                        $parts[] = [
                            'inlineData' => [
                                'mimeType' => $mimeType,
                                'data' => $base64
                            ]
                        ];
                    }
                }
            }

            $response = $client->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => $parts
                        ]
                    ],
                    'systemInstruction' => [
                        'parts' => [
                            ['text' => $systemPrompt]
                        ]
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                    ]
                ]
            ]);

            $raw  = json_decode($response->getBody()->getContents(), true);
            $text = $raw['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            if ($text === '') {
                return $this->generateMockReview($payload);
            }

            $data = $this->extractJson($text);
            if (!$data) {
                return $this->generateMockReview($payload);
            }

            return [
                'question_reviews'   => is_array($data['question_reviews'] ?? null) ? $data['question_reviews'] : [],
                'competency_comment' => (string) ($data['competency_comment'] ?? ''),
                'study_plan'         => is_array($data['study_plan'] ?? null) ? $data['study_plan'] : [],
            ];
        } catch (\Throwable $e) {
            Log::error('Gemini evaluation failed', [
                'error'   => $e->getMessage(),
                'service' => static::class,
            ]);
            return $this->generateMockReview($payload);
        }
    }

    private function callAndParse(string $systemPrompt, array $userPrompt): ?array
    {
        $apiKey = config('services.gemini.api_key') ?? env('GEMINI_API_KEY');
        if (!$apiKey) {
            return null;
        }

        try {
            $client = new Client(['timeout' => $this->timeout, 'connect_timeout' => 10]);
            
            $response = $client->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => json_encode($userPrompt, JSON_UNESCAPED_UNICODE)]
                            ]
                        ]
                    ],
                    'systemInstruction' => [
                        'parts' => [
                            ['text' => $systemPrompt]
                        ]
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                    ]
                ]
            ]);

            $raw = json_decode($response->getBody()->getContents(), true);
            $text = $raw['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            if ($text === '') {
                return null;
            }

            $data = $this->extractJson($text);
            if (!$data) {
                return null;
            }

            return [
                'score' => $this->normalizeScore($data['score'] ?? null),
                'wrong_answers' => is_array($data['wrong_answers'] ?? null) ? $data['wrong_answers'] : [],
                'mistake_explanations' => is_array($data['mistake_explanations'] ?? null) ? $data['mistake_explanations'] : [],
                'competency_comment' => (string) ($data['competency_comment'] ?? ''),
                'next_study_method' => (string) ($data['next_study_method'] ?? ''),
            ];
        } catch (\Throwable $e) {
            Log::error('Gemini callAndParse failed', [
                'error'   => $e->getMessage(),
                'service' => static::class,
            ]);
            return null;
        }
    }

    public function generateMockReview(array $payload): array
    {
        $questions = $payload['questions'] ?? [];
        $subject = $payload['subject'] ?? 'môn học';
        $title = $payload['title'] ?? 'bài thi';

        $total = count($questions);
        $correctCount = 0;
        foreach ($questions as $q) {
            if ($q['is_correct']) {
                $correctCount++;
            }
        }

        $score = $total > 0 ? round(($correctCount / $total) * 10, 1) : 10;

        $questionReviews = [];
        foreach ($questions as $index => $q) {
            $isCorrect = (bool) ($q['is_correct'] ?? false);

            $studentAns = $q['student_answer'] ?? null;
            $studentText = 'Chưa trả lời';
            if ($studentAns !== null && $studentAns !== '') {
                if (is_array($studentAns)) {
                    $studentText = 'Lựa chọn ' . implode(', ', array_map(fn($o) => is_numeric($o) ? chr(65 + (int)$o) : $o, $studentAns));
                } else if (is_numeric($studentAns)) {
                    $studentText = 'Phương án ' . chr(65 + (int)$studentAns);
                } else {
                    $studentText = (string) $studentAns;
                }
            }

            $correctAns = $q['correct_answer'] ?? '';
            $correctText = '';
            if (is_array($correctAns)) {
                $correctText = 'Phương án đúng là: ' . implode(', ', array_map(fn($o) => is_numeric($o) ? chr(65 + (int)$o) : $o, $correctAns));
            } else if (is_numeric($correctAns)) {
                $correctText = 'Đáp án đúng là phương án ' . chr(65 + (int)$correctAns);
            } else {
                $correctText = (string) $correctAns;
            }

            $questionReviews[] = [
                'question_id' => (string) $q['id'],
                'is_correct' => $isCorrect,
                'student_answer_text' => $studentText,
                'correct_answer_text' => $correctText . '. Cần ôn tập kỹ các lý thuyết nền tảng và vận dụng thuần thục công thức liên quan để đạt kết quả tốt nhất.',
                'question_analysis' => 'Phân tích câu hỏi kiểm tra kiến thức về chuyên đề ' . $subject . ' liên quan đến câu này.',
                'options_analysis' => 'Nhận xét các phương án: phương án chính xác phản ánh công thức chuẩn, các phương án còn lại là các phương án gây nhiễu dễ nhầm lẫn.',
                'image_analysis' => (($q['media_type'] ?? '') === 'image') ? 'Phân tích hình ảnh: Hình ảnh cung cấp thông tin trực quan hỗ trợ tìm lời giải.' : 'Không có hình ảnh.',
                'error_analysis' => !$isCorrect ? 'Lỗi sai phổ biến là do áp dụng nhầm công thức biến đổi hoặc đọc lướt nhanh dữ kiện đề bài dẫn đến chọn đáp án sai.' : 'Học sinh chọn đáp án đúng',
            ];
        }

        if ($score >= 8.0) {
            $competencyComment = "Bài làm vô cùng xuất sắc! Học sinh đã thể hiện sự hiểu biết sâu sắc và toàn diện đối với các kiến thức trọng tâm của môn {$subject}. Khả năng tư duy logic và kỹ năng giải quyết bài toán nhanh nhẹn. Hãy tiếp tục phát huy tinh thần và phong độ này nhé!";
            $studyPlan = [
                "Luyện tập thêm các bài toán nâng cao và câu hỏi vận dụng cao của chuyên đề {$subject} để đạt điểm số tuyệt đối.",
                "Thử sức giải các bộ đề thi học sinh giỏi hoặc đề thi thử đại học chính thức để tăng tốc phản xạ giải đề.",
                "Hỗ trợ giải đáp các câu hỏi khó cho bạn học xung quanh để củng cố thêm kiến thức của chính mình.",
            ];
        } else if ($score >= 5.0) {
            $competencyComment = "Kết quả làm bài khá tốt! Bạn đã nắm được những kiến thức nền cơ bản môn {$subject}, tuy nhiên vẫn còn một số câu bị nhầm lẫn đáng tiếc hoặc chưa tối ưu hóa thời gian tính toán. Bạn hoàn toàn có tiềm năng đạt kết quả cao hơn nữa.";
            $studyPlan = [
                "Xem lại chi tiết giải thích đáp án của các câu đã làm sai để rút ra bài học kinh nghiệm.",
                "Tập trung ôn tập lại các lý thuyết còn mông lung và làm thêm các câu hỏi vận dụng mức độ trung bình để nhuần nhuyễn hơn.",
                "Luyện tập cách phân bổ thời gian hợp lý cho từng phần thi, tránh sa đà quá lâu vào một câu hỏi khó.",
            ];
        } else {
            $competencyComment = "Nỗ lực làm bài đáng ghi nhận, tuy nhiên điểm số hiện tại cho thấy bạn còn hổng nhiều kiến thức cơ bản của môn {$subject}. Đừng lo lắng, hãy xem đây là cơ hội rà soát lại kiến thức để bứt phá mạnh mẽ hơn.";
            $studyPlan = [
                "Đọc lại lý thuyết cơ bản trong sách giáo khoa môn {$subject} và các bài giảng tóm tắt kiến thức của giáo viên.",
                "Bắt đầu luyện tập lại các câu hỏi nhận biết và thông hiểu cơ bản trước để củng cố chắc chắn phần gốc.",
                "Chủ động đặt câu hỏi cho bạn học giỏi hoặc thầy cô để tháo gỡ nhanh các chỗ chưa hiểu.",
            ];
        }

        return [
            'question_reviews'   => $questionReviews,
            'competency_comment' => $competencyComment,
            'study_plan'         => $studyPlan,
        ];
    }

    public function generateMockEvaluation(array $payload): array
    {
        $grading = $payload['grading_context'] ?? [];
        $baseScore = $grading['base_score'] ?? 0;
        $subject = $payload['exam']['subject'] ?? ($payload['assignment']['subject'] ?? 'môn học');

        $score = $baseScore;
        $answers = $payload['answers'] ?? [];
        $wrongAnswers = [];
        $mistakeExplanations = [];

        foreach ($answers as $idx => $ans) {
            if (!($ans['is_correct'] ?? false)) {
                $wrongAnswers[] = [
                    'question_id' => $ans['question_id'] ?? ($idx + 1),
                    'reason' => 'Tính toán nhầm hoặc chọn chưa đúng phương án.',
                ];
            }
        }

        if (count($wrongAnswers) > 0) {
            $mistakeExplanations[] = "Còn nhầm lẫn ở một số câu trắc nghiệm lý thuyết và bài tập áp dụng công thức.";
            $mistakeExplanations[] = "Cần rèn luyện thêm kỹ năng đọc kỹ đề bài để tránh bẫy câu hỏi nhiễu.";
        }

        if ($score >= 8.0) {
            $competencyComment = "Kết quả xuất sắc! Bạn nắm bài rất chắc chắn, giải quyết nhanh các bài toán thuộc môn {$subject}.";
            $nextStudyMethod = "1. Tiếp tục duy trì luyện tập các đề thi vận dụng cao.\n2. Rèn luyện tốc độ phản xạ và tối ưu hóa quy trình tính toán.";
        } else if ($score >= 5.0) {
            $competencyComment = "Đạt yêu cầu khá tốt. Khá vững lý thuyết môn {$subject} nhưng còn làm sai các bài tập đòi hỏi tính toán chi tiết.";
            $nextStudyMethod = "1. Làm lại toàn bộ câu hỏi bị sai để hiểu bản chất.\n2. Tăng cường thực hành các bài tập trung bình để thuần thục hơn.";
        } else {
            $competencyComment = "Kiến thức nền tảng môn {$subject} còn chưa chắc chắn. Cần ôn tập lại gấp phần lý thuyết trọng tâm.";
            $nextStudyMethod = "1. Học lại lý thuyết cơ bản và làm các câu hỏi nhận biết đơn giản.\n2. Đặt mục tiêu nhỏ để lấy lại gốc từ từ.";
        }

        return [
            'score'                => (float) $score,
            'wrong_answers'        => $wrongAnswers,
            'mistake_explanations' => $mistakeExplanations,
            'competency_comment'   => $competencyComment,
            'next_study_method'    => $nextStudyMethod,
        ];
    }

    private function extractJson(string $text): ?array
    {
        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{.*\}/s', $text, $matches) === 1) {
            $decoded = json_decode($matches[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    private function normalizeScore(mixed $score): ?float
    {
        if (!is_numeric($score)) {
            return null;
        }

        return max(0, min(10, round((float) $score, 2)));
    }
}
