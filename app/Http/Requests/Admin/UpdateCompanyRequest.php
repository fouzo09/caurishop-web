<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $companyId = $this->route('company')->id;

        return [
            'name' => 'required|string|max:255',
            'registration_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('companies', 'registration_number')->ignore($companyId),
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('companies', 'email')->ignore($companyId),
            ],
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'credit_limit' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de l\'entreprise est obligatoire.',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',

            'registration_number.required' => 'Le numéro d\'enregistrement est obligatoire.',
            'registration_number.unique' => 'Ce numéro d\'enregistrement est déjà utilisé.',

            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'L\'email doit être une adresse email valide.',
            'email.unique' => 'Cet email est déjà utilisé.',

            'phone.required' => 'Le téléphone est obligatoire.',

            'address.required' => 'L\'adresse est obligatoire.',

            'city.required' => 'La ville est obligatoire.',

            'country.required' => 'Le pays est obligatoire.',

            'credit_limit.required' => 'La limite de crédit est obligatoire.',
            'credit_limit.numeric' => 'La limite de crédit doit être un nombre.',
            'credit_limit.min' => 'La limite de crédit doit être supérieure ou égale à 0.',
        ];
    }
}
