<?php

namespace App\DTOs;

readonly class OrderItemDTO
{
    public function __construct(
        public int $productId,
        public int $quantity,
        public float $unitPrice,
        public ?array $variants = null,
        public ?array $supplements = null,
        public ?string $notes = null,
    ) {}

    public function subtotal(): float
    {
        $basePrice = $this->unitPrice * $this->quantity;
        $supplementsPrice = $this->supplements 
            ? array_sum(array_column($this->supplements, 'price')) * $this->quantity
            : 0;
        
        return $basePrice + $supplementsPrice;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            productId: $data['product_id'],
            quantity: $data['quantity'],
            unitPrice: $data['unit_price'],
            variants: $data['variants'] ?? null,
            supplements: $data['supplements'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }
}
