<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CreateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'raison_sociale'      => 'required|string|max:255',
            'registration_number' => 'required|string|max:255|unique:companies,registration_number',
            'email'               => 'required|email|unique:companies,email',
            'phone'               => 'required|string|max:20',
            'address'             => 'required|string|max:500',
            'city'                => 'required|string|max:100',
            'country'             => 'required|string|max:100',
            'credit_limit'        => 'required|numeric|min:0',
            'is_active'           => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'raison_sociale.required'      => 'La raison sociale est obligatoire.',
            'registration_number.required' => 'Le numéro d\'enregistrement est obligatoire.',
            'registration_number.unique'   => 'Ce numéro d\'enregistrement est déjà utilisé.',
            'email.required'               => 'L\'email est obligatoire.',
            'email.email'                  => 'L\'email doit être une adresse valide.',
            'email.unique'                 => 'Cet email est déjà utilisé.',
            'phone.required'               => 'Le téléphone est obligatoire.',
            'address.required'             => 'L\'adresse est obligatoire.',
            'city.required'                => 'La ville est obligatoire.',
            'country.required'             => 'Le pays est obligatoire.',
            'credit_limit.required'        => 'La limite de crédit est obligatoire.',
            'credit_limit.numeric'         => 'La limite de crédit doit être un nombre.',
            'credit_limit.min'             => 'La limite de crédit doit être ≥ 0.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }
    }
}
