<?php
namespace App\Http\Requests\Teacher;
use Illuminate\Foundation\Http\FormRequest;
class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id'   => 'required|exists:subjects,id',
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'content'      => 'nullable|string',
            'video_path'   => 'nullable|string|max:500',
            'order_index'  => 'sometimes|integer|min:0',
            'status'       => 'sometimes|in:draft,published,hidden',
        ];
    }
}
