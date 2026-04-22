<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Category $category) {
            if (empty($category->slug)) {
                $category->slug = static::generateUniqueSlug($category->name);
            }
        });

        static::deleting(function (Category $category) {
            if ($category->products()->exists()) {
                throw new \Exception('Cannot delete category because it has products.');
            }

            if ($category->categoryCityAssignments()->exists()) {
                throw new \Exception('Cannot delete category because it has city-shop assignments.');
            }
        });
    }

    /**
     * Generate a unique slug for the category.
     */
    public static function generateUniqueSlug(string $name): string
    {
        $slug = Str::snake($name);
        $original = $slug;
        $i = 2;
        while (static::where('slug', $slug)->exists()) {
            $slug = $original.'_'.$i++;
        }

        return $slug;
    }

    /**
     * Get the products in the category.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get all city-shop slots assigned to this category.
     */
    public function categoryCityAssignments(): HasMany
    {
        return $this->hasMany(CategoryCityAssignment::class);
    }

    /**
     * Get all shops that serve this category in any city.
     */
    public function assignedShops(): HasManyThrough
    {
        return $this->hasManyThrough(Shop::class, CategoryCityAssignment::class, 'category_id', 'id', 'id', 'shop_id');
    }

    /**
     * Returns the Shop assigned to this category in the given city, or null.
     */
    public function shopInCity(City|int $city): ?Shop
    {
        $cityId = $city instanceof City ? $city->id : $city;

        return $this->categoryCityAssignments()
            ->where('city_id', $cityId)
            ->with('shop')
            ->first()
            ?->shop;
    }

    /**
     * Returns true if a shop is assigned to this category in the given city.
     */
    public function hasShopInCity(City|int $city): bool
    {
        $cityId = $city instanceof City ? $city->id : $city;

        return $this->categoryCityAssignments()
            ->where('city_id', $cityId)
            ->exists();
    }
}
