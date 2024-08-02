<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BathroomController;
use Inertia\Inertia;

// Route::get('/', function () {
//     return inertia::render('bathrooms');
// });

Route::get('/', [BathroomController::class, 'bathrooms']); //-> name('bathrooms');

Route::post('/', [BathroomController::class, 'store']);

Route::post('/', [BathroomController::class, 'store']);//->name('bathrooms.store');

Route::put('/{bathroom}', [BathroomController::class, 'update']);//->name('bathrooms.update');

//Route::get('/{bathroom}', [BathroomController::class, 'edit']);

//Route::put('/{bathroom}', [BathroomController::class, 'update']);

//Route::delete('/{bathroom}', [BathroomController::class, 'destroy']);