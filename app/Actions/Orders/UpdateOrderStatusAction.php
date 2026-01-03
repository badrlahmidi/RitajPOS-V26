<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class UpdateOrderStatusAction
{
    public function execute(Order $order, OrderStatus $newStatus): Order
    {
        $currentStatus = OrderStatus::from($order->status);

        // Valider la transition de statut
        if (!$currentStatus->canTransitionTo($newStatus)) {
            throw new \DomainException(
                "Cannot transition from {$currentStatus->label()} to {$newStatus->label()}"
            );
        }

        $order->update(['status' => $newStatus->value]);

        Log::info('Order status updated', [
            'order_id' => $order->id,
            'from' => $currentStatus->value,
            'to' => $newStatus->value,
        ]);

        return $order->fresh();
    }
}
