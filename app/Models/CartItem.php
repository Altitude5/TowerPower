<?php

namespace App\Models;

use Database\Factories\CartItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    /** @use HasFactory<CartItemFactory> */
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'weight',
        'volume',
        'price',
        'price_type',
        'product_name',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'quantity' => 'decimal:3',
            'weight' => 'decimal:3',
            'volume' => 'decimal:3',
        ];
    }

    /**
     * Get the cart that the item belongs to.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the product associated with the cart item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the quantity of the item (raw decimal string).
     */
    public function quantity(): ?string
    {
        return $this->quantity;
    }

    /**
     * Get the weight of the item (raw decimal string).
     */
    public function weight(): ?string
    {
        return $this->weight;
    }

    /**
     * Get the volume of the item (raw decimal string).
     */
    public function volume(): ?string
    {
        return $this->volume;
    }

    /**
     * Get the price in the smallest currency unit.
     */
    public function price(): int
    {
        return (int) $this->price;
    }

    /**
     * Calculate the total price for this item.
     */
    public function totalPrice(): int
    {
        $multiplier = '0';

        if ($this->quantity !== null) {
            $multiplier = $this->quantity;
        } elseif ($this->weight !== null) {
            $multiplier = $this->weight;
        } elseif ($this->volume !== null) {
            $multiplier = $this->volume;
        }

        // Using bcmul for precision multiplication of decimal strings
        // price is int, multiplier is decimal(10,3) string
        return (int) bcmul((string) $this->price, $multiplier, 0);
    }

    /**
     * Get the total weight as a string.
     */
    public function totalWeight(): ?string
    {
        return $this->weight;
    }

    /**
     * Get the total discount (Unit 7 implementation pending).
     */
    public function totalDiscount(): int
    {
        return 0;
    }

    /**
     * Get the total tax (system implementation pending).
     */
    public function totalTax(): int
    {
        return 0;
    }

    /**
     * Get the total final price (totalPrice - discount + tax).
     */
    public function totalFinalPrice(): int
    {
        return $this->totalPrice() - $this->totalDiscount() + $this->totalTax();
    }
}
