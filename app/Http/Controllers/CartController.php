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
        $cart->load('items.product');

        return Inertia::render('Cart/Index', [
            'cart' => $cart,
            'items' => $cart->items,
        ]);
    }
}
