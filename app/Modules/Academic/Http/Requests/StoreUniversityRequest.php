<?php

namespace App\Modules\Academic\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUniversityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'code' => ['required', 'string', 'max:20', 'unique:academic_universities,code'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'A university with this code already exists.',
        ];
    }
}
