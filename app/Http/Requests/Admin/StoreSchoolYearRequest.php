<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSchoolYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $schoolYearId = $this->route('school_year')?->id;

        return [
            'name'       => 'required|string|max:50|unique:school_years,name,' . $schoolYearId,
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ];
    }
}
