<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Customer;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $customerId = $this->route('customer')->id;

        $rules = [
            'type' => 'required|in:' . Customer::TYPE_INDIVIDUAL . ',' . Customer::TYPE_COMPANY,
            'email' => [
                'required',
                'email',
                Rule::unique('customers', 'email')->ignore($customerId),
            ],
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'is_active' => 'boolean',
        ];

        if ($this->input('type') === Customer::TYPE_INDIVIDUAL) {
            $rules['first_name'] = 'required|string|max:255';
            $rules['last_name'] = 'required|string|max:255';
        } else {
            $rules['company_id'] = 'required|exists:companies,id';
            $rules['company_contact_name'] = 'nullable|string|max:255';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Le type de client est obligatoire.',
            'type.in' => 'Le type de client doit être "individual" ou "company".',

            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',

            'company_id.required' => 'L\'entreprise est obligatoire.',
            'company_id.exists' => 'L\'entreprise sélectionnée n\'existe pas.',

            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'L\'email doit être une adresse email valide.',
            'email.unique' => 'Cet email est déjà utilisé.',

            'phone.required' => 'Le téléphone est obligatoire.',
            'address.required' => 'L\'adresse est obligatoire.',
        ];
    }
}
