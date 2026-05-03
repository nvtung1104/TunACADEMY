<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
class StoreUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'password'       => 'required|string|min:8',
            'role'           => 'required|in:admin,teacher,student',
            'phone'          => 'nullable|string|max:20',
            'status'         => 'sometimes|in:active,inactive,suspended',
            'qualification'  => 'nullable|string|max:255',
            'date_of_birth'  => 'nullable|date',
            'gender'         => 'nullable|in:male,female,other',
            'address'        => 'nullable|string|max:500',
            'parent_name'    => 'nullable|string|max:255',
            'parent_phone'   => 'nullable|string|max:20',
            'parent_email'   => 'nullable|email|max:255',
            'parent_address' => 'nullable|string|max:500',
            'classroom_id'   => 'nullable|integer|exists:classrooms,id',
            'subject_ids'    => 'nullable|array',
            'subject_ids.*'  => 'integer|exists:subjects,id',
        ];
    }
}
