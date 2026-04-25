<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $subjectId = $this->route('subject')?->id;

        return [
            'name'                => 'required|string|max:100',
            'code'                => 'required|string|max:20|unique:subjects,code,' . $subjectId,
            'color'               => 'nullable|string|max:20',
            'icon'                => 'nullable|string|max:50',
            'applicable_grades'   => 'nullable|array',
            'applicable_grades.*' => 'integer|min:1|max:12',
            'status'              => 'required|in:active,inactive',
        ];
    }
}
