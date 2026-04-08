<?php

use App\Models\City;
use App\Models\Street;
use App\Models\Tower;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('belongs to a city', function () {
    $city = City::factory()->create();
    $street = Street::factory()->create(['city_id' => $city->id]);

    expect($street->city->id)->toBe($city->id);
});

it('has many towers', function () {
    $city = City::factory()->create();
    $street = Street::factory()->create(['city_id' => $city->id]);
    $tower = Tower::factory()->create([
        'city_id' => $city->id,
        'street_id' => $street->id,
    ]);

    expect($street->towers)->toHaveCount(1)
        ->and($street->towers->first()->id)->toBe($tower->id);
});
