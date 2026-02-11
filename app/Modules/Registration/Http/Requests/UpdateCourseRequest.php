<?php

namespace App\Modules\Registration\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $courseId = $this->route('courseId');
        return [
            'course_code' => "sometimes|string|max:20|unique:registration_courses,course_code,{$courseId},course_id",
            'name' => 'sometimes|string|max:200',
            'description' => 'nullable|string',
            'credit_hours' => 'sometimes|integer|min:1|max:12',
            'is_active' => 'boolean',
        ];
    }
}
