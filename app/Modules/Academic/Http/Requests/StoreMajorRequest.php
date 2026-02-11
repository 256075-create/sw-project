<?php

namespace App\Modules\Academic\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMajorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'department_id' => ['required', 'integer', 'exists:academic_departments,department_id'],
            'name' => ['required', 'string', 'max:200'],
            'code' => ['required', 'string', 'max:20'],
            'total_credits' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'department_id.exists' => 'The specified department does not exist.',
            'total_credits.min' => 'Total credits must be a non-negative integer.',
        ];
    }
}
