<?php

use App\Models\City;
use App\Models\Street;
use App\Models\Tower;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyFeature(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $city = City::factory()->create();
    $street = Street::factory()->create(['city_id' => $city->id]);
    $tower = Tower::factory()->create(['street_id' => $street->id, 'house_number' => '42', 'city_id' => $city->id]);

    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'tower_id' => $tower->id,
        'floor' => '1',
        'apartment_number' => '101',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('home', absolute: false));
});
