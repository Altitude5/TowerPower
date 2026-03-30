<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('Super User can perform all actions on users', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::ROLE_SUPER_USER);

    $otherUser = User::factory()->create();

    expect($admin->can('viewAny', User::class))->toBeTrue();
    expect($admin->can('view', $otherUser))->toBeTrue();
    expect($admin->can('create', User::class))->toBeTrue();
    expect($admin->can('update', $otherUser))->toBeTrue();
    expect($admin->can('delete', $otherUser))->toBeTrue();
});

test('Staff can view but not create or delete users', function () {
    $staff = User::factory()->create();
    $staff->assignRole(Role::ROLE_STAFF);

    $customer = User::factory()->create();
    $customer->assignRole(Role::ROLE_CUSTOMER);

    expect($staff->can('viewAny', User::class))->toBeTrue();
    expect($staff->can('view', $customer))->toBeTrue();
    expect($staff->can('create', User::class))->toBeFalse();
    expect($staff->can('delete', $customer))->toBeFalse();
});

test('Staff cannot view other Staff or Super Users', function () {
    $staff = User::factory()->create();
    $staff->assignRole(Role::ROLE_STAFF);

    $otherStaff = User::factory()->create();
    $otherStaff->assignRole(Role::ROLE_STAFF);
    
    $super = User::factory()->create();
    $super->assignRole(Role::ROLE_SUPER_USER);

    $guest = User::factory()->create(); // No role but still not allowed

    expect($staff->can('view', $otherStaff))->toBeFalse();
    expect($staff->can('view', $super))->toBeFalse();
    expect($staff->can('view', $guest))->toBeFalse();
});

test('Staff cannot update anyone', function () {
    $staff = User::factory()->create();
    $staff->assignRole(Role::ROLE_STAFF);

    $customer = User::factory()->create();
    $customer->assignRole(Role::ROLE_CUSTOMER);

    expect($staff->can('update', $customer))->toBeFalse();
    expect($staff->can('update', $staff))->toBeFalse();
});

test('Users cannot view or update their own profile in the admin context', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::ROLE_CUSTOMER);

    expect($user->can('view', $user))->toBeFalse();
    expect($user->can('update', $user))->toBeFalse();
});

test('Customers cannot view other users', function () {
    $customer = User::factory()->create();
    $customer->assignRole(Role::ROLE_CUSTOMER);

    $otherUser = User::factory()->create();

    expect($customer->can('viewAny', User::class))->toBeFalse();
    expect($customer->can('view', $otherUser))->toBeFalse();
    expect($customer->can('update', $otherUser))->toBeFalse();
});
