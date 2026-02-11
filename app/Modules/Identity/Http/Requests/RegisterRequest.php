<?php

namespace App\Modules\Identity\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:100', 'unique:identity_users,username'],
            'email' => ['required', 'email', 'max:200', 'unique:identity_users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
