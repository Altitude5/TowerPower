<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'price',
        'price_type',
        'image_path',
        'sku',
        'shop_id',
        'stock_quantity',
        'stock_weight',
        'stock_volume',
        'category_id',
        'available',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'available' => 'boolean',
            'stock_quantity' => 'decimal:3',
            'stock_weight' => 'decimal:3',
            'stock_volume' => 'decimal:3',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ── Helpers ────────────────────────────────────────────────────

    /**
     * Returns the price type ('Unit', 'Weight', or 'Volume').
     */
    public function priceType(): string
    {
        return $this->price_type;
    }

    /**
     * Computed price unit — NOT stored in DB.
     */
    public function priceUnit(): string
    {
        return match ($this->price_type) {
            'Unit' => 'ILS',
            'Weight' => 'ILS/Kg',
            'Volume' => 'ILS/Litre',
        };
    }

    /**
     * Returns full public URL for the product image, or a placeholder.
     */
    public function imageUrl(): string
    {
        if ($this->image_path) {
            return Storage::disk('public')->url($this->image_path);
        }

        return '/images/placeholder-product.png';
    }

    /**
     * Returns the applicable stock value (quantity, weight, or volume), or null.
     */
    public function stock(): int|float|null
    {
        if ($this->stock_quantity !== null) {
            return (float) $this->stock_quantity;
        }

        if ($this->stock_weight !== null) {
            return (float) $this->stock_weight;
        }

        if ($this->stock_volume !== null) {
            return (float) $this->stock_volume;
        }

        return null;
    }

    /**
     * Returns the SKU or null.
     */
    public function sku(): ?string
    {
        return $this->sku;
    }

    /**
     * Returns whether the product is available.
     */
    public function available(): bool
    {
        return $this->available;
    }
}
