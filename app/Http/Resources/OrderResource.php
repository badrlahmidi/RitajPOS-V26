<?php

namespace App\Http\Resources;

use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = OrderStatus::from($this->status);

        return [
            'id' => $this->id,
            'reference' => $this->reference ?? "ORD-{$this->id}",
            'customer' => $this->when(
                $this->relationLoaded('customer'),
                fn() => new CustomerResource($this->customer)
            ),
            'user' => $this->when(
                $this->relationLoaded('user'),
                fn() => [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                ]
            ),
            'table_number' => $this->table_number,
            'status' => [
                'value' => $status->value,
                'label' => $status->label(),
                'color' => $status->color(),
            ],
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'subtotal' => $this->subtotal,
            'tax' => $this->tax ?? 0,
            'total' => $this->total,
            'formatted_total' => number_format($this->total, 2) . ' DH',
            'received_amount' => $this->receivedAmount(),
            'remaining_balance' => $this->remainingBalance(),
            'is_fully_paid' => $this->isFullyPaid(),
            'notes' => $this->notes,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
