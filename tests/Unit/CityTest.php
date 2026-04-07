<?php

use App\Models\City;
use App\Models\Tower;
use App\Models\Street;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('has many towers', function () {
    $city = City::factory()->create();
    $street = Street::factory()->create(['city_id' => $city->id]);
    $tower = Tower::factory()->create([
        'city_id' => $city->id,
        'street_id' => $street->id,
    ]);

    expect($city->towers)->toHaveCount(1)
        ->and($city->towers->first()->id)->toBe($tower->id);
});

it('has many streets', function () {
    $city = City::factory()->create();
    $street = Street::factory()->create(['city_id' => $city->id]);

    expect($city->streets)->toHaveCount(1)
        ->and($city->streets->first()->id)->toBe($street->id);
});
