<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Product;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $rules = [
            'type'                      => 'required|in:' . Product::TYPE_SIMPLE . ',' . Product::TYPE_VARIABLE,
            'name'                      => 'required|string|max:255',
            'slug'                      => 'nullable|string|max:255|unique:products,slug',
            'description'               => 'nullable|string',
            'sku'                       => 'nullable|string|max:100|unique:products,sku',
            'is_published'              => 'boolean',
            'is_active'                 => 'boolean',
            'is_service'                => 'boolean',
            'provider'                  => 'nullable|string|max:255',
            'credit_enabled'            => 'boolean',
            'credit_duration_months'    => 'nullable|integer|min:1|max:24',
            'credit_installments_count' => 'nullable|integer|min:1|max:12',

            // Sans ces règles, une image refusée par PHP (trop lourde) était
            // ignorée en silence : le produit se créait sans aucune image.
            'images'                    => 'nullable|array|max:10',
            'images.*'                  => 'image|mimes:jpg,jpeg,png,webp|max:4096',
        ];

        $isService = $this->boolean('is_service');

        $rules['supplier_price'] = 'nullable|numeric|min:0';

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
            'images.max' => 'Vous pouvez envoyer 10 images au maximum.',
            'images.*.image' => "Le fichier :position n'est pas une image valide. Elle est peut-être trop lourde pour le serveur.",
            'images.*.mimes' => 'Formats acceptés : JPG, PNG ou WEBP.',
            'images.*.max' => 'Chaque image doit peser 4 Mo au maximum.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }

        if (!$this->has('is_published')) {
            $this->merge(['is_published' => false]);
        }

        if (!$this->has('credit_enabled')) {
            $this->merge(['credit_enabled' => false]);
        }

        if (!$this->has('is_service')) {
            $this->merge(['is_service' => false]);
        }
    }
}
