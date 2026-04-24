<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\City;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\Shop;
use App\Models\Street;
use App\Models\Tower;
use App\Models\Transaction;
use App\Models\User;
use App\Policies\CategoryPolicy;
use App\Policies\CityPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Policies\RolePolicy;
use App\Policies\ShopPolicy;
use App\Policies\StreetPolicy;
use App\Policies\TowerPolicy;
use App\Policies\TransactionPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Role::class => RolePolicy::class,
        Shop::class => ShopPolicy::class,
        Product::class => ProductPolicy::class,
        Order::class => OrderPolicy::class,
        Transaction::class => TransactionPolicy::class,
        Tower::class => TowerPolicy::class,
        Category::class => CategoryPolicy::class,
        City::class => CityPolicy::class,
        Street::class => StreetPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
