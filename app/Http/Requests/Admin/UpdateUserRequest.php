<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'name'           => 'sometimes|string|max:255',
            'email'          => 'sometimes|email|unique:users,email,' . $this->route('user')->id,
            'password'       => 'nullable|string|min:8',
            'role'           => 'sometimes|in:admin,teacher,student',
            'phone'          => 'nullable|string|max:20',
            'status'         => 'sometimes|in:active,inactive,suspended',
            'qualification'  => 'nullable|string|max:255',
            'date_of_birth'  => 'nullable|date',
            'gender'         => 'nullable|in:male,female,other',
            'address'        => 'nullable|string|max:500',
            'parent_name'    => 'nullable|string|max:255',
            'parent_phone'   => 'nullable|string|max:20',
            'parent_address' => 'nullable|string|max:500',
            'classroom_id'   => 'nullable|integer|exists:classrooms,id',
            'subject_ids'    => 'nullable|array',
            'subject_ids.*'  => 'integer|exists:subjects,id',
        ];
    }
}
