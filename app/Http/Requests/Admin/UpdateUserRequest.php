<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'name'          => 'sometimes|string|max:255',
            'email'         => 'sometimes|email|unique:users,email,' . $this->route('user')->id,
            'password'      => 'nullable|string|min:8',
            'role'          => 'sometimes|in:admin,teacher,student',
            'phone'         => 'nullable|string|max:20',
            'status'        => 'sometimes|in:active,inactive,suspended',
            'qualification' => 'nullable|string|max:100',
        ];
    }
}
