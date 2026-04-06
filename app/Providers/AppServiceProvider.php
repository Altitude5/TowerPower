<?php

namespace App\Providers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Policies\CartItemPolicy;
use App\Policies\CartPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\SubOrder;
use App\Models\OrderItem;
use App\Observers\OrderObserver;
use App\Observers\SubOrderObserver;
use App\Policies\OrderPolicy;
use App\Policies\SubOrderPolicy;
use App\Policies\OrderItemPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        Gate::policy(Cart::class, CartPolicy::class);
        Gate::policy(CartItem::class, CartItemPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(SubOrder::class, SubOrderPolicy::class);
        Gate::policy(OrderItem::class, OrderItemPolicy::class);

        Order::observe(OrderObserver::class);
        SubOrder::observe(SubOrderObserver::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
