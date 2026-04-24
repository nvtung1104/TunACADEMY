<?php
namespace App\Http\Requests\Teacher;
use Illuminate\Foundation\Http\FormRequest;
class StoreLiveSessionRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'classroom_id'         => 'required|exists:classrooms,id',
            'subject_id'           => 'required|exists:subjects,id',
            'lesson_id'            => 'nullable|exists:lessons,id',
            'title'                => 'required|string|max:255',
            'description'          => 'nullable|string',
            'scheduled_at'         => 'required|date|after:now',
            'duration_minutes'     => 'sometimes|integer|min:5|max:300',
            'max_participants'     => 'sometimes|integer|min:2|max:200',
            'allow_screen_share'   => 'sometimes|boolean',
            'allow_student_mic'    => 'sometimes|boolean',
            'allow_student_cam'    => 'sometimes|boolean',
            'chat_enabled'         => 'sometimes|boolean',
            'recording_enabled'    => 'sometimes|boolean',
        ];
    }
}
