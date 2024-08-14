<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Http;
use App\Models\Bathroom;
use Inertia\Inertia;

class BathroomController extends Controller
{
    
    public function bathrooms(){
        return inertia::render('bathrooms', [
            'bathrooms' => Bathroom::all() -> map(function($bathroom){
                return [
                    'id' => $bathroom -> id,
                    'name' => $bathroom -> name,
                    'location'=> $bathroom -> location,
                    'code' => $bathroom -> code,
                    'note' => $bathroom -> note,
                    'latitude' => $bathroom->latitude,
                    'longitude' => $bathroom->longitude,
                ];
            })
        ]);
    }

    public function store(Request $request){

        $validated = $request -> validate([
            'name' => 'required|max:255',
            'location' => 'required|max:255',
            'code' => 'nullable|numeric',
            'note' => 'nullable|max:255'
        ]);

        $existingBathroom = Bathroom::where('location', $validated['location'])->first(); //

        if ($existingBathroom) {
            return Inertia::render('bathrooms', [
                'bathrooms' => Bathroom::all()->map(function($bathroom) {
                    return [
                        'id' => $bathroom->id,
                        'name' => $bathroom->name,
                        'location'=> $bathroom->location,
                        'code' => $bathroom->code,
                        'note' => $bathroom->note,
                        'latitude' => $bathroom->latitude,
                        'longitude' => $bathroom->longitude,
                    ];
                }),
                'duplicate' => true,
                'existingEntry' => $existingBathroom
            ]);
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'CrapMap/1.0 (angeloferrara12@email.com)'
            ])->get('https://nominatim.openstreetmap.org/search', [
                'q' => $validated['location'],
                'format' => 'json',
                'limit' => 1,
            ]);

            if ($response->successful()) {
                $coordinates = $response->json();
                
                if (!empty($coordinates)) {
                    $location = $coordinates[0];
                    $latitude = $location['lat'];
                    $longitude = $location['lon'];
                } else {
                    $latitude = null;
                    $longitude = null;
                    // Log or report that no coordinates were found for the given location
                    \Log::warning("No coordinates found for location: " . $validated['location']);
                }
            } else {
                $latitude = null;
                $longitude = null;
                // Log or report the unsuccessful response
                \Log::error("Unsuccessful response from Nominatim API: " . $response->status());
                \Log::debug("Nominatim API response: " . $response->body());
            }
        } catch (\Exception $e) {
            $latitude = null;
            $longitude = null;
            // Log or report the exception
            \Log::error("Error fetching coordinates: " . $e->getMessage());
        }

        \Log::debug("Location: " . $validated['location'] . ", Latitude: " . $latitude . ", Longitude: " . $longitude);

        Bathroom::create(array_merge($validated, ['latitude' => $latitude, 'longitude' => $longitude]));

        return redirect('/');

    }

    public function update(Request $request, Bathroom $bathroom)
    {
        $validated = $request->validate([
            'code' => 'nullable|numeric',
            'note' => 'nullable|max:255'
        ]);

        $bathroom->update($validated);

        return redirect('/');
    }

}
