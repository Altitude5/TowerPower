<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\DevUserSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('DevUserSeeder creates all standard dev users in local environment', function () {
    // We need roles first
    $this->seed(RoleSeeder::class);
    
    // The seeder now allows 'testing' environment for tests to pass
    $this->seed(DevUserSeeder::class);

    expect(User::count())->toBe(6);

    $super = User::where('email', 'super@example.com')->first();
    expect($super->isSuperUser())->toBeTrue();

    $staff = User::where('email', 'staff@example.com')->first();
    expect($staff->isStaff())->toBeTrue();

    $seller = User::where('email', 'seller@example.com')->first();
    expect($seller->isSeller())->toBeTrue();

    $customer = User::where('email', 'customer@example.com')->first();
    expect($customer->isCustomer())->toBeTrue();

    $delivery = User::where('email', 'delivery@example.com')->first();
    expect($delivery->isDeliveryPerson())->toBeTrue();

    $user = User::where('email', 'user@example.com')->first();
    expect($user->roles()->count())->toBe(0);
});

test('DevUserSeeder does not create users in production environment', function () {
    $this->seed(RoleSeeder::class);
    
    config(['app.env' => 'production']);

    $this->seed(DevUserSeeder::class, ['--force' => true]);

    expect(\App\Models\User::count())->toBe(0);
});
