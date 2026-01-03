<?php

namespace App\DTOs;

readonly class SplitBillDTO
{
    /**
     * @param array<int, float> $splits [customer_id => amount]
     */
    public function __construct(
        public int $orderId,
        public array $splits,
    ) {}

    public function totalSplitAmount(): float
    {
        return array_sum($this->splits);
    }

    public function validate(float $orderTotal): bool
    {
        return abs($this->totalSplitAmount() - $orderTotal) < 0.01;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            orderId: $data['order_id'],
            splits: $data['splits'],
        );
    }
}
