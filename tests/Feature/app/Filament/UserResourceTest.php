<?php

use App\Filament\Resources\Users\UserResource;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('Staff can only see non-admin users in the list', function () {
    $staffActor = User::factory()->create();
    $staffActor->assignRole(Role::ROLE_STAFF);

    $customer = User::factory()->create();
    $customer->assignRole(Role::ROLE_CUSTOMER);

    $super = User::factory()->create();
    $super->assignRole(Role::ROLE_SUPER_USER);

    $otherStaff = User::factory()->create();
    $otherStaff->assignRole(Role::ROLE_STAFF);

    $this->actingAs($staffActor);

    $query = UserResource::getEloquentQuery();

    // The query should only include non-admin roles (Seller, Customer, Delivery Person)
    // and exclude Super Users and Staff (including the actor).
    expect($query->count())->toBe(1);
    expect($query->where('id', $customer->id)->exists())->toBeTrue();
    expect($query->where('id', $staffActor->id)->exists())->toBeFalse();
    expect($query->where('id', $super->id)->exists())->toBeFalse();
});

test('Super User can see all users in the list', function () {
    $superActor = User::factory()->create();
    $superActor->assignRole(Role::ROLE_SUPER_USER);

    $customer = User::factory()->create();
    $customer->assignRole(Role::ROLE_CUSTOMER);

    $staff = User::factory()->create();
    $staff->assignRole(Role::ROLE_STAFF);

    $this->actingAs($superActor);

    $query = UserResource::getEloquentQuery();

    expect($query->count())->toBe(3);
});
