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
            'quantity' => 'nullable|numeric|min:0.001',
            'weight' => 'nullable|numeric|min:0.001',
            'volume' => 'nullable|numeric|min:0.001',
        ]);

        CartService::addItem(auth()->user(), $product, $validated);

        return redirect()->back()->with('success', 'Product added to cart.');
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
