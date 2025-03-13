<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuoteController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Put specific routes BEFORE the resource route
Route::get('quotes/random', [QuoteController::class, 'random']);
Route::get('quotes/popular', [QuoteController::class, 'popular']);

// Then define the resource route
Route::apiResource('quotes', QuoteController::class);
