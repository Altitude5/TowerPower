<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $user = User::factory()->create();
    $response = $this->get(route('user.dashboard', ['user' => $user->id]));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('user.dashboard', ['user' => $user->id]));
    $response->assertOk();
});