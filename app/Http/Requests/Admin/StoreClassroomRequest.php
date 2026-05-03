<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreClassroomRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        // Khi update, bỏ qua chính lớp đang sửa
        $currentClassroomId = $this->route('classroom')?->id;

        return [
            'name'                => 'required|string|max:20',
            'grade_id'            => 'required|exists:grades,id',
            'school_year_id'      => 'required|exists:school_years,id',
            'homeroom_teacher_id' => [
                'nullable',
                'exists:users,id',
                Rule::unique('classrooms', 'homeroom_teacher_id')
                    ->ignore($currentClassroomId)
                    ->whereNotNull('homeroom_teacher_id'),
            ],
            'max_students'        => 'sometimes|integer|min:1|max:60',
            'status'              => 'sometimes|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'homeroom_teacher_id.unique' => 'Giáo viên này đã là chủ nhiệm của lớp khác.',
        ];
    }
}
