<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case CARD = 'card';
    case MOBILE = 'mobile';
    case CREDIT = 'credit';

    public function label(): string
    {
        return match($this) {
            self::CASH => 'Espèces',
            self::CARD => 'Carte bancaire',
            self::MOBILE => 'Paiement mobile',
            self::CREDIT => 'À crédit',
        };
    }
}
