<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quote;

class QuoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Quote::query();
    
        if ($request->has('min_length')) {
            $query->where('length', '>=', $request->min_length);
        }
        
        if ($request->has('max_length')) {
            $query->where('length', '<=', $request->max_length);
        }
        
        return response()->json($query->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|min:3',
            'author' => 'nullable|string|max:255',
        ]);
        
        // Calculate the word length
        $validated['length'] = str_word_count($validated['content']);
        $validated['popularity_count'] = 0;
        
        $quote = Quote::create($validated);
        
        return response()->json($quote, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Quote $quote)
    {
        $quote->increment('popularity_count');
        return response()->json($quote);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Quote $quote)
    {
        $validated = $request->validate([
            'content' => 'sometimes|required|string|min:3',
            'author' => 'nullable|string|max:255',
        ]);
        
        // If content was updated, recalculate length
        if (isset($validated['content'])) {
            $validated['length'] = str_word_count($validated['content']);
        }
        
        $quote->update($validated);
        
        return response()->json($quote);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quote $quote)
    {
        $quote->delete();
        
        return response()->json(null, 204);
    }

    /**
     * Get random quotes.
     */
    public function random(Request $request)
    {
        $count = $request->input('count', 1);
        $quotes = Quote::inRandomOrder()->limit($count)->get();
        return response()->json($quotes);
    }

    /**
     * Get the most popular quotes.
     */
    public function popular(Request $request)
    {
        $limit = $request->input('limit', 10);
        $quotes = Quote::orderBy('popularity_count', 'desc')
                       ->limit($limit)
                       ->get();
        return response()->json($quotes);
    }
}
