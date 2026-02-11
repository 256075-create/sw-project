<?php

namespace App\Modules\Academic\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'college_id' => ['required', 'integer', 'exists:academic_colleges,college_id'],
            'name' => ['required', 'string', 'max:200'],
            'code' => ['required', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'college_id.exists' => 'The specified college does not exist.',
        ];
    }
}
