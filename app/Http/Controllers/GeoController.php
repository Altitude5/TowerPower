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
     * Get towers on a given street, optionally filtered by house_number.
     */
    public function towersByStreet(Request $request, Street $street): JsonResponse
    {
        $houseNumber = $request->query('house_number');

        if ($houseNumber) {
            $tower = $street->towers()->where('house_number', trim(strtoupper($houseNumber)))->first();

            return response()->json([
                'found' => (bool) $tower,
                'tower' => $tower,
                'street_name' => $street->name,
            ]);
        }

        return response()->json($street->towers()->orderBy('name')->get(['id', 'name', 'house_number', 'image_path']));
    }

    /**
     * Store a new tower.
     */
    public function storeTower(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:120',
            'city_id' => 'required|exists:cities,id',
            'street_id' => 'required|exists:streets,id',
            'house_number' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,gif,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $houseNumber = trim(strtoupper($request->house_number));

        // Handle race condition
        $tower = Tower::where('street_id', $request->street_id)
            ->where('house_number', $houseNumber)
            ->first();

        if ($tower) {
            return response()->json($tower, 200);
        }

        $data = [
            'name' => $request->name ?? "Tower at {$houseNumber}",
            'city_id' => $request->city_id,
            'street_id' => $request->street_id,
            'house_number' => $houseNumber,
        ];

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('towers', 'public');
        }

        $tower = Tower::create($data);

        return response()->json($tower, 201);
    }
}
