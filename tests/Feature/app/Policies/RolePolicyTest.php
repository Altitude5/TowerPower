<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('Super User can manage roles', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::ROLE_SUPER_USER);

    $role = Role::where('slug', Role::ROLE_CUSTOMER)->first();

    expect($admin->can('viewAny', Role::class))->toBeTrue();
    expect($admin->can('view', $role))->toBeTrue();
    expect($admin->can('create', Role::class))->toBeTrue();
    expect($admin->can('update', $role))->toBeTrue();
    expect($admin->can('delete', $role))->toBeTrue();
});

test('Staff cannot view roles', function () {
    $staff = User::factory()->create();
    $staff->assignRole(Role::ROLE_STAFF);

    $role = Role::where('slug', Role::ROLE_CUSTOMER)->first();

    expect($staff->can('viewAny', Role::class))->toBeFalse();
    expect($staff->can('view', $role))->toBeFalse();
    expect($staff->can('create', Role::class))->toBeFalse();
    expect($staff->can('update', $role))->toBeFalse();
    expect($staff->can('delete', $role))->toBeFalse();
});

test('Customers cannot see roles', function () {
    $customer = User::factory()->create();
    $customer->assignRole(Role::ROLE_CUSTOMER);

    $role = Role::where('slug', Role::ROLE_STAFF)->first();

    expect($customer->can('viewAny', Role::class))->toBeFalse();
    expect($customer->can('view', $role))->toBeFalse();
});
