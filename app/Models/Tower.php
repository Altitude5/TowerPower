<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tower extends Model
{
    /** @use HasFactory<\Database\Factories\TowerFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'street_id',
        'house_number',
        'zipcode',
        'city_id',
        'state',
        'country',
        'latitude',
        'longitude',
    ];

    /**
     * Get the city that the tower belongs to.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the street that the tower is on.
     */
    public function street(): BelongsTo
    {
        return $this->belongsTo(Street::class);
    }

    /**
     * Get the users who live in the tower.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(TowerUser::class)
            ->withPivot(['apartment_number', 'floor'])
            ->withTimestamps();
    }

    /**
     * Get the orders associated with the tower.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the full address of the tower.
     */
    public function fullAddress(): string
    {
        $streetName = $this->street->name ?? 'Unknown Street';
        $cityName = $this->city->name ?? 'Unknown City';
        
        return "{$streetName} {$this->house_number}, {$cityName} {$this->zipcode}";
    }
}
