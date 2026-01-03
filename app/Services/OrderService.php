<?php

namespace App\Services;

use App\Actions\Orders\CancelOrderAction;
use App\Actions\Orders\CreateOrderAction;
use App\Actions\Orders\SplitBillAction;
use App\Actions\Orders\UpdateOrderStatusAction;
use App\DTOs\CreateOrderDTO;
use App\DTOs\SplitBillDTO;
use App\Enums\OrderStatus;
use App\Models\Order;

class OrderService
{
    public function __construct(
        private readonly CreateOrderAction $createOrderAction,
        private readonly UpdateOrderStatusAction $updateStatusAction,
        private readonly CancelOrderAction $cancelOrderAction,
        private readonly SplitBillAction $splitBillAction,
    ) {}

    public function createOrder(CreateOrderDTO $dto): Order
    {
        return $this->createOrderAction->execute($dto);
    }

    public function updateStatus(Order $order, OrderStatus $status): Order
    {
        return $this->updateStatusAction->execute($order, $status);
    }

    public function cancelOrder(Order $order, string $reason): Order
    {
        return $this->cancelOrderAction->execute($order, $reason);
    }

    public function splitBill(SplitBillDTO $dto): array
    {
        return $this->splitBillAction->execute($dto);
    }

    public function getOrdersByStatus(OrderStatus $status)
    {
        return Order::with(['items.product', 'customer', 'user'])
            ->where('status', $status->value)
            ->latest()
            ->get();
    }

    public function getTodayOrders()
    {
        return Order::with(['items.product', 'customer'])
            ->whereDate('created_at', today())
            ->latest()
            ->get();
    }

    public function getOrderStatistics(\DateTime $startDate, \DateTime $endDate): array
    {
        $orders = Order::whereBetween('created_at', [$startDate, $endDate])->get();

        return [
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('total'),
            'average_order_value' => $orders->avg('total'),
            'orders_by_status' => $orders->groupBy('status')->map->count(),
        ];
    }
}
