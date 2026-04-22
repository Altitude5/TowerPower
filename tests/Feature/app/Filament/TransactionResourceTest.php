<?php

use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('allows super user and staff to access transactions list', function (string $role) {
    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user)
        ->get(TransactionResource::getUrl('index'))
        ->assertSuccessful();
})->with([Role::ROLE_SUPER_USER, Role::ROLE_STAFF]);

it('denies customer access to transactions list', function () {
    $customer = User::factory()->create();
    $customer->assignRole(Role::ROLE_CUSTOMER);

    $this->actingAs($customer)
        ->get(TransactionResource::getUrl('index'))
        ->assertForbidden();
});

it('does not allow editing transactions', function () {
    $super = User::factory()->create();
    $super->assignRole(Role::ROLE_SUPER_USER);

    $transaction = Transaction::factory()->create();

    $this->actingAs($super);

    // Edit action should not even exist
    Livewire::test(ListTransactions::class)
        ->assertTableActionDoesNotExist('edit', null, $transaction);
});

it('does not allow deleting transactions', function () {
    $super = User::factory()->create();
    $super->assignRole(Role::ROLE_SUPER_USER);

    $transaction = Transaction::factory()->create();

    $this->actingAs($super);

    // Delete action should not even exist
    Livewire::test(ListTransactions::class)
        ->assertTableActionDoesNotExist('delete', null, $transaction);
});
