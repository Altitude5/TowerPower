<?php

use App\Models\City;
use App\Models\Street;
use App\Models\Tower;

test('registration page loads', function () {
    $this->get('/register')->assertStatus(200);
});

test('tower lookup returns tower when found', function () {
    $city = City::factory()->create();
    $street = Street::factory()->create(['city_id' => $city->id]);
    $tower = Tower::factory()->create(['street_id' => $street->id, 'house_number' => '42']);

    $this->get(route('geo.towers', ['street' => $street->id, 'house_number' => '42']))
        ->assertStatus(200)
        ->assertJson(['found' => true, 'tower' => ['id' => $tower->id]]);
});

test('tower lookup returns not found when no tower exists', function () {
    $city = City::factory()->create();
    $street = Street::factory()->create(['city_id' => $city->id]);

    $this->get(route('geo.towers', ['street' => $street->id, 'house_number' => '999']))
        ->assertStatus(200)
        ->assertJson(['found' => false]);
});
