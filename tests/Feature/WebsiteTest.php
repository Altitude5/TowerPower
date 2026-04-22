<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole(Role::ROLE_CUSTOMER);
    $this->withoutVite();
});

test('unauthenticated users are redirected to login for protected pages', function () {
    $this->get('/cart')->assertRedirect('/login');
});

test('unauthenticated users can access home page', function () {
    $this->get('/')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->component('Home'));
});

test('authenticated user can access home page', function () {
    $this->actingAs($this->user)
        ->get('/')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->component('Home'));
});

test('authenticated user can access category page', function () {
    $category = Category::factory()->create(['name' => 'Test Category', 'slug' => 'test-category']);

    $this->actingAs($this->user)
        ->get("/category/{$category->slug}")
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->component('Category/Show'));
});

test('authenticated user can access product page', function () {
    $category = Category::factory()->create(['name' => 'Test Category', 'slug' => 'test-category']);
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'slug' => 'test-product',
        'available' => true,
    ]);

    $this->actingAs($this->user)
        ->get("/category/{$category->slug}/{$product->slug}")
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->component('Product/Show'));
});

test('authenticated user can access cart page', function () {
    $this->actingAs($this->user)
        ->get('/cart')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->component('Cart/Index'));
});

test('authenticated user can access their own dashboard', function () {
    $this->actingAs($this->user)
        ->get("/users/{$this->user->id}")
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->component('User/Dashboard'));
});

test('authenticated user cannot access another users dashboard', function () {
    $otherUser = User::factory()->create();

    $this->actingAs($this->user)
        ->get("/users/{$otherUser->id}")
        ->assertStatus(403);
});
