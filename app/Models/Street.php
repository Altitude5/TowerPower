<?php

namespace App\Models;

use Database\Factories\StreetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Street extends Model
{
    /** @use HasFactory<StreetFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'city_id',
    ];

    /**
     * Get the city that the street belongs to.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the towers on this street.
     */
    public function towers(): HasMany
    {
        return $this->hasMany(Tower::class);
    }
}
