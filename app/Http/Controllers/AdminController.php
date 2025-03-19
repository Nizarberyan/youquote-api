<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
            abort(403, 'Unauthorized. Admin access required.');
        }
    }
    
    /**
     * List all users (admin only)
     */
    public function listUsers()
    {
        $this->checkAdmin();
        
        $users = User::all();
        return response()->json($users);
    }
    
    /**
     * Change user role (admin only)
     */
    public function changeRole(Request $request, User $user)
    {
        $this->checkAdmin();
        
        $validated = $request->validate([
            'role' => 'required|string|in:user,moderator,admin',
        ]);
        
        $user->update(['role' => $validated['role']]);
        
        return response()->json($user);
    }
    
    /**
     * List all quotes (admin only)
     */
    public function listAllQuotes()
    {
        $this->checkAdmin();
        
        $quotes = Quote::with('user')->get();
        return response()->json($quotes);
    }
    
    /**
     * Delete any quote (admin only)
     */
    public function deleteQuote(Quote $quote)
    {
        $this->checkAdmin();
        
        $quote->delete();
        
        return response()->json(['message' => 'Quote deleted successfully'], 200);
    }
    
    /**
     * List deleted quotes (admin only)
     */
    public function listDeletedQuotes()
    {
        $this->checkAdmin();
        
        $deletedQuotes = Quote::onlyTrashed()->with('user')->get();
        return response()->json($deletedQuotes);
    }
    
    /**
     * Restore a deleted quote (admin only)
     */
    public function restoreQuote($id)
    {
        $this->checkAdmin();
        
        $quote = Quote::withTrashed()->findOrFail($id);
        $quote->restore();
        
        return response()->json(['message' => 'Quote restored successfully', 'quote' => $quote]);
    }
}
