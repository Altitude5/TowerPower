<?php

use App\Enums\TransactionStatus;
use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\Tower;
use App\Models\Transaction;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a transaction during checkout', function () {
    $user = User::factory()->create();
    $tower = Tower::factory()->create();
    $product = Product::factory()->create(['price' => 1000, 'stock_quantity' => 10, 'price_type' => 'Unit']);

    $cart = CartService::getCart($user);
    CartService::switchTower($cart, $tower->id);
    CartService::addItem($user, $product, ['quantity' => 2]);

    $order = OrderService::checkout($user);

    expect(Transaction::count())->toBe(1);

    $transaction = Transaction::first();
    expect($transaction->order_id)->toBe($order->id);
    expect($transaction->user_id)->toBe($user->id);
    expect($transaction->amount)->toBe(2000);
    expect($transaction->status)->toBe(TransactionStatus::Pending);
    expect($transaction->currency)->toBe('ILS');
});

it('prevents checkout with insufficient stock', function () {
    $user = User::factory()->create();
    $tower = Tower::factory()->create();
    $product = Product::factory()->create(['stock_quantity' => 5, 'price_type' => 'Unit']);

    $cart = CartService::getCart($user);
    CartService::switchTower($cart, $tower->id);
    CartService::addItem($user, $product, ['quantity' => 10]); // More than stock

    OrderService::checkout($user);
})->throws(InsufficientStockException::class);

it('handles gateway callback and updates transaction', function () {
    $user = User::factory()->create();
    $tower = Tower::factory()->create();
    $product = Product::factory()->create(['price_type' => 'Unit']);

    $cart = CartService::getCart($user);
    CartService::switchTower($cart, $tower->id);
    CartService::addItem($user, $product, ['quantity' => 1]);

    $order = OrderService::checkout($user);
    $transaction = Transaction::first();

    $payload = [
        'gateway' => $transaction->gateway,
        'ref' => 'TXN_123456',
        'order_id' => $order->id,
    ];

    $updatedTransaction = PaymentService::handleGatewayCallback($payload);

    expect($updatedTransaction->status)->toBe(TransactionStatus::Completed);
    expect($updatedTransaction->transaction_reference)->toBe('TXN_123456');
});

it('is idempotent for gateway callbacks', function () {
    $user = User::factory()->create();
    $tower = Tower::factory()->create();
    $product = Product::factory()->create(['price_type' => 'Unit']);

    $cart = CartService::getCart($user);
    CartService::switchTower($cart, $tower->id);
    CartService::addItem($user, $product, ['quantity' => 1]);

    $order = OrderService::checkout($user);
    $transaction = Transaction::first();

    $payload = [
        'gateway' => $transaction->gateway,
        'ref' => 'TXN_123456',
        'order_id' => $order->id,
    ];

    // First call
    PaymentService::handleGatewayCallback($payload);

    // Second call
    $secondResult = PaymentService::handleGatewayCallback($payload);

    expect(Transaction::count())->toBe(1);
    expect($secondResult->id)->toBe($transaction->id);
});
