<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Deliberately no `exists:users,email` rule — avoids revealing whether
        // an email is registered (the controller always returns a generic message).
        return [
            'email' => ['required', 'email'],
        ];
    }
}
