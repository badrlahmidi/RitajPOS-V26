<?php

namespace App\Actions\Orders;

use App\DTOs\SplitBillDTO;
use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class SplitBillAction
{
    public function execute(SplitBillDTO $dto): array
    {
        return DB::transaction(function () use ($dto) {
            $order = Order::with('payments')->lockForUpdate()->findOrFail($dto->orderId);

            // Valider que le total des splits correspond au total de la commande
            if (!$dto->validate($order->total)) {
                throw new \DomainException(
                    "Split total ({$dto->totalSplitAmount()}) does not match order total ({$order->total})"
                );
            }

            // Vérifier que la commande n'est pas déjà payée
            if ($order->isFullyPaid()) {
                throw new \DomainException('Order is already fully paid');
            }

            $payments = [];
            foreach ($dto->splits as $customerId => $amount) {
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'customer_id' => $customerId,
                    'amount' => $amount,
                    'method' => PaymentMethod::CASH->value, // Par défaut
                    'is_split_payment' => true,
                ]);
                $payments[] = $payment;
            }

            return $payments;
        });
    }
}
