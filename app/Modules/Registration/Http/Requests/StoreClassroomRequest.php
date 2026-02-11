<?php

namespace App\Modules\Registration\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_number' => 'required|string|max:20',
            'building' => 'required|string|max:100',
            'capacity' => 'required|integer|min:1',
        ];
    }
}
