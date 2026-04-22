<?php

use App\Models\Role;
use App\Models\Tower;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superUser = User::factory()->create();
    $role = Role::firstOrCreate(['slug' => 'super_user'], ['name' => 'Super User']);
    $this->superUser->assignRole($role);
});

it('allows super user to view all towers', function () {
    Tower::factory()->count(3)->create();

    $this->actingAs($this->superUser)
        ->get('/admin/towers')
        ->assertStatus(200);
});

it('does not allow unauthorized access to tower creation', function () {
    $customer = User::factory()->create();
    $role = Role::firstOrCreate(['slug' => 'customer'], ['name' => 'Customer']);
    $customer->assignRole($role);

    $this->actingAs($customer)
        ->get('/admin/towers/create')
        ->assertStatus(403);
});
