<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Product;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $productId = $this->route('product')->id;

        $rules = [
            'type'                      => 'required|in:' . Product::TYPE_SIMPLE . ',' . Product::TYPE_VARIABLE,
            'name'                      => 'required|string|max:255',
            'slug'                      => ['nullable','string','max:255', Rule::unique('products','slug')->ignore($productId)],
            'description'               => 'nullable|string',
            'sku'                       => ['nullable','string','max:100', Rule::unique('products','sku')->ignore($productId)],
            'is_published'              => 'boolean',
            'is_active'                 => 'boolean',
            'is_service'                => 'boolean',
            'provider'                  => 'nullable|string|max:255',
            'credit_enabled'            => 'boolean',
            'credit_duration_months'    => 'nullable|integer|min:1|max:24',
            'credit_installments_count' => 'nullable|integer|min:1|max:12',
        ];

        $isService = $this->boolean('is_service');

        if ($this->input('type') === Product::TYPE_SIMPLE && !$isService) {
            $rules['price']          = 'required|numeric|min:0';
            $rules['stock_quantity'] = 'required|integer|min:0';
        } elseif ($this->input('type') === Product::TYPE_SIMPLE && $isService) {
            $rules['price']          = 'nullable|numeric|min:0';
            $rules['stock_quantity'] = 'nullable|integer|min:0';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Le type de produit est obligatoire.',
            'name.required' => 'Le nom du produit est obligatoire.',
            'sku.required' => 'Le SKU est obligatoire.',
            'sku.unique' => 'Ce SKU est déjà utilisé.',
            'price.required' => 'Le prix est obligatoire pour un produit simple.',
            'price.numeric' => 'Le prix doit être un nombre.',
            'price.min' => 'Le prix doit être supérieur ou égal à 0.',
            'stock_quantity.required' => 'La quantité en stock est obligatoire.',
            'credit_duration_months.max' => 'La durée maximale est de 24 mois.',
            'credit_installments_count.max' => 'Le nombre maximum de mensualités est 12.',
        ];
    }
}
