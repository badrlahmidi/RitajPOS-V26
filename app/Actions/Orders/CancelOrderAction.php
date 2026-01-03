<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancelOrderAction
{
    public function execute(Order $order, string $reason): Order
    {
        return DB::transaction(function () use ($order, $reason) {
            $currentStatus = OrderStatus::from($order->status);

            // Vérifier si l'annulation est possible
            if (!$currentStatus->canTransitionTo(OrderStatus::CANCELLED)) {
                throw new \DomainException(
                    "Cannot cancel order with status {$currentStatus->label()}"
                );
            }

            // Restaurer le stock pour chaque item
            foreach ($order->items as $item) {
                $product = Product::lockForUpdate()->find($item->product_id);
                
                if ($product && $product->track_stock) {
                    $product->increment('stock', $item->quantity);
                }
            }

            // Mettre à jour le statut de la commande
            $order->update([
                'status' => OrderStatus::CANCELLED->value,
                'cancellation_reason' => $reason,
                'cancelled_at' => now(),
            ]);

            Log::warning('Order cancelled', [
                'order_id' => $order->id,
                'reason' => $reason,
                'previous_status' => $currentStatus->value,
            ]);

            return $order->fresh();
        });
    }
}
