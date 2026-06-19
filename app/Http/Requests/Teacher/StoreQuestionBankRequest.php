<?php

namespace App\Http\Requests\Teacher;

use App\Services\QuestionService;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionBankRequest extends FormRequest
{

    public function authorize(): bool
    {
        return $this->user()?->hasRole('teacher') ?? false;
    }

    public function rules(): array
    {
        $types = QuestionService::getAllQuestionTypes();

        return [
            'subject_id'       => 'required|exists:subjects,id',
            'grade_id'         => 'nullable|exists:grades,id',
            'lesson_id'        => 'nullable|exists:lessons,id',
            'type'             => 'required|in:' . implode(',', $types),
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
            'sub_questions.*.type'       => 'required_with:sub_questions|in:' . implode(',', $types),
            'sub_questions.*.content'    => 'required_with:sub_questions|string',
            'sub_questions.*.options'    => 'nullable',
            'sub_questions.*.correct_answer' => 'nullable',
            'sub_questions.*.points'     => 'sometimes|numeric|min:0',
            'metadata'         => 'nullable|array',
            'default_points'   => 'sometimes|numeric|min:0.25|max:100',
            'is_public'        => 'sometimes|boolean',
            'assign_to_type'   => 'nullable|in:exam,assignment',
            'assign_to_id'     => 'nullable|integer',
        ];
    }
}
