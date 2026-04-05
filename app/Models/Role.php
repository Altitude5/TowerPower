<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Role extends Model
{
    use HasFactory;

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saving(function (Role $role) {
            // Validate name: min 3, alpha-numeric with spaces
            if (strlen($role->name) < 3) {
                throw new \InvalidArgumentException('Role name must be at least 3 characters.');
            }

            if (! preg_match('/^[a-zA-Z0-9\s]+$/', $role->name)) {
                throw new \InvalidArgumentException('Role name can only contain alpha-numeric characters and spaces.');
            }

            // Auto-generate slug if missing
            if (empty($role->slug) && ! empty($role->name)) {
                $role->slug = Str::slug($role->name, '_');
            }

            // Validate slug: alpha-numeric with underscores
            if (! preg_match('/^[a-z0-9_]+$/', $role->slug)) {
                throw new \InvalidArgumentException('Role slug can only contain lowercase alpha-numeric characters and underscores.');
            }
        });
    }

    public const ROLE_SUPER_USER = 'super_user';

    public const ROLE_STAFF = 'staff';

    public const ROLE_SELLER = 'seller';

    public const ROLE_CUSTOMER = 'customer';

    public const ROLE_DELIVERY_PERSON = 'delivery_person';

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * The roles that belong to the user.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(RoleUser::class)
            ->withPivot(['id', 'assigned_by', 'expires_at', 'is_active'])
            ->withTimestamps();
    }

    /**
     * Assign this role to a user.
     */
    public function assignToUser(User $user, ?User $assignedBy = null): void
    {
        $user->assignRole($this, $assignedBy);
    }

    /**
     * Remove this role from a user.
     */
    public function removeFromUser(User $user): void
    {
        $user->removeRole($this);
    }

    /**
     * Check if the role has any users.
     */
    public function hasUsers(): bool
    {
        return $this->users()->wherePivot('is_active', true)->exists();
    }
}
