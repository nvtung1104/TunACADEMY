<?php
namespace App\Http\Requests\Teacher;
use Illuminate\Foundation\Http\FormRequest;
class StoreAssignmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id'   => 'required|exists:subjects,id',
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'type'         => 'required|in:quiz,essay,fill_blank,matching,upload,listening,writing',
            'deadline'     => 'required|date|after:now',
            'max_score'    => 'sometimes|numeric|min:1|max:100',
            'allow_late'   => 'sometimes|boolean',
            'status'       => 'sometimes|in:draft,published,closed',
        ];
    }
}
