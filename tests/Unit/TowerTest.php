<?php

use App\Models\City;
use App\Models\Street;
use App\Models\Tower;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('calculates full address correctly', function () {
    $city = City::factory()->create(['name' => 'Tel Aviv']);
    $street = Street::factory()->create(['city_id' => $city->id, 'name' => 'Herzl']);
    $tower = Tower::factory()->create([
        'city_id' => $city->id,
        'street_id' => $street->id,
        'house_number' => '14B',
        'zipcode' => '61234'
    ]);

    expect($tower->fullAddress())->toBe('Herzl 14B, Tel Aviv 61234');
});

it('has many users', function () {
    $tower = Tower::factory()->create();
    $user = User::factory()->create();
    
    $tower->users()->attach($user->id, [
        'apartment_number' => '4A',
        'floor' => '1'
    ]);

    expect($tower->users)->toHaveCount(1)
        ->and($tower->users->first()->id)->toBe($user->id);
});
