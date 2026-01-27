<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $variantId = $this->route('variant')->id;

        return [
            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('product_variants', 'sku')->ignore($variantId),
            ],
            'name' => 'required|string|max:255',
            'attributes' => 'nullable|array',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'credit_enabled' => 'nullable|boolean',
            'credit_duration_months' => 'nullable|integer|min:1|max:24',
            'credit_installments_count' => 'nullable|integer|min:1|max:12',
        ];
    }

    public function messages(): array
    {
        return [
            'sku.required' => 'Le SKU est obligatoire.',
            'sku.unique' => 'Ce SKU est déjà utilisé.',
            'name.required' => 'Le nom de la variante est obligatoire.',
            'price.required' => 'Le prix est obligatoire.',
            'price.numeric' => 'Le prix doit être un nombre.',
            'stock_quantity.required' => 'La quantité en stock est obligatoire.',
        ];
    }
}
