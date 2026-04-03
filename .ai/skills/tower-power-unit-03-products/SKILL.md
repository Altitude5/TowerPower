---
name: tower-power-unit-03-products
description: >
    Implement the Product system for the Tower Power Laravel 13 app.
    Use this skill when creating or modifying: Product model, product migrations,
    image storage, price/unit validation, stock fields, SoftDeletes, hard-delete
    guard, or product helper methods.
---

# Unit 3 — Product System

## Model: Product

### Table: `products`

| Column         | Type            | Rules                                                             |
| -------------- | --------------- | ----------------------------------------------------------------- |
| id             | bigint unsigned | PK, auto-increment                                                |
| name           | string(120)     | required, min:3, unique(shop_id, name), alpha-numeric with spaces |
| price          | int             | required, min:0 (stored in smallest unit, e.g. agorot)            |
| price_type     | string          | required, enum: ['Unit', 'Weight', 'Volume']                      |
| image_path     | string          | nullable, formats: jpg/gif/png, max 5MB                           |
| sku            | string          | nullable, unique when present, pattern: `^[A-Z0-9-]{8,12}$`       |
| shop_id        | bigint unsigned | FK → shops.id (no cascade)                                        |
| stock_quantity | decimal(10,3)   | nullable, prohibited_with: stock_weight, stock_volume             |
| stock_weight   | decimal(10,3)   | nullable, prohibited_with: stock_quantity, stock_volume           |
| stock_volume   | decimal(10,3)   | nullable, prohibited_with: stock_quantity, stock_weight           |
| category_id    | bigint unsigned | nullable, FK → categories.id                                      |
| available      | boolean         | default true                                                      |
| created_at     | timestamp       |                                                                   |
| updated_at     | timestamp       |                                                                   |
| deleted_at     | timestamp       | nullable (SoftDeletes)                                            |

### Indexes

```
INDEX (shop_id)
INDEX (sku)
INDEX (available)
```

---

## Image Storage

- **Stored at**: `storage/app/public/products/`
- **Public URL**: `/storage/products/{filename}`
- Use Laravel's `Storage::disk('public')` for all operations
- `image_path` column stores relative path e.g. `products/salmon.jpg`

---

## Relationships

```php
shop(): BelongsTo(Shop::class)
category(): BelongsTo(Category::class)   // nullable
orderItems(): HasMany(OrderItem::class)
```

---

## Helper Methods

```php
priceType(): string
// Returns $this->price_type ('Unit' | 'Weight' | 'Volume')

priceUnit(): string
// Computed — NOT stored in DB:
public function priceUnit(): string
{
    return match($this->price_type) {
        'Unit'   => 'ILS',
        'Weight' => 'ILS/Kg',
        'Volume' => 'ILS/Litre',
    };
}

imageUrl(): string
// Returns full public URL or placeholder if image_path is null
// e.g. Storage::url($this->image_path)

stock(): int|float|null
// Returns stock_quantity if set, stock_weight if set, stock_volume if set, or null
// (only one can be set at a time due to prohibited_with rule)

sku(): string|null
// Returns $this->sku

available(): bool
// Returns $this->available
```

---

## Validation Rules

### price_type / price_unit coupling

```
price_type = 'Unit'   → price_unit must be 'ILS'
price_type = 'Weight' → price_unit must be 'ILS/Kg'
price_type = 'Volume' → price_unit must be 'ILS/Litre'
```

`price_unit` is **not stored** — it is derived via `priceUnit()`.
Validate `price_type` is one of the allowed values on input.

### Stock fields

```
stock_quantity , stock_weight and stock_volume are mutually exclusive.
Use prohibited_with validation rule on both.
Both nullable — null means stock is not tracked for this product.
```

### SKU pattern

```
^[A-Z0-9-]{8,12}$   — uppercase alphanumeric with dashes, 8–12 chars
Unique only when not null (partial unique index or application-level check).
```

---

## SoftDeletes

- Use `Illuminate\Database\Eloquent\SoftDeletes` trait
- `deleted_at` column is the soft-delete flag
- Default `->delete()` performs soft delete
- `->forceDelete()` performs hard (permanent) delete

---

## Hard Delete Guard

A product can only be **permanently deleted** (`forceDelete`) if:

1. It has no `OrderItem` records (`orderItems()->exists() === false`)
2. It has no `CartItem` records (`cartItems()->exists() === false`)

Enforce in a `ProductService::forceDelete()` method. Throw a descriptive
exception if guard conditions are not met.

Soft delete is always allowed (product becomes unavailable, history preserved).

---

## Example Product Record

```
Id:         10
Name:       Fresh Salmon
price_type: Weight
priceUnit:  ILS/Kg   (computed)
price:      8000     (= 80.00 ILS)
image_path: products/salmon.jpg
sku:        null
shop_id:    (assigned)
stock:      null
available:  true
```

---

## Authorization Matrix

| Action               | Super User | Staff | Seller | Customer | Delivery Person |
| -------------------- | :--------: | :---: | :----: | :------: | :-------------: |
| View All Products    |     ✅     |  ✅   |   ❌   |    ❌    |       ❌        |
| View Owned Products  |     -      |   -   |   ✅   |    -     |        -        |
| View Product Listing |     ✅     |  ✅   |   ✅   |    ✅    |       ✅        |
| Create Products      |     ✅     |  ❌   |   ❌   |    ❌    |       ❌        |
| Update Products      |     ✅     |  ❌   |   ❌   |    ❌    |       ❌        |
| Soft Delete Products |  **(c1)**  |  ❌   |   ❌   |    ❌    |       ❌        |
| Hard Delete Products |  **(c1)**  |  ❌   |   ❌   |    ❌    |       ❌        |

**(c1) ** — Only soft deleted products for whom their product_id is not present as a foreign key in any other table
Product Listing: The public details of a product (name, price, image, etc.) on the public website product page

---

## Key Implementation Rules

1. **price_unit is never stored** — always derived from `price_type` via
   `priceUnit()`. Do not add a `price_unit` column to the migration.
2. **stock_quantity vs stock_weight vs stock_volume** — mutually exclusive at validation level.
   Only one may be non-null per product row.
3. **Soft vs hard delete** — `delete()` = soft (safe always). `forceDelete()`
   = hard (guarded by order/cart check).
4. **image_path** stores the relative path only (`products/filename.jpg`),
   not the full URL. `imageUrl()` resolves the full URL at runtime.
5. **Decimal return types** — `stock_quantity` , `stock_weight` and `stock_volume` are
   `decimal(10,3)` columns; Laravel returns these as **strings**. Cast or
   handle accordingly in business logic.
6. **shop_id FK** has no cascade — deleting a shop must be blocked at service
   layer if it has products (see Unit 2 deletion guard).
