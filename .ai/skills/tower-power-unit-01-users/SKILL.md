---
name: tower-power-unit-01-users
description: >
  Implement the User & Role system for the Tower Power Laravel 13 app.
  Use this skill when creating or modifying: User model, Role model, RoleUser
  pivot, RoleSeeder, DevUserSeeder, the CreateSuperUser artisan command, any
  role-based helper methods (hasRole, isSuperUser, isAdmin, assignRole, etc.),
  or any User/Role policy or authorization logic.
---


# Unit 1 — User & Role System



## Stack

- Laravel 13, Sanctum auth, Vue/Inertia 3, Filament v5 (admin panel)
- Money: smallest currency unit (8000 = ₪80.00)

---


## Canonical Migration Order (full app reference)

```
roles
users
role_user
cities          (Unit 13)
streets         (Unit 13)
towers          (Unit 8)
tower_user      (Unit 8)
categories      (Unit 11)
shops           (Unit 2)
products        (Unit 3)
carts           (Unit 4)
cart_items      (Unit 4)
orders          (Unit 5)
sub_orders      (Unit 5)
order_items     (Unit 5)
transactions    (Unit 6)
```

---


## Model: Role



### Table: `roles`

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned | PK, auto-increment |
| name | string(50) | required, unique, min:3, alpha-numeric with spaces |
| slug | string | required, unique, alpha-numeric with underscores |
| created_at | timestamp | |
| updated_at | timestamp | |


### Constants

```php
const ROLE_SUPER_USER      = 'super_user';
const ROLE_STAFF           = 'staff';
const ROLE_SELLER          = 'seller';
const ROLE_CUSTOMER        = 'customer';
const ROLE_DELIVERY_PERSON = 'delivery_person';
```
> Constants hold **slug** values. All role checks use slug, never name.


### Seeded Roles — `RoleSeeder` (all environments)

| name | slug |
|------|------|
| Super User | super_user |
| Staff | staff |
| Seller | seller |
| Customer | customer |
| Delivery Person | delivery_person |

Use `firstOrCreate(['slug' => ...], ['name' => ...])` to make the seeder
idempotent.


### Relationships

```php
users(): BelongsToMany(User::class, 'role_user', 'role_id', 'user_id')
         ->using(RoleUser::class)
         ->withPivot(['assigned_by', 'expires_at', 'is_active'])
         ->withTimestamps()
```


### Helper Methods

```php
assignToUser(User $user, ?User $assignedBy = null): void
// Attaches role to user via RoleUser pivot.
// Sets assigned_by = $assignedBy->id if provided, null if system-assigned.

removeFromUser(User $user): void
// Detaches role from user.

hasUsers(): bool
// Returns true if ANY active (is_active = true) RoleUser record exists
// for this role. Used for the role deletion guard.
```

---


## Model: User



### Table: `users`

Laravel default columns plus `deleted_at` for SoftDeletes.

```php
// Add to users migration:
$table->softDeletes();
```


### Filament Panel Access

Implement `canAccessPanel()` on the User model for Filament v5:
```php
public function canAccessPanel(\Filament\Panel $panel): bool
{
    return $this->isAdmin(); // SuperUser or Staff
}
```


### Relationships

```php
roles(): BelongsToMany(Role::class, 'role_user', 'user_id', 'role_id')
         ->using(RoleUser::class)
         ->withPivot(['assigned_by', 'expires_at', 'is_active'])
         ->withTimestamps()
```


### Helper Methods


```php
hasRole(string $slug): bool
// Checks active (is_active = true) role assignments by slug.
// Example: $user->hasRole(Role::ROLE_STAFF)

hasAnyRole(array $slugs): bool
// Returns true if user has ANY of the given slugs with is_active = true.

isSuperUser(): bool      // hasRole(Role::ROLE_SUPER_USER)
isStaff(): bool          // hasRole(Role::ROLE_STAFF)
isAdmin(): bool          // isSuperUser() || isStaff()
isSeller(): bool         // hasRole(Role::ROLE_SELLER)
isCustomer(): bool       // hasRole(Role::ROLE_CUSTOMER)
isDeliveryPerson(): bool // hasRole(Role::ROLE_DELIVERY_PERSON)

assignRole(Role|string $role, ?User $assignedBy = null): void
// Accepts Role instance or slug string.
// Resolves to Role model, then calls $role->assignToUser($this, $assignedBy).
// No-op if role already assigned and active.

removeRole(Role|string $role): void
// Accepts Role instance or slug string.
// Calls $role->removeFromUser($this).

syncRoles(array $roles): void
// Accepts array of Role instances or slug strings.
// Detaches all current roles, attaches the new set.
```

