<?php

namespace App\Modules\Student\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'major_id' => 'required|integer|exists:academic_majors,major_id',
            'user_id' => 'nullable|string|exists:identity_users,user_id',
            'student_number' => 'nullable|string|max:50|unique:student_students,student_number',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:200|unique:student_students,email',
            'enrollment_date' => 'nullable|date',
            'status' => 'nullable|string|in:active,inactive,graduated,suspended',
        ];
    }
}
