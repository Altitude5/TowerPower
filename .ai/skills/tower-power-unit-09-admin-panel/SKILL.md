---
name: tower-power-unit-09-admin
description: >
    Implement the Admin Panel for the Tower Power Laravel 13 app using Filament 5.x. Use this skill when creating or modifying: Filament Resources, Filament Pages, Filament Widgets, panel access control (canAccess), per-resource Staff vs SuperUser policies, table filters, InfoLists, or any /admin/* route. Do NOT use hand-rolled controllers or Inertia for the admin panel — everything goes through Filament.
---

# Unit 9 — Admin Panel (Filament 5.x)

## Stack

- **Framework**: Laravel Filament 5.x
- **URL prefix**: `/admin`
- **Access**: Super Users and Staff users only
- **Pagination default**: 25 records per page on all tables

---

## Filament Panel Setup

```php
// app/Providers/Filament/AdminPanelProvider.php
public function panel(Panel $panel): Panel
{
    return $panel
        ->id('admin')
        ->path('admin')
        ->authMiddleware([Authenticate::class])
        ->authGuard('web')
        ->login()
        ->resources([...])  // register all resources
        ->pages([...])
        ->widgets([...]);
}
```

### Panel Access Gate

Only Super Users and Staff may enter the panel. Implement via the
`canAccessPanel` method on the `User` model:

```php
// app/Models/User.php
public function canAccessPanel(Panel $panel): bool
{
    return $this->isAdmin(); // isSuperUser() || isStaff()
}
```

---

## Resource Structure

Every section has a **full CRUD set** plus an **InfoList**:

| Page   | Filament Class | Purpose                         |
| ------ | -------------- | ------------------------------- |
| List   | `ListRecords`  | Paginated table with filters    |
| Create | `CreateRecord` | Form to create a new record     |
| Edit   | `EditRecord`   | Form to edit an existing record |
| View   | `ViewRecord`   | InfoList read-only detail view  |

Each Resource must implement:

- `table(Table $table)` — columns, filters, actions, bulk actions
- `form(Form $form)` — fields for Create and Edit
- `infolist(Infolist $infolist)` — read-only detail layout for View page

---

## Sidebar Navigation

| Label         | Resource / Page                                                   | Staff Access      |
| ------------- | ----------------------------------------------------------------- | ----------------- |
| Dashboard     | `AdminDashboard` (custom Page)                                    | ✅                |
| Shops         | `ShopResource`                                                    | ✅ view only      |
| Users         | `UserResource`                                                    | ✅ filtered       |
| Roles         | `RoleResource`                                                    | ❌ SuperUser only |
| Products      | `ProductResource`                                                 | ✅                |
| Orders        | `OrderResource`                                                   | ✅                |
| Transactions  | `TransactionResource`                                             | ✅                |
| Discounts     | `DiscountResource`                                                | ✅ (placeholder)  |
| Towers        | `TowerResource`                                                   | ✅                |
| Categories    | `CategoryResource`                                                | ❌ SuperUser only |
| Geo-locations | `CityResource` + `StreetResource` + `GeoImporterPage` (nav group) | ✅ limited        |

---

## Per-Resource Access Control

Use Filament's **Policy** integration. Each Resource references its model
Policy. Filament automatically calls `viewAny`, `view`, `create`, `update`,
`delete`, `forceDelete` on the policy.

Register policies in `AuthServiceProvider`:

```php
protected $policies = [
    User::class        => UserPolicy::class,
    Role::class        => RolePolicy::class,
    Shop::class        => ShopPolicy::class,
    Product::class     => ProductPolicy::class,
    Order::class       => OrderPolicy::class,
    Transaction::class => TransactionPolicy::class,
    Tower::class       => TowerPolicy::class,
    Category::class    => CategoryPolicy::class,
    City::class        => CityPolicy::class,
    Street::class      => StreetPolicy::class,
];
```

---

## Pagination

Set per-Resource table (25 is the default):

```php
public function table(Table $table): Table
{
    return $table
        ->defaultPaginationPageOption(25)
        ->paginationPageOptions([25, 50, 100]);
}
```

---

## InfoList Schema

Every Resource View page must implement `infolist()`. Example pattern:

```php
public static function infolist(Infolist $infolist): Infolist
{
    return $infolist->schema([
        Section::make('Details')->schema([
            TextEntry::make('id')->label('ID'),
            TextEntry::make('name'),
            TextEntry::make('created_at')->dateTime(),
            TextEntry::make('updated_at')->dateTime(),
            // ... model-specific entries
        ]),
    ]);
}
```

InfoLists are **read-only** — do not reuse the form schema on the View page.

---

## Discounts Resource (Placeholder)

Scaffold a `DiscountResource` with an empty table and form until Unit 7
(Promotions & Discounts) is specced. Set `canCreate()` to `false`.
Keep it visible to Staff for when it is implemented.

---

## Key Implementation Rules

1. **Filament Policies are the authority** — register a Policy for every
   Resource model. Never bypass with inline `auth()->user()` checks in
   table actions without a backing policy method.

2. **Staff query filter on UserResource is applied in `getEloquentQuery()`**
   — not as a Filament table filter the user can remove. Staff must never
   receive Staff/SuperUser rows at the query level.

3. **`canAccess()` controls sidebar visibility** — use it on `RoleResource`,
   `CategoryResource`, and `GeoImporterPage` to hide nav items for
   unauthorised roles. Filament omits nav items automatically.

4. **Full CRUD pages exist on every Resource** — even where Staff cannot
   create/edit/delete. The policy gates the action; the page class still
   exists. Do not remove `CreateRecord` or `EditRecord` pages from the
   resource — remove the policy permission instead.

5. **InfoList is required on every Resource** — every Resource must have a
   `ViewRecord` page with a populated `infolist()` schema. This is a hard
   spec requirement.

6. **Relation Managers for nested routes** — `/admin/shops/{id}/products`
   and geo city/street tower views are Filament Relation Managers on the
   parent Resource's View page. They are not separate Filament Resources.

7. **Transactions are never deleted** — `TransactionPolicy::delete()` returns
   `false` unconditionally. Financial records must never be cascade-removed
   or admin-deleted.

8. **Orders are never admin-created** — `OrderPolicy::create()` returns
   `false`. Orders are only created via the frontend checkout flow (Unit 5).

9. **Geo importer is SuperUser only** — implemented as a custom Filament
   `Page` with `canAccess(): bool { return auth()->user()->isSuperUser(); }`.
   Staff can view cities and streets but not the importer.

10. **`/admin/geo` Staff access summary**:
    - Geo index (navigation group landing): ✅ Staff can view
    - Importer: ❌ SuperUser only (`canAccess`)
    - Cities list + detail: ✅ Staff view only (policy)
    - Streets list + detail: ✅ Staff view only (policy)
