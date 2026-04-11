<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;

uses(DatabaseTruncation::class);

beforeEach(function () {
    Role::firstOrCreate(['slug' => Role::ROLE_SUPER_USER], ['name' => 'Super User']);
    Role::firstOrCreate(['slug' => Role::ROLE_STAFF], ['name' => 'Staff']);
    Role::firstOrCreate(['slug' => Role::ROLE_SELLER], ['name' => 'Seller']);
    Role::firstOrCreate(['slug' => Role::ROLE_CUSTOMER], ['name' => 'Customer']);
    Role::firstOrCreate(['slug' => Role::ROLE_DELIVERY_PERSON], ['name' => 'Delivery Person']);

    $this->withoutVite();
});

// --- View Roles ---
// Super User can view Roles on the roles admin page (/admin/roles)
test('Super User can view Roles on the roles admin page', function () {
    createBrowserUserWithRole(Role::ROLE_SUPER_USER, 'super_role_view@example.com');

    $page = visit('/admin/roles');
    $page->fill('#form\.email', 'super_role_view@example.com')
        ->fill('#form\.password', 'password')
        ->click('button[type="submit"]')
        ->waitForText('Roles')
        ->assertSee('Staff')
        ->assertSee('Super User');
});

// Staff User can view Roles on the roles admin page (/admin/roles) but cannot see management buttons
test('Staff User can view Roles on the roles admin page', function () {
    createBrowserUserWithRole(Role::ROLE_STAFF, 'staff_role_view@example.com');

    $page = visit('/admin/roles');
    $page->fill('#form\.email', 'staff_role_view@example.com')
        ->fill('#form\.password', 'password')
        ->click('button[type="submit"]')
        ->waitForText('Roles')
        ->assertSee('Staff')
        ->assertSee('Super User')
        ->assertDontSee('New role');
});

// Super User can view a Role on the role details admin page (/admin/roles/{role_id}/view)
test('Super User can view a Role on the role details admin page', function () {
    createBrowserUserWithRole(Role::ROLE_SUPER_USER, 'super_role_view_single@example.com');
    $role = Role::where('slug', Role::ROLE_STAFF)->first();

    $page = visit("/admin/roles/{$role->id}/view");
    $page->fill('#form\.email', 'super_role_view_single@example.com')
        ->fill('#form\.password', 'password')
        ->click('button[type="submit"]')
        ->waitForText('View Role')
        ->assertSee('Name')
        ->assertSee('Staff');
});

// Staff User can view a Role on the role details admin page (/admin/roles/{role_id}/view) but cannot see Edit button
test('Staff User can view a Role on the role details admin page', function () {
    createBrowserUserWithRole(Role::ROLE_STAFF, 'staff_role_single_view@example.com');
    $role = Role::where('slug', Role::ROLE_SUPER_USER)->first();

    $page = visit("/admin/roles/{$role->id}/view");
    $page->fill('#form\.email', 'staff_role_single_view@example.com')
        ->fill('#form\.password', 'password')
        ->click('button[type="submit"]')
        ->waitForText('View Role')
        ->assertSee('Name')
        ->assertSee('Super User')
        ->assertDontSee('Edit');
});

// --- Create Role ---
// Super User can create a new Role by clicking the "New role" button on the roles admin page (/admin/roles) and filling the form
test('Super User can create a new Role by clicking the "New role" button on the roles admin page and filling the form', function () {
    createBrowserUserWithRole(Role::ROLE_SUPER_USER, 'super_role_create@example.com');

    $page = visit('/admin/roles');
    $page->fill('#form\.email', 'super_role_create@example.com')
        ->fill('#form\.password', 'password')
        ->click('button[type="submit"]')
        ->click('a:has-text("New role")')
        ->waitForText('Create Role')
        ->fill('#form\.name', 'Moderator')
        ->fill('#form\.slug', 'moderator')
        ->click('button[type="submit"]:has-text("Create")')
        ->waitForText('Created')
        ->navigate('/admin/roles')
        ->assertSee('Moderator');

    $this->assertDatabaseHas('roles', ['name' => 'Moderator', 'slug' => 'moderator']);
});

// --- Update Role ---

// Super User can update an existing Role by clicking the "Edit" button on the Role row on the roles admin page (/admin/roles) and filling the form
test('Super User can update an existing Role by clicking the Edit button on the Role row on the roles admin page and filling the form', function () {
    createBrowserUserWithRole(Role::ROLE_SUPER_USER, 'super_role_update@example.com');
    $role = Role::create(['name' => 'Support', 'slug' => 'support']);

    $page = visit('/admin/roles');
    $page->fill('#form\.email', 'super_role_update@example.com')
        ->fill('#form\.password', 'password')
        ->click('button[type="submit"]')
        ->waitForText('Support')
        ->click('a[href$="/roles/'.$role->id.'"]:has-text("Edit")')
        ->waitForText('Edit Role')
        ->fill('#form\.name', 'Senior Support')
        ->click('button:has-text("Save changes")')
        ->waitForText('Saved')
        ->navigate('/admin/roles')
        ->assertSee('Senior Support');

    $this->assertDatabaseHas('roles', ['name' => 'Senior Support']);
});
// Super User can update an existing Role by clicking the "Edit Role" button on the Role on the role\'s details admin page (/admin/roles/{role_id}/view) and filling the form
test("Super User can update an existing Role by clicking the Edit Role button on the Role on the role's details admin page and filling the form", function () {
    createBrowserUserWithRole(Role::ROLE_SUPER_USER, 'super_role_update_from_view@example.com');
    $role = Role::create(['name' => 'Guest', 'slug' => 'guest']);

    $page = visit("/admin/roles/{$role->id}/view");
    $page->fill('#form\.email', 'super_role_update_from_view@example.com')
        ->fill('#form\.password', 'password')
        ->click('button[type="submit"]')
        ->click('a[href$="/roles/'.$role->id.'"]:has-text("Edit")')
        ->waitForText('Edit Role')
        ->fill('#form\.name', 'Visitor')
        ->click('button:has-text("Save changes")')
        ->waitForText('Saved');

    $this->assertDatabaseHas('roles', ['name' => 'Visitor']);
});

