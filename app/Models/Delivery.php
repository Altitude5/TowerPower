<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'sub_order_id',
        'schedule_id',
        'delivery_person_id',
        'customer_id',
        'tower_id',
        'shop_id',
        'city_id',
        'date',
        'time',
        'status',
        'cancelled_by_user_id',
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'status' => DeliveryStatus::class,
        ];
    }

    public function subOrder(): BelongsTo
    {
        return $this->belongsTo(SubOrder::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function deliveryPerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_person_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function tower(): BelongsTo
    {
        return $this->belongsTo(Tower::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'ratable');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function canTransitionTo(DeliveryStatus $newStatus): bool
    {
        return match ($this->status) {
            DeliveryStatus::Scheduled => in_array($newStatus, [DeliveryStatus::Departed, DeliveryStatus::Cancelled]),
            DeliveryStatus::Departed => in_array($newStatus, [DeliveryStatus::Completed, DeliveryStatus::Failed, DeliveryStatus::Cancelled]),
            DeliveryStatus::Failed => in_array($newStatus, [DeliveryStatus::Scheduled, DeliveryStatus::Cancelled]),
            default => false,
        };
    }
}
