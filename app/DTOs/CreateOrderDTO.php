<?php

namespace App\DTOs;

use App\Enums\OrderStatus;
use Illuminate\Support\Collection;

readonly class CreateOrderDTO
{
    /**
     * @param Collection<OrderItemDTO> $items
     */
    public function __construct(
        public ?int $customerId,
        public int $userId,
        public ?int $tableNumber,
        public Collection $items,
        public OrderStatus $status = OrderStatus::PENDING,
        public ?string $notes = null,
    ) {}

    public function calculateTotal(): float
    {
        return $this->items->sum(fn(OrderItemDTO $item) => $item->subtotal());
    }

    public static function fromRequest(array $data, int $userId): self
    {
        $items = collect($data['items'])
            ->map(fn(array $item) => OrderItemDTO::fromArray($item));

        return new self(
            customerId: $data['customer_id'] ?? null,
            userId: $userId,
            tableNumber: $data['table_number'] ?? null,
            items: $items,
            status: isset($data['status']) 
                ? OrderStatus::from($data['status']) 
                : OrderStatus::PENDING,
            notes: $data['notes'] ?? null,
        );
    }
}
