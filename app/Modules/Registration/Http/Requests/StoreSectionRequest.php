<?php

namespace App\Modules\Registration\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => 'required|integer|exists:registration_courses,course_id',
            'classroom_id' => 'required|integer|exists:registration_classrooms,classroom_id',
            'section_number' => 'required|string|max:20',
            'instructor_name' => 'required|string|max:200',
            'max_capacity' => 'required|integer|min:1',
            'semester' => 'required|string|max:20',
            'academic_year' => 'required|string|max:10',
        ];
    }
}
