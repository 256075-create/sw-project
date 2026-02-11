<?php

namespace App\Modules\Student\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $studentId = $this->route('studentId');

        return [
            'major_id' => 'sometimes|integer|exists:academic_majors,major_id',
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'email' => "sometimes|email|max:200|unique:student_students,email,{$studentId},student_id",
            'status' => 'sometimes|string|in:active,inactive,graduated,suspended',
        ];
    }
}