// --- Delete Role ---

// Super User can delete an existing Role by clicking the "Delete" button on the Role row on the roles admin page (/admin/roles) for a Role that has no Users
test('Super User can delete an existing Role by clicking the Delete button on the Role row on the roles admin page and for a Role that has no Users', function () {
    createBrowserUserWithRole(Role::ROLE_SUPER_USER, 'super_role_delete@example.com');
    $role = Role::create(['name' => 'Temp', 'slug' => 'temp']);

    $page = visit('/admin/roles');
    $page->fill('#form\.email', 'super_role_delete@example.com')
        ->fill('#form\.password', 'password')
        ->click('button[type="submit"]')
        ->waitForText('Temp')
        ->click('tr:has-text("Temp") button:has-text("Delete")')
        ->waitForText('Are you sure you would like to do this?')
        ->click('.fi-modal button[type="submit"]') // <--- Changed here
        ->waitForText('Deleted')
        ->assertDontSee('Temp');

    $this->assertDatabaseMissing('roles', ['name' => 'Temp']);
});

// Super User cannot see a "Delete" button on the Role row on the roles admin page (/admin/roles) for a Role that has Users
test('Super User cannot see a Delete button on the Role row on the roles admin page for a Role that has Users', function () {
    createBrowserUserWithRole(Role::ROLE_SUPER_USER, 'super_role_nodelete@example.com');
    $role = Role::create(['name' => 'Protected', 'slug' => 'protected']);
    $user = User::factory()->create();
    $user->assignRole($role);

    $page = visit('/admin/roles');
    $page->fill('#form\.email', 'super_role_nodelete@example.com')
        ->fill('#form\.password', 'password')
        ->click('button[type="submit"]')
        ->waitForText('Protected')
        ->assertMissing('tr:has-text("Protected") button:has-text("Delete")');
});

// --- Assign and revoke roles ---
// Super User can assign a Role to a User by editing the user clicking Roles waiting for a list to load and choosing a Role
test('Super User can assign a Role to a User by editing the user clicking Roles waiting for a list to load and choosing a Role', function () {
    createBrowserUserWithRole(Role::ROLE_SUPER_USER, 'super_user_assign@example.com');
    $targetUser = User::factory()->create(['name' => 'Candidate User']);
    $role = Role::create(['name' => 'Protected', 'slug' => 'protected']);

    $page = visit('/admin/login');
    $page->fill('#form\.email', 'super_user_assign@example.com')
        ->fill('#form\.password', 'password')
        ->click('button[type="submit"]')
        ->waitForText('Dashboard'); // Or similar text to ensure login finished

    $page = visit("/admin/users/{$targetUser->id}/view");
    $page->waitForText('Candidate User');

    $page = visit("/admin/users/{$targetUser->id}");
    $page->waitForText('Edit User')
        ->click('.fi-select-input') // Click on the searchable select input
        ->waitForText('Protected')
        ->click('[role="option"]:has-text("Protected")')
        ->click('button:has-text("Save changes")')
        ->waitForText('Saved');

    $this->assertTrue($targetUser->fresh()->hasRole('protected'));
});

// Super User can revoke a Role from a User by editing the user clicking Roles waiting for a list to load and unchoosing a Role
test('Super User can revoke a Role from a User by editing the user clicking Roles waiting for a list to load and unchoosing a Role', function () {
    createBrowserUserWithRole(Role::ROLE_SUPER_USER, 'super_user_revoke@example.com');
    $targetUser = User::factory()->create(['name' => 'Revoke Target']);
    $roleToRevoke = Role::where('slug', Role::ROLE_SELLER)->first();
    $targetUser->assignRole($roleToRevoke);

    $page = visit('/admin/login');
    $page->fill('#form\.email', 'super_user_revoke@example.com')
        ->fill('#form\.password', 'password')
        ->click('button[type="submit"]')
        ->waitForText('Dashboard'); // Or similar text to ensure login finished

    $page = visit("/admin/users/{$targetUser->id}");
    $page->waitForText('Edit User')
        ->waitForText('seller')
        ->click('.fi-badge:has-text("seller") button')
        ->click('button:has-text("Save changes")')
        ->waitForText('Saved');

    $this->assertFalse($targetUser->fresh()->hasRole(Role::ROLE_SELLER));
});
