<?php
namespace App\Http\Requests\Teacher;
use Illuminate\Foundation\Http\FormRequest;
class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'type'           => 'required|in:multiple_choice,fill_blank,essay,listening,reading,arrange',
            'content'        => 'required|string',
            'options'        => 'nullable|array',
            'options.*.id'   => 'required_with:options|string',
            'options.*.text' => 'required_with:options|string',
            'correct_answer' => 'nullable|string',
            'points'         => 'sometimes|numeric|min:0.25|max:100',
            'explanation'    => 'nullable|string',
            'order_index'    => 'sometimes|integer|min:0',
            'chapter_tag'    => 'nullable|string|max:100',
        ];
    }
}
