<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Shop extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'owner_id',
        'minimum_order',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::deleting(function (Shop $shop) {
            $shop->ratings()->delete();

            if ($shop->products()->exists()) {
                throw new \Exception('Cannot delete shop because it has products.');
            }

            if ($shop->owner_id !== null) {
                throw new \Exception('Cannot delete shop because it has an owner.');
            }

            if ($shop->categoryCityAssignments()->exists()) {
                throw new \Exception('Cannot delete shop because it has category-city assignments.');
            }

            // SubOrder check (Unit 5 integration)
            if (class_exists(SubOrder::class) && method_exists($shop, 'subOrders') && $shop->subOrders()->exists()) {
                throw new \Exception('Cannot delete shop because it has sub-orders.');
            }
        });
    }

    /**
     * Get the owner of the shop.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the products for the shop.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the category city assignments for the shop.
     */
    public function categoryCityAssignments(): HasMany
    {
        return $this->hasMany(CategoryCityAssignment::class);
    }

    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'ratable');
    }

    /**
     * Get all CategoryCityAssignment records for this shop, eager-loading category and city.
     */
    public function assignedCategories(): Collection
    {
        return $this->categoryCityAssignments()->with('category', 'city')->get();
    }

    /**
     * Check if the given user is the owner of the shop.
     */
    public function isOwner(User $user): bool
    {
        return $this->owner_id !== null && $this->owner_id === $user->id;
    }

    /**
     * Assign a user as the owner of the shop.
     * Overwrites any existing owner.
     */
    public function makeOwner(User $user): void
    {
        $this->owner_id = $user->id;
        $this->save();
    }

    /**
     * Remove the given user from being the owner.
     * Only removes if the given user is currently the owner.
     */
    public function removeOwner(User $user): void
    {
        if ($this->isOwner($user)) {
            $this->owner_id = null;
            $this->save();
        }
    }

    /**
     * Get the minimum order amount for the shop.
     */
    public function minimumOrder(): ?int
    {
        return $this->minimum_order;
    }

    public function averageScore(): ?float
    {
        return $this->ratings()->avg('score');
    }

    public function userRating(User $user): ?Rating
    {
        return $this->ratings()->where('user_id', $user->id)->first();
    }
}
