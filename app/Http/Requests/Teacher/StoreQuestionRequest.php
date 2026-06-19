<?php

namespace App\Http\Requests\Teacher;

use App\Services\QuestionService;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('teacher') ?? false;
    }

    public function rules(): array
    {
        $allTypes = QuestionService::getAllQuestionTypes();

        return [
            'grade_id'        => 'nullable|exists:grades,id',
            'lesson_id'       => 'nullable|exists:lessons,id',
            'type'            => 'required|in:' . implode(',', $allTypes),
            'difficulty'      => 'required|in:easy,medium,hard',
            'chapter_tag'     => 'nullable|string|max:100',
            'content'         => 'required|string|max:10000',
            'media_type'      => 'nullable|in:image,audio,video',
            'media_path'      => 'nullable|string|max:500',
            'audio_path'      => 'nullable|string|max:500',
            'options'         => 'nullable|array',
            'correct_answer'  => 'nullable',
            'explanation'     => 'nullable|string',
            'sub_questions'   => 'nullable|array',
            'metadata'        => 'nullable|array',
            'points'          => 'required|numeric|min:0|max:100',
            'order_index'     => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'    => 'Vui lòng chọn loại câu hỏi',
            'type.in'          => 'Loại câu hỏi không hợp lệ',
            'content.required' => 'Vui lòng nhập nội dung câu hỏi',
            'points.required'  => 'Vui lòng nhập điểm số',
            'points.min'       => 'Điểm số phải lớn hơn hoặc bằng 0',
            'points.max'       => 'Điểm số không được vượt quá 100',
        ];
    }
}
