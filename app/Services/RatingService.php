<?php

namespace App\Services;

use App\Models\Rating;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class RatingService
{
    /**
     * Update or create a rating for a ratable item.
     */
    public static function updateOrCreateRating(User $user, Model $ratable, int $score): Rating
    {
        return Rating::updateOrCreate(
            [
                'user_id' => $user->id,
                'ratable_type' => $ratable->getMorphClass(),
                'ratable_id' => $ratable->id,
            ],
            [
                'score' => $score,
            ]
        );
    }
}
