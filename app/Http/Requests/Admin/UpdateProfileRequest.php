<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore(auth()->id())],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Le nom est obligatoire.',
            'email.required' => 'L\'email est obligatoire.',
            'email.unique'   => 'Cette adresse email est déjà utilisée.',
        ];
    }
}
