<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\HasApiTokens;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }
    
    /**
     * Check if user is admin
     */
    private function checkAdmin()
    {
        if (! Gate::allows('admin')) {
            // Instead of abort(), throw the correct exception type
            throw new \Illuminate\Auth\Access\AuthorizationException('Unauthorized. Admin access required. Your current role does not have permission to perform this action.');
        }
    }
    
    /**
     * List all users (admin only)
     */
    public function listUsers()
    {
        try {
            $this->checkAdmin();
            
            $users = User::all();
            
            if ($users->isEmpty()) {
                return response()->json([
                    'message' => 'No users found in the system',
                    'users' => []
                ]);
            }
            
            return response()->json([
                'message' => 'Users retrieved successfully',
                'count' => $users->count(),
                'users' => $users
            ]);
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return response()->json([
                    'error' => 'Permission denied',
                    'message' => 'You do not have the required permissions to view all users'
                ], 403);
            }
            
            return response()->json([
                'error' => 'Server error',
                'message' => 'Failed to retrieve users due to a server error'
            ], 500);
        }
    }
    
    /**
     * Change user role (admin only)
     */
    public function changeRole(Request $request, User $user)
    {
        try {
            $this->checkAdmin();
            
            try {
                $validated = $request->validate([
                    'role' => 'required|string|in:user,moderator,admin',
                ]);
            } catch (ValidationException $e) {
                return response()->json([
                    'error' => 'Validation failed',
                    'message' => 'The role provided is invalid. Valid roles are: user, moderator, admin',
                    'details' => $e->errors()
                ], 422);
            }
            
            // Prevent changing own role to avoid lockout
            if ($user->id === auth()->id() && $validated['role'] !== 'admin') {
                return response()->json([
                    'error' => 'Operation not allowed',
                    'message' => 'You cannot change your own admin role for security reasons'
                ], 403);
            }
            
            $oldRole = $user->role;
            $user->update(['role' => $validated['role']]);
            
            return response()->json([
                'message' => "User role updated successfully from '{$oldRole}' to '{$validated['role']}'",
                'user' => $user
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'User not found',
                'message' => 'The specified user could not be found'
            ], 404);
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return response()->json([
                    'error' => 'Permission denied',
                    'message' => 'You do not have the required permissions to change user roles'
                ], 403);
            }
            
            return response()->json([
                'error' => 'Server error',
                'message' => 'Failed to update user role due to a server error'
            ], 500);
        }
    }
    
    /**
     * List all quotes (admin only)
     */
    public function listAllQuotes()
    {
        try {
            $this->checkAdmin();
            
            $quotes = Quote::with('user')->get();
            
            if ($quotes->isEmpty()) {
                return response()->json([
                    'message' => 'No quotes found in the system',
                    'quotes' => []
                ]);
            }
            
            return response()->json([
                'message' => 'Quotes retrieved successfully',
                'count' => $quotes->count(),
                'quotes' => $quotes
            ]);
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return response()->json([
                    'error' => 'Permission denied',
                    'message' => 'You do not have the required permissions to view all quotes'
                ], 403);
            }
            
            return response()->json([
                'error' => 'Server error',
                'message' => 'Failed to retrieve quotes due to a server error'
            ], 500);
        }
    }
    
    /**
     * Delete any quote (admin only)
     */
    public function deleteQuote(Quote $quote)
    {
        try {
            $this->checkAdmin();
            
            $quoteInfo = [
                'id' => $quote->id,
                'author' => $quote->author,
                'preview' => substr($quote->content, 0, 50) . (strlen($quote->content) > 50 ? '...' : '')
            ];
            
            $quote->delete();
            
            return response()->json([
                'message' => 'Quote deleted successfully',
                'quote' => $quoteInfo
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Quote not found',
                'message' => 'The specified quote could not be found'
            ], 404);
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return response()->json([
                    'error' => 'Permission denied',
                    'message' => 'You do not have the required permissions to delete quotes'
                ], 403);
            }
            
            return response()->json([
                'error' => 'Server error',
                'message' => 'Failed to delete quote due to a server error'
            ], 500);
        }
    }
    
    /**
     * List deleted quotes (admin only)
     */
    public function listDeletedQuotes()
    {
        try {
            $this->checkAdmin();
            
            $deletedQuotes = Quote::onlyTrashed()->with('user')->get();
            
            if ($deletedQuotes->isEmpty()) {
                return response()->json([
                    'message' => 'No deleted quotes found',
                    'quotes' => []
                ]);
            }
            
            return response()->json([
                'message' => 'Deleted quotes retrieved successfully',
                'count' => $deletedQuotes->count(),
                'quotes' => $deletedQuotes
            ]);
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return response()->json([
                    'error' => 'Permission denied',
                    'message' => 'You do not have the required permissions to view deleted quotes'
                ], 403);
            }
            
            return response()->json([
                'error' => 'Server error',
                'message' => 'Failed to retrieve deleted quotes due to a server error'
            ], 500);
        }
    }
    
    /**
     * Restore a deleted quote (admin only)
     */
    public function restoreQuote($id)
    {
        try {
            $this->checkAdmin();
            
            try {
                $quote = Quote::withTrashed()->findOrFail($id);
            } catch (ModelNotFoundException $e) {
                return response()->json([
                    'error' => 'Quote not found',
                    'message' => 'The specified quote does not exist or is not deleted'
                ], 404);
            }
            
            if (!$quote->trashed()) {
                return response()->json([
                    'error' => 'Quote not deleted',
                    'message' => 'This quote is not deleted and does not need restoration'
                ], 422);
            }
            
            $quote->restore();
            
            return response()->json([
                'message' => 'Quote restored successfully',
                'quote' => $quote
            ]);
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return response()->json([
                    'error' => 'Permission denied',
                    'message' => 'You do not have the required permissions to restore quotes'
                ], 403);
            }
            
            return response()->json([
                'error' => 'Server error',
                'message' => 'Failed to restore quote due to a server error'
            ], 500);
        }
    }
    
    /**
     * Restore all deleted quotes (admin only)
     */
    public function restoreAllQuotes()
    {
        try {
            $this->checkAdmin();
            
            $count = Quote::onlyTrashed()->count();
            
            if ($count === 0) {
                return response()->json([
                    'message' => 'No deleted quotes to restore'
                ]);
            }
            
            Quote::onlyTrashed()->restore();
            
            return response()->json([
                'message' => 'All quotes restored successfully',
                'count' => $count
            ]);
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return response()->json([
                    'error' => 'Permission denied',
                    'message' => 'You do not have the required permissions to restore quotes'
                ], 403);
            }
            
            return response()->json([
                'error' => 'Server error',
                'message' => 'Failed to restore quotes due to a server error'
            ], 500);
        }
    }
    
    /**
     * Permanently delete a quote (admin only)
     * 
     * @param int $id The ID of the quote to force delete
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceDeleteQuote($id)
    {
        try {
            $this->checkAdmin();
            
            try {
                $quote = Quote::withTrashed()->findOrFail($id);
            } catch (ModelNotFoundException $e) {
                return response()->json([
                    'error' => 'Quote not found',
                    'message' => 'The specified quote could not be found in the system'
                ], 404);
            }
            
            // Store some details for the response
            $quoteDetails = [
                'id' => $quote->id,
                'content_preview' => substr($quote->content, 0, 50) . (strlen($quote->content) > 50 ? '...' : ''),
                'author' => $quote->author,
                'was_deleted' => $quote->trashed()
            ];
            
            // Permanently delete the quote
            $quote->forceDelete();
            
            return response()->json([
                'message' => 'Quote permanently deleted from the database',
                'quote' => $quoteDetails
            ], 200);
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return response()->json([
                    'error' => 'Permission denied',
                    'message' => 'You do not have the required permissions to permanently delete quotes'
                ], 403);
            }
            
            return response()->json([
                'error' => 'Server error',
                'message' => 'Failed to permanently delete quote due to a server error'
            ], 500);
        }
    }
    
    /**
     * Permanently delete all soft-deleted quotes (admin only)
     */
    public function forceDeleteAllQuotes()
    {
        try {
            $this->checkAdmin();
            
            $count = Quote::onlyTrashed()->count();
            
            if ($count === 0) {
                return response()->json([
                    'message' => 'No deleted quotes to permanently remove'
                ]);
            }
            
            Quote::onlyTrashed()->forceDelete();
            
            return response()->json([
                'message' => 'All deleted quotes have been permanently removed from the database',
                'count' => $count
            ]);
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return response()->json([
                    'error' => 'Permission denied',
                    'message' => 'You do not have the required permissions to permanently delete quotes'
                ], 403);
            }
            
            return response()->json([
                'error' => 'Server error',
                'message' => 'Failed to permanently delete quotes due to a server error',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
