<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
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
                    'note' => $bathroom -> note
                ];
            })
        ]);
    }

    public function store(Request $request){
        $validated = $request -> validate([
            'name' => 'required|max:255',
            'location' => 'required|max:255|unique:bathrooms',
            'code' => 'nullable|numeric',
            'note' => 'nullable|max:255'
        ]);

        Bathroom::create($validated);

        return redirect('/') -> with('message', 'Bathroom Created');

    }

    //public function edit(Bathroom $bathroom){
    
        //return $bathroom;

        // return Inertia::render('edit', [
        //     'bathrooms' => Bathroom::all() -> map(function($bathroom){
        //         return [
        //             'id' => $bathroom -> id,
        //             'name' => $bathroom -> name,
        //             'location'=> $bathroom -> location,
        //             'code' => $bathroom -> code,
        //             'note' => $bathroom -> note
        //         ];
        //     })
        // ]);
    //}

    // public function update(Request $request, Bathroom $bathroom){
    //     $validated = $request -> validate([
    //         'name' => 'required|max:255', // do I need this on update?
    //         'location' => 'required|max:255', // do I need this on update?
    //         'code' => 'nullable|numeric',
    //         'note' => 'nullable|max:255'
    //     ]);

    //     $bathroom -> update($validated);

    //     return redirect('/') -> with('message', 'Bathroom code updated');
    // }

    // public function destroy(Bathroom $bathroom){
    //     $bathroom -> delete();

    //     return redirect('/') -> with('message', 'Bathroom deleted');
    // }

}
