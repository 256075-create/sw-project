<?php

namespace App\Modules\Registration\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_code' => 'required|string|max:20|unique:registration_courses,course_code',
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'credit_hours' => 'required|integer|min:1|max:12',
            'is_active' => 'boolean',
        ];
    }
}
