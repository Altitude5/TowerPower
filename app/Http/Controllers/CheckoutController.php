<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    /**
     * Display the checkout page.
     */
    public function index(Request $request): Response|RedirectResponse
    {
        $cart = CartService::getCart($request->user());
        $cart->load(['items.product', 'tower.city', 'tower.street']);

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        if (!$cart->tower_id) {
            return redirect()->route('cart.index')->with('error', 'Please select a tower before checking out.');
        }

        return Inertia::render('Checkout/Index', [
            'cart' => $cart,
            'items' => $cart->items,
            'tower' => $cart->tower,
        ]);
    }

    /**
     * Process the checkout and create an order.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $order = OrderService::checkout($request->user());

            return redirect()->route('checkout.success', $order);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the checkout success page.
     */
    public function success(Order $order): Response
    {
        // Ensure the order belongs to the authenticated user
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('Checkout/Success', [
            'order' => $order->load(['tower.city', 'tower.street']),
        ]);
    }
}
