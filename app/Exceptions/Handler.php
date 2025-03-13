<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Throwable;
use Illuminate\Http\Request;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $doNotReport = [
        //
    ];

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $doNotFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // Use render to catch all authentication exceptions
        $this->render(function (AuthenticationException $e, Request $request) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You must be logged in to perform this action.',
                'details' => 'Please login or register to get an API token.'
            ], 401);
        });
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // Always return a JSON response
        return response()->json([
            'error' => 'Unauthorized',
            'message' => 'You must be logged in to perform this action.',
            'details' => 'Please login or register to get an API token.'
        ], 401);
    }
}
