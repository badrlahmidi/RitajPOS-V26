<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'category_id',
        'price',
        'cost',
        'stock',
        'track_stock',
        'barcode',
        'image',
        'is_active',
        'variants',
        'supplements',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'stock' => 'integer',
        'track_stock' => 'boolean',
        'is_active' => 'boolean',
        'variants' => 'array',
        'supplements' => 'array',
    ];

    // Relationships

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_id', 'id');
    }

    // Accessors

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2) . ' DH';
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return asset('images/placeholder-product.png');
        }

        return asset('storage/' . $this->image);
    }

    public function getProfitMarginAttribute(): float
    {
        if ($this->cost <= 0) {
            return 0;
        }

        return (($this->price - $this->cost) / $this->cost) * 100;
    }

    public function isLowStock(): bool
    {
        if (!$this->track_stock) {
            return false;
        }

        return $this->stock <= 10; // Seuil configurable
    }

    public function isOutOfStock(): bool
    {
        if (!$this->track_stock) {
            return false;
        }

        return $this->stock <= 0;
    }

    // Query Scopes

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('track_stock', false)
              ->orWhere('stock', '>', 0);
        });
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->where('track_stock', true)
            ->where('stock', '<=', 10)
            ->where('stock', '>', 0);
    }

    public function scopeByCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('barcode', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }
}
