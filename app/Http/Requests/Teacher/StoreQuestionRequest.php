<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
{
    // All 24 supported types
    private const TYPES = [
        'multiple_choice', 'multiple_select', 'true_false',
        'fill_blank', 'short_answer', 'essay',
        'reading', 'listening', 'speaking', 'writing',
        'ordering', 'matching', 'drag_drop',
        'calculation', 'multi_step',
        'data_analysis', 'map_analysis', 'chart_analysis',
        'experiment', 'case_study',
        'code_fill', 'code_debug', 'code_output', 'code_runner',
    ];

    // Types that REQUIRE options
    private const NEEDS_OPTIONS = [
        'multiple_choice', 'multiple_select', 'true_false',
        'ordering', 'matching', 'drag_drop',
    ];

    // Types where correct_answer is null (teacher grades manually)
    private const TEACHER_GRADED = [
        'essay', 'speaking', 'writing',
    ];

    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $type = $this->input('type');

        return [
            // ── Core ─────────────────────────────────────────────────────────
            'type'             => 'required|in:' . implode(',', self::TYPES),
            'content'          => 'required|string',
            'difficulty'       => 'sometimes|in:easy,medium,hard',
            'chapter_tag'      => 'nullable|string|max:100',
            'grade_id'         => 'nullable|exists:grades,id',
            'lesson_id'        => 'nullable|exists:lessons,id',
            'question_bank_id' => 'nullable|exists:question_bank,id',

            // ── Media ────────────────────────────────────────────────────────
            'media_type'       => 'nullable|in:image,audio,video',
            'media_path'       => 'nullable|string|max:500',
            'audio_path'       => 'nullable|string|max:500',  // listening / speaking

            // ── Options ──────────────────────────────────────────────────────
            // Flexible: array (most types) or object (matching)
            // Validated deeper per-type below
            'options'          => [
                in_array($type, self::NEEDS_OPTIONS) ? 'required' : 'nullable',
            ],

            // ── Correct answer ───────────────────────────────────────────────
            // null for teacher-graded types; string/array/object for auto-graded
            'correct_answer'   => [
                in_array($type, self::TEACHER_GRADED) ? 'nullable' : 'nullable',
            ],

            // ── Scoring ──────────────────────────────────────────────────────
            'points'           => 'sometimes|numeric|min:0.25|max:100',
            'order_index'      => 'sometimes|integer|min:0',
            'explanation'      => 'nullable|string',

            // ── Sub-questions (reading, listening, multi_step, etc.) ─────────
            'sub_questions'              => 'nullable|array',
            'sub_questions.*.type'       => 'required_with:sub_questions|in:' . implode(',', self::TYPES),
            'sub_questions.*.content'    => 'required_with:sub_questions|string',
            'sub_questions.*.options'    => 'nullable',
            'sub_questions.*.correct_answer' => 'nullable',
            'sub_questions.*.points'     => 'sometimes|numeric|min:0',

            // ── Type-specific metadata ────────────────────────────────────────
            // calculation: {tolerance, unit, accept_range}
            // code_runner: {language, test_cases:[{input, expected}]}
            // code_debug:  {buggy_code, language}
            // code_output: {code, language}
            // code_fill:   {template, blanks:[{id, position}]}
            // speaking:    {prompt_audio_path, time_limit_seconds}
            // fill_blank:  {case_sensitive, alternatives}
            'metadata'                   => 'nullable|array',
            'metadata.tolerance'         => 'nullable|numeric|min:0',
            'metadata.unit'              => 'nullable|string|max:50',
            'metadata.language'          => 'nullable|in:javascript,python,php,java,c,cpp',
            'metadata.test_cases'        => 'nullable|array',
            'metadata.test_cases.*.input'    => 'required_with:metadata.test_cases|string',
            'metadata.test_cases.*.expected' => 'required_with:metadata.test_cases|string',
            'metadata.case_sensitive'    => 'nullable|boolean',
            'metadata.time_limit_seconds'=> 'nullable|integer|min:10|max:300',
        ];
    }

    public function messages(): array
    {
        return [
            'type.in'                       => 'Loại câu hỏi không hợp lệ.',
            'options.required'              => 'Loại câu hỏi này bắt buộc phải có đáp án lựa chọn.',
            'sub_questions.*.type.required_with' => 'Mỗi câu hỏi con phải có loại.',
            'sub_questions.*.content.required_with' => 'Mỗi câu hỏi con phải có nội dung.',
        ];
    }
}
