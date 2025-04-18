<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Quote;

class AuthorController extends Controller
{
    /**
     * Get all users with their quotes
     */
    public function index()
    {
        $authors = Quote::distinct('author')->pluck('author');
        return response()->json($authors, 200);
    }

public function show($authorName)
{
    // Decode URL-encoded characters and replace hyphens with spaces
    $authorName = urldecode($authorName);

    // Replace hyphens with spaces and handle special cases like O'Reilly
    $formattedAuthorName = str_replace('-', ' ', $authorName);

    // Normalize the author name (handle different variations)
    $variations = [
        $formattedAuthorName,
        ucwords($formattedAuthorName),
        trim($formattedAuthorName),
        ucwords(trim($formattedAuthorName)),
    ];

    // Try to find quotes with any of these variations
    $quotes = Quote::whereIn('author', $variations)
        ->with('user')  // Optional: eager load user information
        ->get();

    if ($quotes->isEmpty()) {
        return response()->json([
            'message' => 'No quotes found for this author',
            'author' => $authorName,
            'searched_variations' => $variations
        ], 404);
    }

    return response()->json([
        'author' => $quotes->first()->author, // Use the exact author name from the database
        'quotes' => $quotes
    ], 200);
}
}
