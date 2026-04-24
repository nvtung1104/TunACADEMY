<?php
namespace App\Http\Requests\Teacher;
use Illuminate\Foundation\Http\FormRequest;
class StoreExamRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'classroom_id'        => 'required|exists:classrooms,id',
            'subject_id'          => 'required|exists:subjects,id',
            'title'               => 'required|string|max:255',
            'description'         => 'nullable|string',
            'duration_minutes'    => 'required|integer|min:5|max:300',
            'opened_at'           => 'required|date|after:now',
            'closed_at'           => 'required|date|after:opened_at',
            'shuffle_questions'   => 'sometimes|boolean',
            'shuffle_options'     => 'sometimes|boolean',
            'proctoring_enabled'  => 'sometimes|boolean',
            'max_violations'      => 'sometimes|integer|min:1',
            'show_result'         => 'sometimes|in:immediate,after_close,manual',
            'allow_retake'        => 'sometimes|boolean',
        ];
    }
}
