<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tower_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tower(): BelongsTo
    {
        return $this->belongsTo(Tower::class);
    }

    public function subOrders(): HasMany
    {
        return $this->hasMany(SubOrder::class);
    }

    public function orderItems(): HasManyThrough
    {
        return $this->hasManyThrough(OrderItem::class, SubOrder::class);
    }

    public function canTransitionTo(OrderStatus $newStatus): bool
    {
        return match ($this->status) {
            OrderStatus::Pending => in_array($newStatus, [OrderStatus::Processing, OrderStatus::Cancelled]),
            OrderStatus::Processing => in_array($newStatus, [OrderStatus::Completed, OrderStatus::Cancelled]),
            default => false,
        };
    }

    public function totalPrice(): int
    {
        return $this->subOrders->sum(fn (SubOrder $so) => $so->totalPrice());
    }

    public function totalQuantity(): int
    {
        return $this->subOrders->sum(fn (SubOrder $so) => $so->totalQuantity());
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
