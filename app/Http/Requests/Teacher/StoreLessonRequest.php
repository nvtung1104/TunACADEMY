<?php
namespace App\Http\Requests\Teacher;

use App\Http\Requests\Concerns\SanitizesHtml;
use App\Models\Lesson;
use Illuminate\Foundation\Http\FormRequest;

class StoreLessonRequest extends FormRequest
{
    use SanitizesHtml;

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title'       => $this->stripAllHtml($this->input('title')),
            'description' => $this->stripAllHtml($this->input('description')),
            'content'     => $this->stripDangerousTags($this->input('content')),
        ]);
    }

    public function authorize(): bool
    {
        // On create: any teacher may create a lesson
        // On update: the teacher must own the lesson (checked via route model binding)
        $lesson = $this->route('lesson');
        if ($lesson instanceof Lesson) {
            return $this->user()->can('update', $lesson);
        }
        return $this->user()->can('create', Lesson::class);
    }

    public function rules(): array
    {
        return [
            'classroom_id' => 'required|exists:classrooms,id',
            'subject_id'   => [
                'required',
                'exists:subjects,id',
                function ($attribute, $value, $fail) {
                    $classroomId = $this->input('classroom_id');
                    $teacherId = $this->user()->id;

                    // Cho phép admin bỏ qua kiểm tra này
                    if ($this->user()->hasRole('admin')) {
                        return;
                    }

                    $exists = \App\Models\ClassroomSubjectTeacher::where('teacher_id', $teacherId)
                        ->where('subject_id', $value)
                        ->where('classroom_id', $classroomId)
                        ->exists();

                    if (!$exists) {
                        $fail('Bạn không được phân công giảng dạy môn học này ở lớp đã chọn.');
                    }
                }
            ],
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string|max:5000',
            'content'      => 'nullable|string|max:100000',
            'video_path'   => 'nullable|string|max:500',
            'order_index'  => 'sometimes|integer|min:0',
            'status'       => 'sometimes|in:draft,published,hidden',
        ];
    }
}
