<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Street;
use App\Models\Tower;
use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GeoController extends Controller
{
    /**
     * Get all streets in a given city.
     */
    public function streetsByCity(City $city): JsonResponse
    {
        return response()->json($city->streets()->orderBy('name')->get(['id', 'name']));
    }

    /**
     * Get all towers on a given street.
     */
    public function towersByStreet(Street $street): JsonResponse
    {
        return response()->json($street->towers()->orderBy('name')->get(['id', 'name', 'house_number', 'image_path']));
    }

    /**
     * Store a new tower.
     */
    public function storeTower(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
            'street_id' => 'required|exists:streets,id',
            'house_number' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tower = Tower::create($request->only(['name', 'city_id', 'street_id', 'house_number']));

        return response()->json($tower, 201);
    }
}
