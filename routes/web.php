<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/category/{category:slug}', [CategoryController::class, 'show'])->name('category.show');
    Route::get('/category/{category:slug}/{product:slug}', [ProductController::class, 'show'])->name('product.show');
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{product}', [CartController::class, 'store'])->name('cart.store');
    Route::get('/users/{user}', [UserDashboardController::class, 'show'])->name('user.dashboard');
});

require __DIR__.'/settings.php';
