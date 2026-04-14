<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(protected CartService $cartService) {}

    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'nullable|numeric',
            'weight' => 'nullable|numeric',
            'volume' => 'nullable|numeric',
            'absolute' => 'boolean',
        ]);

        // If absolute update is occurring, we allow all quantity fields to be missing/null,
        // because the service will handle the deletion logic.
        if (isset($validated['absolute']) && $validated['absolute']) {
            // Remove the strict requirement for at least one value to allow setting to 0
        } else {
            $request->validate([
                'quantity' => 'required_without_all:weight,volume',
                'weight' => 'required_without_all:quantity,volume',
                'volume' => 'required_without_all:quantity,weight',
            ]);
        }

        \Illuminate\Support\Facades\Log::info('CartController Store Payload', $validated);

        CartService::addItem(auth()->user(), $product, $validated);

        return redirect()->back()->with('success', 'Cart updated.');
    }

    public function index(): Response
    {
        $cart = CartService::getCart(auth()->user());
        $cart->load('items.product');

        return Inertia::render('Cart/Index', [
            'cart' => $cart,
            'items' => $cart->items,
        ]);
    }
}
