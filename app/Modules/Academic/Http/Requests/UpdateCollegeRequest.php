<?php

namespace App\Modules\Academic\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCollegeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'university_id' => ['sometimes', 'integer', 'exists:academic_universities,university_id'],
            'name' => ['sometimes', 'string', 'max:200'],
            'code' => ['sometimes', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'university_id.exists' => 'The specified university does not exist.',
        ];
    }
}
