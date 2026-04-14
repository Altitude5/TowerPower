<?php

namespace App\Http\Middleware;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $cartItems = [];

        if ($user) {
            $cartItems = CartItem::whereHas('cart', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
                ->get(['product_id', 'quantity', 'weight', 'volume'])
                ->mapWithKeys(function ($item) {
                    $val = null;

                    if ($item->quantity !== null && bccomp((string) $item->quantity, '0', 3) > 0) {
                        $val = $item->quantity;
                    } elseif ($item->weight !== null && bccomp((string) $item->weight, '0', 3) > 0) {
                        $val = $item->weight;
                    } elseif ($item->volume !== null && bccomp((string) $item->volume, '0', 3) > 0) {
                        $val = $item->volume;
                    }

                    return [(string) $item->product_id => $val];
                })
                ->toArray();
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
            ],
            'cart' => [
                'items' => (object) $cartItems,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
