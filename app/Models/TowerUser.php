<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TowerUser extends Pivot
{
    use HasFactory;

    protected $table = 'tower_user';

    protected $fillable = [
        'tower_id',
        'user_id',
        'apartment_number',
        'floor',
    ];
}
