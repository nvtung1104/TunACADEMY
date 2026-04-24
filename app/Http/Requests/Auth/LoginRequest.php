<?php
namespace App\Http\Requests\Auth;
use Illuminate\Foundation\Http\FormRequest;
class LoginRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'email'    => 'required|email',
            'password' => 'required|string',
        ];
    }
    public function messages(): array
    {
        return [
            'email.required'    => 'Vui long nhap email',
            'email.email'       => 'Email khong hop le',
            'password.required' => 'Vui long nhap mat khau',
        ];
    }
}
