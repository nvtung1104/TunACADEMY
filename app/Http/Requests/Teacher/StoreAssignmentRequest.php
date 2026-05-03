<?php
namespace App\Http\Requests\Teacher;
use Illuminate\Foundation\Http\FormRequest;
class StoreAssignmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'classroom_id' => 'nullable|exists:classrooms,id',
            'subject_id'   => 'nullable|exists:subjects,id',
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'type'         => 'nullable|in:quiz,essay,fill_blank,matching,upload,listening,writing',
            'deadline'     => 'nullable|date|after:now',
            'max_score'    => 'sometimes|numeric|min:1|max:100',
            'allow_late'   => 'sometimes|boolean',
            'visibility'   => 'sometimes|in:public,private,class',
        ];
    }
}
