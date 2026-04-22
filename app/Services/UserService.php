<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserService
{
    /**
     * Determine if a user can be hard deleted.
     * Condition (c1): permitted only when ALL of:
     * - The user has already been soft-deleted (deleted_at IS NOT NULL)
     * - The user's id does not appear as a FK in any other table.
     */
    public static function canHardDelete(User $user): bool
    {
        if (! $user->trashed()) {
            return false;
        }

        $constraints = [
            'orders' => ['user_id'],
            'transactions' => ['user_id'],
            'role_user' => ['user_id', 'assigned_by'],
            'tower_user' => ['user_id'],
            'carts' => ['user_id'],
            'shops' => ['owner_id'],
        ];

        foreach ($constraints as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (DB::table($table)->where($column, $user->id)->exists()) {
                    return false;
                }
            }
        }

        return true;
    }
}
