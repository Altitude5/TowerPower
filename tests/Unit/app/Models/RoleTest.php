<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('A Role can be assigned to a User and retrieved via relationship', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'Custom Role', 'slug' => 'custom_role']);
    
    $user->roles()->attach($role, ['is_active' => true]);
    $role->load('users');
    
    expect($role->users)->toHaveCount(1)
        ->and($role->users->first()->id)->toBe($user->id);
});

test('A Role can belong to many Users', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $role = Role::create(['name' => 'Custom Role', 'slug' => 'custom_role']);
    
    $user1->roles()->attach($role, ['is_active' => true]);
    $user2->roles()->attach($role, ['is_active' => true]);
    $role->load('users');
    
    expect($role->users)->toHaveCount(2);
});

test('a Role name must be unique', function () {
    Role::create(['name' => 'Unique Role', 'slug' => 'unique_role_1']);
    Role::create(['name' => 'Unique Role', 'slug' => 'unique_role_2']);
})->throws(QueryException::class);

test('a Role slug must be unique', function () {
    Role::create(['name' => 'Role 1', 'slug' => 'unique_slug']);
    Role::create(['name' => 'Role 2', 'slug' => 'unique_slug']);
})->throws(QueryException::class);

test('a Role name must be at least 3 characters', function () {
    Role::create(['name' => 'ab', 'slug' => 'short_name']);
})->throws(\InvalidArgumentException::class, 'Role name must be at least 3 characters.');

test('a Role name cannot contain special characters', function () {
    Role::create(['name' => 'Admin!@#', 'slug' => 'special_name']);
})->throws(\InvalidArgumentException::class, 'Role name can only contain alpha-numeric characters and spaces.');

test('a Role slug cannot contain special characters or spaces', function () {
    Role::create(['name' => 'Role Name', 'slug' => 'invalid slug!']);
})->throws(\InvalidArgumentException::class, 'Role slug can only contain lowercase alpha-numeric characters and underscores.');

test('A Role cannot be assigned to the same User twice', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'Custom Role', 'slug' => 'custom_role']);
    
    $user->roles()->attach($role, ['is_active' => true]);
    try {
        $user->roles()->attach($role, ['is_active' => true]);
    } catch (QueryException $e) {
        // Expected if DB constraint exists
    }
    
    expect($user->roles()->count())->toBe(1);
});

test('Removing a Role from a User does not delete the User', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'Custom Role', 'slug' => 'custom_role']);
    
    $user->roles()->attach($role, ['is_active' => true]);
    $user->roles()->detach($role);
    
    expect(User::find($user->id))->not->toBeNull()
        ->and($user->roles()->count())->toBe(0);
});

test('Removing a Role from a User does not delete the Role', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'Custom Role', 'slug' => 'custom_role']);
    
    $user->roles()->attach($role, ['is_active' => true]);
    $user->roles()->detach($role);
    
    expect(Role::find($role->id))->not->toBeNull();
});
