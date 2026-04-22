<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('it creates a super user successfully', function () {
    $this->artisan('user:create-super-user')
        ->expectsQuestion('Enter the email address for the Super User', 'super@example.com')
        ->expectsQuestion('Enter the password (minimum 8 characters)', 'password123')
        ->expectsQuestion('Confirm the password', 'password123')
        ->expectsOutput('Super User super@example.com created successfully.')
        ->assertExitCode(0);

    $user = User::where('email', 'super@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->isSuperUser())->toBeTrue();

    // Check assigned_by is null
    $pivot = $user->roles()->where('slug', Role::ROLE_SUPER_USER)->first()->pivot;
    expect($pivot->assigned_by)->toBeNull();
});

test('it validates email uniqueness', function () {
    User::factory()->create(['email' => 'exists@example.com']);

    $this->artisan('user:create-super-user')
        ->expectsQuestion('Enter the email address for the Super User', 'exists@example.com')
        ->expectsOutput('The email has already been taken.')
        ->assertExitCode(1);
});

test('it validates password confirmation', function () {
    $this->artisan('user:create-super-user')
        ->expectsQuestion('Enter the email address for the Super User', 'new@example.com')
        ->expectsQuestion('Enter the password (minimum 8 characters)', 'password123')
        ->expectsQuestion('Confirm the password', 'mismatch')
        ->expectsOutput('Passwords do not match.')
        ->assertExitCode(1);
});

test('it validates password length', function () {
    $this->artisan('user:create-super-user')
        ->expectsQuestion('Enter the email address for the Super User', 'new@example.com')
        ->expectsQuestion('Enter the password (minimum 8 characters)', 'short')
        ->expectsQuestion('Confirm the password', 'short')
        ->expectsOutput('Password must be at least 8 characters.')
        ->assertExitCode(1);
});
