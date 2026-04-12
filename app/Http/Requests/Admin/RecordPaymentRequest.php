<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RecordPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'amount'       => 'required|numeric|min:0.01',
            'method'       => 'required|in:cash,transfer,mobile_money,card,other',
            'reference'    => 'nullable|string|max:255',
            'payment_date' => 'required|date',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required'       => 'Le montant est obligatoire.',
            'amount.min'            => 'Le montant doit être supérieur à 0.',
            'method.required'       => 'La méthode de paiement est obligatoire.',
            'payment_date.required' => 'La date de paiement est obligatoire.',
        ];
    }
}
