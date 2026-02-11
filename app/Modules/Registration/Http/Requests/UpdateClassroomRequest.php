<?php

namespace App\Modules\Registration\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_number' => 'sometimes|string|max:20',
            'building' => 'sometimes|string|max:100',
            'capacity' => 'sometimes|integer|min:1',
        ];
    }
}
