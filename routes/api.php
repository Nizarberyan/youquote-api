<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImageController;

// User profile route
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Auth routes - public
Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('login', [AuthController::class, 'login'])->name('login');

// Auth routes - protected
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});

// Public Quote routes - accessible without authentication
Route::get('quotes', [QuoteController::class, 'index'])->name('quotes.index');
Route::get('quotes/random', [QuoteController::class, 'random'])->name('quotes.random');
Route::get('quotes/popular', [QuoteController::class, 'popular'])->name('quotes.popular');
Route::get('quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');

// Protected Quote routes
Route::middleware(['auth:sanctum', 'json.response'])->group(function () {
    // Create, update, delete operations should be protected
    Route::post('quotes', [QuoteController::class, 'store'])->name('quotes.store');
    Route::put('quotes/{quote}', [QuoteController::class, 'update'])->name('quotes.update');
    Route::patch('quotes/{quote}', [QuoteController::class, 'update']);
    Route::delete('quotes/{quote}', [QuoteController::class, 'destroy'])->name('quotes.destroy');
});
