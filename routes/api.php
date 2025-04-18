<?php

use App\Http\Controllers\AuthorController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Models\User;
use Illuminate\Auth\Events\Verified;

use Illuminate\Auth\Access\AuthorizationException;

// User profile route with email verification status (alternative)
Route::get('/user', function (Request $request) {
    $user = $request->user();
    $userData = $user->toArray();
    $userData['email_verified'] = $user->hasVerifiedEmail();

    return response()->json($userData);
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
Route::get('authors', [AuthorController::class, 'index']);
Route::get('authors/{authorName}', [AuthorController::class, 'show']);

// Protected Quote routes requiring email verification
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    // Quote CRUD operations
    Route::post('quotes', [QuoteController::class, 'store'])->name('quotes.store');
    Route::put('quotes/{quote}', [QuoteController::class, 'update'])->name('quotes.update');
    Route::patch('quotes/{quote}', [QuoteController::class, 'update']);
    Route::delete('quotes/{quote}', [QuoteController::class, 'destroy'])->name('quotes.destroy');

    // Interactions
    Route::post('quotes/{quote}/like', [QuoteController::class, 'like'])->name('quotes.like');
    Route::post('quotes/{quote}/favorite', [QuoteController::class, 'favorite'])->name('quotes.favorite');
});

// Admin routes
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    // User management
    Route::get('/users', [AdminController::class, 'listUsers']);
    Route::patch('/users/{user}/role', [AdminController::class, 'changeRole']);

    // Admin quote management routes
    Route::get('/quotes', [AdminController::class, 'listAllQuotes'])->name('admin.quotes.index');
    Route::delete('/quotes/{quote}', [AdminController::class, 'deleteQuote'])->name('admin.quotes.delete');
    Route::post('/quotes/{id}/restore', [AdminController::class, 'restoreQuote'])->name('admin.quotes.restore');
    Route::get('/quotes/deleted', [AdminController::class, 'listDeletedQuotes'])->name('admin.quotes.deleted');
    Route::delete('/quotes/{id}/force', [AdminController::class, 'forceDeleteQuote'])->name('admin.quotes.force-delete');
    Route::post('/quotes/restore-all', [AdminController::class, 'restoreAllQuotes'])->name('admin.quotes.restore-all');
    Route::delete('/quotes/force-all', [AdminController::class, 'forceDeleteAllQuotes'])->name('admin.quotes.force-delete-all');
});

// Email verification routes
Route::post('/email/verification-notification', function (Request $request) {
    if ($request->user()->hasVerifiedEmail()) {
        return response()->json([
            'message' => 'Email already verified'
        ]);
    }

    $request->user()->sendEmailVerificationNotification();

    return response()->json([
        'message' => 'Verification link sent'
    ]);
})->middleware(['auth:sanctum', 'throttle:6,1'])->name('verification.send');

Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    try {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            throw new AuthorizationException('Invalid verification link');
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified'
            ]);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json([
            'message' => 'Email verified successfully'
        ]);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'error' => 'User not found',
            'message' => 'The verification link is invalid'
        ], 404);
    } catch (AuthorizationException $e) {
        return response()->json([
            'error' => 'Invalid verification link',
            'message' => $e->getMessage()
        ], 403);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Verification failed',
            'message' => 'An error occurred during email verification'
        ], 500);
    }
})->middleware(['throttle:6,1'])->name('verification.verify');

// Email verification status check
Route::middleware('auth:sanctum')->get('/email/verify/check', function (Request $request) {
    return response()->json([
        'verified' => $request->user()->hasVerifiedEmail(),
        'email' => $request->user()->email
    ]);
});
