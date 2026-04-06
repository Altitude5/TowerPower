<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sub_order_id',
        'product_id',
        'product_name',
        'price',
        'price_type',
        'quantity',
        'weight',
        'volume',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'quantity' => 'decimal:3',
            'weight' => 'decimal:3',
            'volume' => 'decimal:3',
        ];
    }

    public function subOrder(): BelongsTo
    {
        return $this->belongsTo(SubOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function quantity(): ?string
    {
        return $this->quantity;
    }

    public function weight(): ?string
    {
        return $this->weight;
    }

    public function volume(): ?string
    {
        return $this->volume;
    }

    public function price(): int
    {
        return (int) $this->price;
    }

    public function totalPrice(): int
    {
        $multiplier = "0";

        if ($this->quantity !== null) {
            $multiplier = (string) $this->quantity;
        } elseif ($this->weight !== null) {
            $multiplier = (string) $this->weight;
        } elseif ($this->volume !== null) {
            $multiplier = (string) $this->volume;
        }

        return (int) bcmul((string) $this->price, $multiplier, 0);
    }

    public function totalWeight(): string
    {
        return (string) ($this->weight ?? '0.000');
    }

    public function totalVolume(): string
    {
        return (string) ($this->volume ?? '0.000');
    }

    public function totalDiscount(): int
    {
        return 0;
    }

    public function totalTax(): int
    {
        return 0;
    }

    public function totalFinalPrice(): int
    {
        return $this->totalPrice() - $this->totalDiscount() + $this->totalTax();
    }
}
