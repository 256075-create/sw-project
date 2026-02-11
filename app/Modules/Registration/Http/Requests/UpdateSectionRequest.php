<?php

namespace App\Modules\Registration\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => 'sometimes|integer|exists:registration_courses,course_id',
            'classroom_id' => 'sometimes|integer|exists:registration_classrooms,classroom_id',
            'section_number' => 'sometimes|string|max:20',
            'instructor_name' => 'sometimes|string|max:200',
            'max_capacity' => 'sometimes|integer|min:1',
            'semester' => 'sometimes|string|max:20',
            'academic_year' => 'sometimes|string|max:10',
        ];
    }
}
