<?php

use App\Models\Role;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('RoleSeeder creates all canonical roles', function () {
    $this->seed(RoleSeeder::class);

    expect(Role::count())->toBe(5);

    expect(Role::where('slug', Role::ROLE_SUPER_USER)->exists())->toBeTrue()
        ->and(Role::where('slug', Role::ROLE_STAFF)->exists())->toBeTrue()
        ->and(Role::where('slug', Role::ROLE_SELLER)->exists())->toBeTrue()
        ->and(Role::where('slug', Role::ROLE_CUSTOMER)->exists())->toBeTrue()
        ->and(Role::where('slug', Role::ROLE_DELIVERY_PERSON)->exists())->toBeTrue();
});

test('RoleSeeder is idempotent', function () {
    $this->seed(RoleSeeder::class);
    $this->seed(RoleSeeder::class);

    expect(Role::count())->toBe(5);
});
