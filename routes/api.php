<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuoteController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('quotes', QuoteController::class);
Route::get('quotes/random', [QuoteController::class, 'random']);
Route::get('quotes/popular', [QuoteController::class, 'popular']);