> All role checks MUST filter by `is_active = true` on the pivot.
> An inactive assignment (`is_active = false`) is treated as no assignment.

---


## Model: RoleUser (Pivot)



### Table: `role_user`

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned | PK, auto-increment |
| role_id | bigint unsigned | FK → roles.id **ON DELETE RESTRICT** |
| user_id | bigint unsigned | FK → users.id **ON DELETE RESTRICT** |
| assigned_by | bigint unsigned | **nullable**, FK → users.id |
| expires_at | timestamp | nullable |
| is_active | boolean | default true |
| created_at | timestamp | |
| updated_at | timestamp | |


### Indexes

```
UNIQUE (role_id, user_id)
INDEX  (user_id)
INDEX  (role_id)
```


### Implementation Notes

- Extend `Illuminate\Database\Eloquent\Relations\Pivot`
- `assigned_by` is **nullable** — null = system-assigned (seeder, artisan
  command). Never enforce non-null at DB or application level.
- `ON DELETE RESTRICT` on both FKs — a user with any role_user rows cannot
  be deleted at DB level. Service layer must detach all roles first.
- `is_active = false` suspends a role without deleting the audit record.
- `expires_at` is scaffolded for future temporary-role logic. No expiry
  enforcement required in this unit.

---


## Seeders



### RoleSeeder (all environments)

```php
public function run(): void
{
    $roles = [
        ['name' => 'Super User',      'slug' => 'super_user'],
        ['name' => 'Staff',           'slug' => 'staff'],
        ['name' => 'Seller',          'slug' => 'seller'],
        ['name' => 'Customer',        'slug' => 'customer'],
        ['name' => 'Delivery Person', 'slug' => 'delivery_person'],
    ];

    foreach ($roles as $role) {
        Role::firstOrCreate(['slug' => $role['slug']], ['name' => $role['name']]);
    }
}
```


### DevUserSeeder (local environment only)

```php
public function run(): void
{
    if (!app()->environment('local')) return;

    $users = [
        ['email' => 'super@example.com',    'role' => Role::ROLE_SUPER_USER],
        ['email' => 'staff@example.com',    'role' => Role::ROLE_STAFF],
        ['email' => 'seller@example.com',   'role' => Role::ROLE_SELLER],
        ['email' => 'customer@example.com', 'role' => Role::ROLE_CUSTOMER],
        ['email' => 'delivery@example.com', 'role' => Role::ROLE_DELIVERY_PERSON],
        ['email' => 'user@example.com',     'role' => null],
    ];

    foreach ($users as $data) {
        $user = User::firstOrCreate(
            ['email' => $data['email']],
            [
                'name'     => ucfirst(explode('@', $data['email'])[0]),
                'password' => bcrypt('password'),
            ]
        );

        if ($data['role']) {
            $user->assignRole($data['role']); // assigned_by = null (system)
        }
    }
}
```

---


## Artisan Command: `user:create-super-user`


```
php artisan user:create-super-user
```

Behaviour:
1. Prompt for email — validate: required, valid format, unique in `users` table
2. Prompt for password — hidden input, min 8 chars, with confirmation prompt
3. Create `User` record
4. Assign `super_user` role (`assigned_by = null` — system assignment)
5. Output: `"Super User {email} created successfully."`

For **production** use only. DevUserSeeder handles local environment.

---


## Authorization Matrix


| Action | Super User | Staff | Seller | Customer | Delivery Person |
|--------|:----------:|:-----:|:------:|:--------:|:---------------:|
| View all users | ✅ | ❌ | ❌ | ❌ | ❌ |
| View Super Users | ✅ | ❌ | ❌ | ❌ | ❌ |
| View Staff | ✅ | ❌ | ❌ | ❌ | ❌ |
| View Sellers | ✅ | ✅ | ❌ | ❌ | ❌ |
| View Customers | ✅ | ✅ | ❌ | ❌ | ❌ |
| View Delivery Persons | ✅ | ✅ | ❌ | ❌ | ❌ |
| Create Users | ✅ | ❌ | ❌ | ❌ | ❌ |
| Update Users | ✅ | ❌ | ❌ | ❌ | ❌ |
| Soft Delete Users | ✅ | ❌ | ❌ | ❌ | ❌ |
| Hard Delete Users | ✅ **(c1)** | ❌ | ❌ | ❌ | ❌ |
| View Roles | ✅ | ✅ | ❌ | ❌ | ❌ |
| Create Roles | ✅ | ❌ | ❌ | ❌ | ❌ |
| Update Roles | ✅ | ❌ | ❌ | ❌ | ❌ |
| Delete Roles | ✅ **(c2)** | ❌ | ❌ | ❌ | ❌ |


