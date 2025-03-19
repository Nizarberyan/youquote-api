<?php

namespace App\Policies;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class QuotePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true; // Anyone can view quotes (visitor permission)
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Quote $quote): bool
    {
        return true; // Anyone can view a quote (visitor permission)
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create quotes
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Quote $quote): bool
    {
        // Users can update their own quotes, admins can update any quote
        return $user->id === $quote->user_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Quote $quote): bool
    {
        // Users can delete their own quotes, admins can delete any quote
        return $user->id === $quote->user_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Quote $quote): bool
    {
        return $user->isAdmin(); // Only admins can restore quotes
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Quote $quote): bool
    {
        return $user->isAdmin(); // Only admins can permanently delete quotes
    }

    /**
     * Determine whether the user can like a quote.
     */
    public function like(User $user, Quote $quote): bool
    {
        return true; // All authenticated users can like quotes
    }

    /**
     * Determine whether the user can favorite a quote.
     */
    public function favorite(User $user, Quote $quote): bool
    {
        return true; // All authenticated users can favorite quotes
    }

    /**
     * Determine whether the user can add tags to a quote.
     */
    public function addTags(User $user, Quote $quote): bool
    {
        // Users can add tags to their own quotes, admins can add tags to any quote
        return $user->id === $quote->user_id || $user->isAdmin();
    }
}
