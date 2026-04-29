<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionBankRequest extends FormRequest
{
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

    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'subject_id'       => 'required|exists:subjects,id',
            'grade_id'         => 'nullable|exists:grades,id',
            'lesson_id'        => 'nullable|exists:lessons,id',
            'type'             => 'required|in:' . implode(',', self::TYPES),
            'difficulty'       => 'sometimes|in:easy,medium,hard',
            'chapter_tag'      => 'nullable|string|max:100',
            'content'          => 'required|string',
            'media_type'       => 'nullable|in:image,audio,video',
            'media_path'       => 'nullable|string|max:500',
            'audio_path'       => 'nullable|string|max:500',
            'options'          => 'nullable',
            'correct_answer'   => 'nullable',
            'explanation'      => 'nullable|string',
            'sub_questions'              => 'nullable|array',
            'sub_questions.*.type'       => 'required_with:sub_questions|in:' . implode(',', self::TYPES),
            'sub_questions.*.content'    => 'required_with:sub_questions|string',
            'sub_questions.*.options'    => 'nullable',
            'sub_questions.*.correct_answer' => 'nullable',
            'sub_questions.*.points'     => 'sometimes|numeric|min:0',
            'metadata'         => 'nullable|array',
            'default_points'   => 'sometimes|numeric|min:0.25|max:100',
            'is_public'        => 'sometimes|boolean',
        ];
    }
}
