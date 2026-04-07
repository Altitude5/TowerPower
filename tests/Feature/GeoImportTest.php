<?php

use App\Models\City;
use App\Models\Street;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('imports cities and streets from a CSV file', function () {
    $csvContent = "city_code,city_name,street_code,street_name\n" .
                  "70,Tel Aviv,1001,Dizengoff\n" .
                  "70,Tel Aviv,1002,Ibn Gabirol\n" .
                  "80,Haifa,2001,Herzl";
    
    $path = storage_path('geo_import_test.csv');
    File::put($path, $csvContent);

    Artisan::call('geo:import', ['file' => $path]);

    $this->assertDatabaseHas('cities', ['code' => '70', 'name' => 'Tel Aviv']);
    $this->assertDatabaseHas('cities', ['code' => '80', 'name' => 'Haifa']);
    
    $telAviv = City::where('code', '70')->first();
    $haifa = City::where('code', '80')->first();
    
    $this->assertDatabaseHas('streets', ['name' => 'Dizengoff', 'city_id' => $telAviv->id]);
    $this->assertDatabaseHas('streets', ['name' => 'Ibn Gabirol', 'city_id' => $telAviv->id]);
    $this->assertDatabaseHas('streets', ['name' => 'Herzl', 'city_id' => $haifa->id]);

    File::delete($path);
});

it('updates city name if code already exists', function () {
    City::create(['code' => '70', 'name' => 'Old Name']);
    
    $csvContent = "city_code,city_name,street_code,street_name\n" .
                  "70,New Name,1001,Dizengoff";
    
    $path = storage_path('geo_update_test.csv');
    File::put($path, $csvContent);

    Artisan::call('geo:import', ['file' => $path]);

    $this->assertDatabaseHas('cities', ['code' => '70', 'name' => 'New Name']);
    $this->assertDatabaseMissing('cities', ['name' => 'Old Name']);

    File::delete($path);
});
