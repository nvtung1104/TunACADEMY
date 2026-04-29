<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExamRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $type = $this->input('type', 'quiz_15');
        $isPractice = $type === 'practice_exam';

        return [
            'subject_id'          => 'required|exists:subjects,id',
            'classroom_id'        => 'nullable|exists:classrooms,id',

            // Loại đề: quiz_15 / quiz_30 / quiz_45 / practice_exam
            'type'                => 'required|in:quiz_15,quiz_30,quiz_45,practice_exam',

            'title'               => 'required|string|max:255',
            'description'         => 'nullable|string',

            // Bài kiểm tra cần thời gian; đề thi ôn tập thì không bắt buộc
            'duration_minutes'    => [
                $isPractice ? 'nullable' : 'required',
                'integer', 'min:5', 'max:300',
            ],

            // Thời gian mở/đóng: không cần với đề thi ôn tập
            'opened_at'           => [
                $isPractice ? 'nullable' : 'required',
                'nullable', 'date', 'after:now',
            ],
            'closed_at'           => [
                $isPractice ? 'nullable' : 'required',
                'nullable', 'date', 'after:opened_at',
            ],

            'visibility'          => 'sometimes|in:public,private,class,selected',
            'shuffle_questions'   => 'sometimes|boolean',
            'shuffle_options'     => 'sometimes|boolean',

            // Chống gian lận chỉ dùng cho bài kiểm tra, không cho đề thi ôn tập
            'proctoring_enabled'  => 'sometimes|boolean',
            'max_violations'      => 'sometimes|integer|min:1|max:20',

            'show_result'         => 'sometimes|in:immediate,after_close,manual',
            'allow_retake'        => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'type.in'          => 'Loại đề phải là: quiz_15, quiz_30, quiz_45, hoặc practice_exam.',
            'opened_at.after'  => 'Thời gian mở phải sau thời điểm hiện tại.',
            'closed_at.after'  => 'Thời gian đóng phải sau thời gian mở.',
        ];
    }
}
