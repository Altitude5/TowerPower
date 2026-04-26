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

    $this->get(route('register'));

    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'tower_id' => $tower->id,
        'floor' => '1',
        'apartment_number' => '101',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertAuthenticated();
    $response->assertRedirect(route('home', absolute: false));
});

test('registration fails if floor or apartment number is missing', function () {
    $city = City::factory()->create();
    $street = Street::factory()->create(['city_id' => $city->id]);
    $tower = Tower::factory()->create(['street_id' => $street->id, 'house_number' => '42', 'city_id' => $city->id]);

    $this->get(route('register'));

    $response = $this->from(route('register'))->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'tower_id' => $tower->id,
        // missing floor and apartment_number
    ]);

    $response->assertSessionHasErrors(['floor', 'apartment_number']);
    $this->assertGuest();
});
