<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('Super User and Staff can access the admin panel', function () {
    $super = User::factory()->create();
    $super->assignRole(Role::ROLE_SUPER_USER);

    $staff = User::factory()->create();
    $staff->assignRole(Role::ROLE_STAFF);

    $this->actingAs($super)->get('/admin')->assertSuccessful();
    $this->actingAs($staff)->get('/admin')->assertSuccessful();
});

test('Customers cannot access the admin panel', function () {
    $customer = User::factory()->create();
    $customer->assignRole(Role::ROLE_CUSTOMER);

    $this->actingAs($customer)->get('/admin')->assertForbidden();
});

test('Guests are redirected to the login page', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});
