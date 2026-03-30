<?php

use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('it returns false if user is not soft-deleted', function () {
    $user = User::factory()->create();
    expect(UserService::canHardDelete($user))->toBeFalse();
});

test('it returns true if user is soft-deleted and has no constraints', function () {
    $user = User::factory()->create();
    $user->delete(); // Soft delete

    expect(UserService::canHardDelete($user))->toBeTrue();
});

test('it returns false if user is soft-deleted but has roles assigned', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::ROLE_CUSTOMER);
    $user->delete();

    // Still has role_user rows
    expect(UserService::canHardDelete($user))->toBeFalse();
});

test('it returns false if user is soft-deleted but assigned a role to someone else', function () {
    $admin = User::factory()->create();
    $customer = User::factory()->create();
    
    $customer->assignRole(Role::ROLE_CUSTOMER, $admin); // admin assigned role
    $admin->delete();

    // admin is present in assigned_by column
    expect(UserService::canHardDelete($admin))->toBeFalse();
});

test('it returns true if roles are detached before hard delete check', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::ROLE_CUSTOMER);
    $user->delete();

    expect(UserService::canHardDelete($user))->toBeFalse();

    $user->roles()->detach();
    expect(UserService::canHardDelete($user))->toBeTrue();
});
