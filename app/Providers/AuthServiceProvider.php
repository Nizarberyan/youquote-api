<?php

namespace App\Providers;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Quote::class => \App\Policies\QuotePolicy::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Define roles-based gates
        Gate::define('admin', function (User $user) {
            return $user->isAdmin();
        });

        // Registered user permissions
        Gate::define('create-quote', function (User $user) {
            return true; // All registered users can create quotes
        });
        
        Gate::define('update-own-quote', function (User $user, Quote $quote) {
            return $user->id === $quote->user_id;
        });
        
        Gate::define('delete-own-quote', function (User $user, Quote $quote) {
            return $user->id === $quote->user_id;
        });
        
        // Admin permissions
        Gate::define('manage-all-quotes', function (User $user) {
            return $user->isAdmin();
        });
        
        Gate::define('restore-quotes', function (User $user) {
            return $user->isAdmin();
        });
    }
}
