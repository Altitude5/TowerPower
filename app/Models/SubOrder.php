<?php

namespace App\Models;

use App\Enums\SubOrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SubOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'shop_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubOrderStatus::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }

    public function canTransitionTo(SubOrderStatus $newStatus): bool
    {
        return match ($this->status) {
            SubOrderStatus::Pending => in_array($newStatus, [SubOrderStatus::Processing, SubOrderStatus::Cancelled]),
            SubOrderStatus::Processing => in_array($newStatus, [SubOrderStatus::OutForDelivery, SubOrderStatus::Cancelled]),
            SubOrderStatus::OutForDelivery => in_array($newStatus, [SubOrderStatus::Delivered, SubOrderStatus::Returned, SubOrderStatus::Cancelled]),
            SubOrderStatus::Delivered => in_array($newStatus, [SubOrderStatus::Completed, SubOrderStatus::Returned]),
            default => false,
        };
    }

    public function totalPrice(): int
    {
        return $this->orderItems->sum(fn (OrderItem $oi) => $oi->totalPrice());
    }

    public function totalQuantity(): int
    {
        return $this->orderItems->sum(fn (OrderItem $oi) => (int) ($oi->quantity ?? 0));
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
