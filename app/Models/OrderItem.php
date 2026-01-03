<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
        'variants',
        'supplements',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'variants' => 'array',
        'supplements' => 'array',
    ];

    public $timestamps = false;

    // Relationships

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    // Accessors

    public function getFormattedSubtotalAttribute(): string
    {
        return number_format($this->subtotal, 2) . ' DH';
    }

    public function getVariantsTextAttribute(): ?string
    {
        if (!$this->variants) {
            return null;
        }

        return collect($this->variants)
            ->pluck('name')
            ->implode(', ');
    }

    public function getSupplementsTextAttribute(): ?string
    {
        if (!$this->supplements) {
            return null;
        }

        return collect($this->supplements)
            ->pluck('name')
            ->implode(', ');
    }

    public function getTotalSupplementsPriceAttribute(): float
    {
        if (!$this->supplements) {
            return 0;
        }

        return (float) collect($this->supplements)->sum('price');
    }

    // Boot Method

    protected static function booted(): void
    {
        static::creating(function (OrderItem $item) {
            // Auto-calculer le subtotal si non fourni
            if (!$item->subtotal) {
                $supplementsPrice = $item->getTotalSupplementsPriceAttribute();
                $item->subtotal = ($item->unit_price + $supplementsPrice) * $item->quantity;
            }
        });
    }
}
