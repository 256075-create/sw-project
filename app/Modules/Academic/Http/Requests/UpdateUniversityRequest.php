<?php

namespace App\Modules\Academic\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUniversityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $universityId = $this->route('universityId');

        return [
            'name' => ['sometimes', 'string', 'max:200'],
            'code' => ['sometimes', 'string', 'max:20', "unique:academic_universities,code,{$universityId},university_id"],
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'A university with this code already exists.',
        ];
    }
}
