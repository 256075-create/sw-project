<?php

namespace App\Modules\Student\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EnrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => 'required|integer|exists:student_students,student_id',
            'section_id' => 'required|integer|exists:registration_sections,section_id',
        ];
    }
}
