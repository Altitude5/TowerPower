<?php

use App\Models\City;
use App\Models\Street;
use App\Models\Tower;

test('registration page initial state: only city field is visible', function () {
    City::factory()->create(['name' => 'Test City']);

    $response = $this->get('/register')->assertStatus(200);

    $response->assertSee('City');
    $response->assertDontSee([
        'Street', 'House Number', 'Yes, this is my Tower!', 'Start over tower search',
        'Floor', 'Apartment #', 'Full name', 'Email address', 'Password', 'Confirm password',
    ]);
});

test('registration page after city selection: street field becomes visible', function () {
    $city = City::factory()->create();
    $street = Street::factory()->create(['city_id' => $city->id]);

    $this->get(route('geo.streets', ['city' => $city->id]))
        ->assertStatus(200)
        ->assertJsonFragment(['id' => $street->id, 'name' => $street->name]);

    $this->get('/register')
        ->assertDontSee(['House Number', 'Yes, this is my Tower!', 'Start over tower search', 'Floor']);
});

test('registration page after street selection: house number field becomes visible', function () {
    $city = City::factory()->create();
    $street = Street::factory()->create(['city_id' => $city->id]);

    $this->get(route('geo.towers', ['street' => $street->id]))
        ->assertStatus(200);

    $this->get('/register')
        ->assertDontSee(['Yes, this is my Tower!', 'Start over tower search', 'Floor']);
});

test('registration page after tower found: tower confirmation ui is active', function () {
    $city = City::factory()->create();
    $street = Street::factory()->create(['city_id' => $city->id]);
    $tower = Tower::factory()->create(['street_id' => $street->id, 'house_number' => '42']);

    $this->get(route('geo.towers', ['street' => $street->id, 'house_number' => '42']))
        ->assertStatus(200)
        ->assertJsonFragment(['found' => true]);

    $this->get('/register')
        ->assertStatus(200);
});

test('registration page after confirming tower: personal details become visible', function () {
    $this->get('/register')->assertStatus(200);
});
