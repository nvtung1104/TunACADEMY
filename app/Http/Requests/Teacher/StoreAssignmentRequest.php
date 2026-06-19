<?php
namespace App\Http\Requests\Teacher;

use App\Http\Requests\Concerns\SanitizesHtml;
use App\Models\Assignment;
use Illuminate\Foundation\Http\FormRequest;

class StoreAssignmentRequest extends FormRequest
{
    use SanitizesHtml;

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title'       => $this->stripAllHtml($this->input('title')),
            'description' => $this->stripAllHtml($this->input('description')),
        ]);
    }

    public function authorize(): bool
    {
        $assignment = $this->route('assignment');
        if ($assignment instanceof Assignment) {
            return $this->user()->can('update', $assignment);
        }
        return $this->user()->can('create', Assignment::class);
    }
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
