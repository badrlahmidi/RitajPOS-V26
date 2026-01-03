<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case ORDERED = 'ordered';
    case PREPARING = 'preparing';
    case READY = 'ready';
    case SERVED = 'served';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'En attente',
            self::ORDERED => 'Commandé',
            self::PREPARING => 'En préparation',
            self::READY => 'Prêt',
            self::SERVED => 'Servi',
            self::PAID => 'Payé',
            self::CANCELLED => 'Annulé',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::ORDERED => 'info',
            self::PREPARING => 'primary',
            self::READY => 'success',
            self::SERVED => 'success',
            self::PAID => 'success',
            self::CANCELLED => 'danger',
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return match($this) {
            self::PENDING => in_array($status, [self::ORDERED, self::CANCELLED]),
            self::ORDERED => in_array($status, [self::PREPARING, self::CANCELLED]),
            self::PREPARING => in_array($status, [self::READY, self::CANCELLED]),
            self::READY => in_array($status, [self::SERVED, self::CANCELLED]),
            self::SERVED => in_array($status, [self::PAID]),
            self::PAID, self::CANCELLED => false,
        };
    }
}
