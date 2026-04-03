---
name: tower-power-unit-02-shops
description: >
    Implement the Shop system for the Tower Power Laravel 13 app.
    Use this skill when creating or modifying: Shop model, shop migrations,
    shop helper methods (isOwner, makeOwner, removeOwner, minimumOrder),
    or shop deletion guards.
---

# Unit 2 — Shops System

## Model: Shop

### Table: `shops`

| Column        | Type            | Rules                                              |
| ------------- | --------------- | -------------------------------------------------- |
| id            | bigint unsigned | PK, auto-increment                                 |
| name          | string(120)     | required, unique, min:3, alpha-numeric with spaces |
| owner_id      | bigint unsigned | FK → users.id (no cascade — see deletion rules)    |
| minimum_order | int             | nullable                                           |
| created_at    | timestamp       |                                                    |
| updated_at    | timestamp       |                                                    |

---

## Relationships

```php
owner(): BelongsTo(User::class, 'owner_id')
products(): HasMany(Product::class)
```

---

## Helper Methods

```php
isOwner(User $user): bool
// Returns true if $user->id === $this->owner_id

makeOwner(User $user): void
// Sets owner_id = $user->id and saves
// A shop can only have ONE owner — overwrite any existing owner_id

removeOwner(User $user): void
// Sets owner_id = null and saves
// Only removes if $user->id matches current owner_id

minimumOrder(): ?int
// Returns minimum_order value or null
```

---

## Business Rules

### Deletion Guard

A Shop **cannot** be deleted if ANY of the following are true:

1. It has Products (`products()->exists()`)
2. It has Orders via SubOrders (`subOrders()->exists()` — check via SubOrder model)
3. It has an Owner (`owner_id IS NOT NULL`)

Enforce via a service method or model `deleting` event. Throw an exception
or return false with a descriptive error — do not silently fail.

### Single Owner

A Shop can have at most one Owner (User). `makeOwner()` overwrites — no
pivot table, single FK column.

### FK Cascade Policy

`owner_id` has **no cascade** — deleting a user does not delete their shops.
Handle via service layer: reassign or clear owner before deleting a user.

---

## Authorization Matrix

| Action           | Super User | Staff | Seller | Customer | Delivery Person |
| ---------------- | :--------: | :---: | :----: | :------: | :-------------: |
| View All Shops   |     ✅     |  ✅   |   ❌   |    ❌    |       ❌        |
| View Owned Shops |     -      |   -   |   ✅   |    ❌    |       ❌        |

| Create Shops | ✅ | ❌ | ❌ | ❌ | ❌ |
| Update Shops | ✅ | ❌ | ❌ | ❌ | ❌ |
| Soft Delete Shops | **(c1)** | ❌ | ❌ | ❌ |
| Hard Delete Shops | **(c1)** | ❌ | ❌ | ❌ | ❌ |

**(c1) Hard Delete Shops ** — Only When the shop has no products, no suborders, and no owner.

---

## Key Implementation Rules

1. **Deletion guard** must be enforced in a `ShopService::delete()` method
   (not just in the controller), so it applies everywhere.
2. `minimumOrder()` returns `?int` — null means no minimum applies.
3. The `owner_id` FK intentionally has no `ON DELETE CASCADE` or
   `ON DELETE SET NULL` — deletion of a user while they own a shop must be
   blocked at the service layer.
4. Shops are globally unique by `name` (not scoped to tower or category).
