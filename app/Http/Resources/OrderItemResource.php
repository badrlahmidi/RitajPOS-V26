<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product' => $this->when(
                $this->relationLoaded('product'),
                fn() => [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'image' => $this->product->image_url,
                ]
            ),
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'subtotal' => $this->subtotal,
            'formatted_subtotal' => number_format($this->subtotal, 2) . ' DH',
            'variants' => $this->variants,
            'supplements' => $this->supplements,
            'notes' => $this->notes,
        ];
    }
}
