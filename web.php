<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BathroomController;
use Inertia\Inertia;

Route::get('/', [BathroomController::class, 'bathrooms']); 

Route::post('/', [BathroomController::class, 'store']);

Route::post('/', [BathroomController::class, 'store']);

Route::put('/{bathroom}', [BathroomController::class, 'update']);