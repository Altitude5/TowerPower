<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    /** @use HasFactory<\Database\Factories\CityFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
    ];

    /**
     * Get the towers in the city.
     */
    public function towers(): HasMany
    {
        return $this->hasMany(Tower::class);
    }

    /**
     * Get the streets in the city.
     */
    public function streets(): HasMany
    {
        return $this->hasMany(Street::class);
    }
}
