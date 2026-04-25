<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartItemRequest;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(protected CartService $cartService) {}

    public function store(StoreCartItemRequest $request, Product $product): RedirectResponse
    {
        CartService::addItem(auth()->user(), $product, $request->validated());

        return redirect()->back()->with('success', 'Cart updated.');
    }

    public function index(): Response
    {
        $cart = CartService::getCart(auth()->user());
        $cart->load(['items.product.shop', 'items.product.category', 'tower']);

        $deliveryService = app(\App\Services\DeliveryAssignmentService::class);
        $cityId = $cart->tower->city_id ?? null;

        $groupedItems = $cart->items->groupBy(function ($item) {
            return $item->product->shop_id.'-'.($item->category_name ?? $item->product->category?->name ?? 'General');
        })->map(function ($items) use ($deliveryService, $cityId) {
            $firstItem = $items->first();
            $shopId = $firstItem->product->shop_id;
            
            $nextDelivery = null;
            if ($cityId) {
                $nextDelivery = $deliveryService->findNextDeliveryDate($shopId, $cityId, now());
            }

            return [
                'shop_id' => $shopId,
                'shop_name' => $firstItem->shop_name ?? $firstItem->product->shop->name,
                'category_name' => $firstItem->category_name ?? $firstItem->product->category?->name ?? 'General',
                'items' => $items,
                'expected_delivery_date' => $nextDelivery ? $nextDelivery['date']->format('Y-m-d') : null,
                'expected_delivery_day' => $nextDelivery ? $nextDelivery['date']->format('l, M j') : 'No schedule found',
            ];
        })->values();

        return Inertia::render('Cart/Index', [
            'cart' => $cart,
            'groupedItems' => $groupedItems,
            'items' => $cart->items, // Keep for compatibility if needed, but we'll use groupedItems
        ]);
    }
}
