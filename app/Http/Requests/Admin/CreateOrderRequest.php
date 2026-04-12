<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $rules = [
            'customer_id'       => 'required|exists:customers,id',
            'order_type'        => 'required|in:cash,credit',
            'items'             => 'required|array|min:1',
            'items.*.product_id'=> 'required|exists:products,id',
            'items.*.variant_id'=> 'nullable|exists:product_variants,id',
            'items.*.quantity'  => 'required|integer|min:1',
            'down_payment'      => 'nullable|numeric|min:0',
        ];

        if ($this->input('order_type') === 'credit') {
            $rules['credit_installments_count'] = 'required|integer|min:1|max:24';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'customer_id.required'              => 'Veuillez sélectionner un client.',
            'customer_id.exists'                => 'Client introuvable.',
            'order_type.required'               => 'Le type de paiement est obligatoire.',
            'items.required'                    => 'La commande doit contenir au moins un produit.',
            'items.*.product_id.required'       => 'Produit invalide.',
            'items.*.quantity.required'         => 'La quantité est obligatoire.',
            'items.*.quantity.min'              => 'La quantité minimum est 1.',
            'credit_installments_count.required'=> 'Le nombre de mensualités est obligatoire.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'down_payment' => $this->input('down_payment', 0),
        ]);
    }
}
