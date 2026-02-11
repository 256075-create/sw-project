<?php

namespace App\Modules\Academic\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMajorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'department_id' => ['sometimes', 'integer', 'exists:academic_departments,department_id'],
            'name' => ['sometimes', 'string', 'max:200'],
            'code' => ['sometimes', 'string', 'max:20'],
            'total_credits' => ['sometimes', 'integer', 'min:0'],
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
