<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ratable_type',
        'ratable_id',
        'score',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
        ];
    }

    /**
     * Get the rated model.
     */
    public function ratable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who submitted the rating.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