### Conditions

**(c1) Hard Delete Users** — permitted only when ALL of:
- The user has already been soft-deleted (`deleted_at IS NOT NULL`)
- The user's `id` does not appear as a FK in any other table:
  `orders.user_id`, `transactions.user_id`, `role_user.user_id`,
  `role_user.assigned_by`, `tower_user.user_id`, `carts.user_id`,
  `shops.owner_id`

Implement as `UserService::canHardDelete(User $user): bool`.

**(c2) Delete Roles** — permitted only when `$role->hasUsers() === false`
(zero active assignments). Enforce via `RolePolicy::delete()`.

---


## Policies



### UserPolicy

```php
viewAny(User $actor): bool
// SuperUser: true (sees everyone)
// Staff: true (but query is filtered — see Staff filter below)
// Others: false

view(User $actor, User $target): bool
// SuperUser: always true
// Staff: true only if target has Seller, Customer, or Delivery Person role
//        AND does NOT have Staff or SuperUser role

create(User $actor): bool        // $actor->isSuperUser()
update(User $actor): bool        // $actor->isSuperUser()

delete(User $actor, User $target): bool
// $actor->isSuperUser() — soft delete only

forceDelete(User $actor, User $target): bool
// $actor->isSuperUser() && UserService::canHardDelete($target)
```


### RolePolicy

```php
viewAny(User $actor): bool   // $actor->isAdmin()
view(User $actor): bool      // $actor->isAdmin()
create(User $actor): bool    // $actor->isSuperUser()
update(User $actor): bool    // $actor->isSuperUser()
delete(User $actor, Role $role): bool
// $actor->isSuperUser() && !$role->hasUsers()
```

---


## Staff Query Filter


When Staff accesses user lists, apply at the Eloquent query level
(not just in the view). Used in Filament `getEloquentQuery()` override
and any API/controller that returns user lists:

```php
if (auth()->user()->isStaff() && !auth()->user()->isSuperUser()) {
    $query
        ->whereHas('roles', fn($q) =>
            $q->whereIn('slug', [
                Role::ROLE_SELLER,
                Role::ROLE_CUSTOMER,
                Role::ROLE_DELIVERY_PERSON,
            ])->where('is_active', true)
        )
        ->whereDoesntHave('roles', fn($q) =>
            $q->whereIn('slug', [
                Role::ROLE_SUPER_USER,
                Role::ROLE_STAFF,
            ])->where('is_active', true)
        );
}
```

---


## Filament v5 Integration


- Implement `canAccessPanel(\Filament\Panel $panel): bool` on User model
  returning `$this->isAdmin()`
- Register `UserResource` and `RoleResource` in the panel
- Apply the Staff query filter in `UserResource::getEloquentQuery()`
- Hide Create/Edit/Delete actions from Staff using Filament's
  `visible(fn() => auth()->user()->isSuperUser())` on action definitions
- Use `->authorizeUsing(fn() => ...)` or register Policies with Filament's
  policy discovery for consistent authorization

---


## Key Implementation Rules


1. **Slug is the authority** — all `hasRole()` checks and constants use slug.
   Never compare against the `name` field in code.
2. **is_active filter** — every role check must scope to `is_active = true`
   on the pivot. An inactive row = no role for all purposes.
3. **assigned_by is nullable** — system assignments leave `assigned_by = null`.
   Never enforce non-null at DB or application level.
4. **RESTRICT deletes** — `role_user` FKs use `ON DELETE RESTRICT`. A user
   with any role_user rows (active or inactive) cannot be deleted at DB level.
   Service layer must detach all roles before hard-deleting a user.
5. **SoftDeletes on User** — `delete()` = soft delete. `forceDelete()` =
   hard delete, gated by `UserService::canHardDelete()`.
6. **Filament canAccessPanel()** — must be implemented on User model or
   Filament v5 will block all admin access. Gate: `isAdmin()`.
7. **Staff query filter is query-level** — never filter only in the UI.
   Staff must never receive Super User or Staff records via any endpoint.