<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'exists:customers,id'],
            'table_number' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', Rule::enum(OrderStatus::class)],
            'notes' => ['nullable', 'string', 'max:500'],
            
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.variants' => ['nullable', 'array'],
            'items.*.variants.*.id' => ['required_with:items.*.variants', 'integer'],
            'items.*.variants.*.name' => ['required_with:items.*.variants', 'string'],
            'items.*.supplements' => ['nullable', 'array'],
            'items.*.supplements.*.id' => ['required_with:items.*.supplements', 'integer'],
            'items.*.supplements.*.name' => ['required_with:items.*.supplements', 'string'],
            'items.*.supplements.*.price' => ['required_with:items.*.supplements', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'La commande doit contenir au moins un article',
            'items.*.product_id.exists' => 'Produit invalide',
            'items.*.quantity.min' => 'La quantité doit être au moins 1',
            'items.*.unit_price.min' => 'Le prix unitaire doit être positif',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validation personnalisée : vérifier le stock disponible
            if ($this->has('items')) {
                foreach ($this->items as $index => $item) {
                    $product = \App\Models\Product::find($item['product_id']);
                    
                    if ($product && $product->track_stock && $product->stock < $item['quantity']) {
                        $validator->errors()->add(
                            "items.{$index}.quantity",
                            "Stock insuffisant pour {$product->name}. Disponible: {$product->stock}"
                        );
                    }
                }
            }
        });
    }
}
