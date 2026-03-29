<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
    /**
     * The roles that belong to the user.
     */
    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id')
                    ->using(RoleUser::class)
                    ->withPivot(['assigned_by', 'expires_at', 'is_active'])
                    ->withTimestamps();
    }

    public function hasRole(string $slug): bool
    {
        return $this->roles()->where('slug', $slug)->wherePivot('is_active', true)->exists();
    }

    public function hasAnyRole(array $slugs): bool
    {
        return $this->roles()
            ->whereIn('slug', $slugs)
            ->wherePivot('is_active', true)
            ->exists();
    }

    public function isSuperUser(): bool
    {
        return $this->hasRole(Role::ROLE_SUPER_USER);
    }

    public function isStaff(): bool
    {
        return $this->hasRole(Role::ROLE_STAFF);
    }

    public function isAdmin(): bool
    {
        return $this->isSuperUser() || $this->isStaff();
    }

    public function assignRole(Role|string $role, ?User $assignedBy = null): void
    {
        $roleModel = $role instanceof Role ? $role : Role::where('slug', $role)->firstOrFail();
        
        if (!$this->hasRole($roleModel->slug)) {
            $this->roles()->attach($roleModel->id, [
                'assigned_by' => $assignedBy?->id,
                'is_active' => true,
            ]);
        }
    }

    public function removeRole(Role|string $role): void
    {
        $roleModel = $role instanceof Role ? $role : Role::where('slug', $role)->firstOrFail();
        $this->roles()->detach($roleModel->id);
    }

    public function syncRoles(array $roles): void
    {
        $roleIds = collect($roles)->map(function ($role) {
            return $role instanceof Role ? $role->id : Role::where('slug', $role)->firstOrFail()->id;
        });

        $this->roles()->syncWithPivotValues($roleIds, ['is_active' => true]);
    }

    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return $this->isAdmin();
    }
}
