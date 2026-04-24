<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
class StoreClassroomRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'name'                => 'required|string|max:20',
            'grade_id'            => 'required|exists:grades,id',
            'school_year_id'      => 'required|exists:school_years,id',
            'homeroom_teacher_id' => 'nullable|exists:users,id',
            'max_students'        => 'sometimes|integer|min:1|max:60',
            'status'              => 'sometimes|in:active,inactive',
        ];
    }
}
