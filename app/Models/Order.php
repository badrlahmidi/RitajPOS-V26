<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'user_id',
        'table_number',
        'status',
        'subtotal',
        'tax',
        'total',
        'notes',
        'cancellation_reason',
        'cancelled_at',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'cancelled_at' => 'datetime',
    ];

    protected $with = ['items', 'customer'];

    // Relationships

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'order_id', 'id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // Accessors & Mutators

    public function getCustomerNameAttribute(): string
    {
        return $this->customer
            ? "{$this->customer->first_name} {$this->customer->last_name}"
            : __('Walk-in Customer');
    }

    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total, 2) . ' DH';
    }

    // Business Logic Methods

    public function receivedAmount(): float
    {
        if ($this->relationLoaded('payments')) {
            return (float) $this->payments->sum('amount');
        }
        return (float) $this->payments()->sum('amount');
    }

    public function remainingBalance(): float
    {
        return max(0, $this->total - $this->receivedAmount());
    }

    public function isFullyPaid(): bool
    {
        return $this->receivedAmount() >= $this->total;
    }

    public function canBeCancelled(): bool
    {
        return !in_array($this->status, [OrderStatus::PAID, OrderStatus::CANCELLED]);
    }

    public function isPending(): bool
    {
        return $this->status === OrderStatus::PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === OrderStatus::PAID;
    }

    // Query Scopes

    public function scopeByCustomer(Builder $query, int $customerId): Builder
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByStatus(Builder $query, OrderStatus $status): Builder
    {
        return $query->where('status', $status->value);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeDateRange(Builder $query, Carbon $startDate, Carbon $endDate): Builder
    {
        return $query->whereBetween('created_at', [
            $startDate->startOfDay(),
            $endDate->endOfDay()
        ]);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::PENDING->value);
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->whereIn('status', [
            OrderStatus::ORDERED->value,
            OrderStatus::PREPARING->value,
            OrderStatus::READY->value,
        ]);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::PAID->value);
    }

    public function scopeByTable(Builder $query, int $tableNumber): Builder
    {
        return $query->where('table_number', $tableNumber);
    }

    // Boot Method

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (!$order->user_id) {
                $order->user_id = auth()->id();
            }
        });
    }
}
