<?php

namespace App\Actions\Orders;

use App\DTOs\CreateOrderDTO;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateOrderAction
{
    public function execute(CreateOrderDTO $dto): Order
    {
        return DB::transaction(function () use ($dto) {
            // Créer la commande principale
            $order = Order::create([
                'customer_id' => $dto->customerId,
                'user_id' => $dto->userId,
                'table_number' => $dto->tableNumber,
                'status' => $dto->status->value,
                'notes' => $dto->notes,
                'subtotal' => $dto->calculateTotal(),
                'tax' => 0, // À calculer selon vos règles
                'total' => $dto->calculateTotal(),
            ]);

            // Créer les items et mettre à jour le stock
            foreach ($dto->items as $itemDTO) {
                // Lock pessimiste pour éviter les race conditions
                $product = Product::lockForUpdate()->findOrFail($itemDTO->productId);

                // Vérifier le stock disponible
                if ($product->track_stock && $product->stock < $itemDTO->quantity) {
                    throw new \DomainException(
                        "Stock insuffisant pour {$product->name}. Disponible: {$product->stock}"
                    );
                }

                // Créer l'item de commande
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $itemDTO->productId,
                    'quantity' => $itemDTO->quantity,
                    'unit_price' => $itemDTO->unitPrice,
                    'subtotal' => $itemDTO->subtotal(),
                    'variants' => $itemDTO->variants,
                    'supplements' => $itemDTO->supplements,
                    'notes' => $itemDTO->notes,
                ]);

                // Décrémenter le stock si le tracking est activé
                if ($product->track_stock) {
                    $product->decrement('stock', $itemDTO->quantity);
                }
            }

            Log::info('Order created', [
                'order_id' => $order->id,
                'user_id' => $dto->userId,
                'total' => $order->total,
            ]);

            return $order->fresh(['items.product', 'customer', 'user']);
        });
    }
}
