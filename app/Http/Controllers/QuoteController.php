<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuoteRequest;
use App\Http\Requests\UpdateQuoteRequest;
use App\Models\Quote;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = Quote::query();
            
            // Filter by minimum length
            if ($request->has('min_length')) {
                $minLength = $request->min_length;
                
                if (!is_numeric($minLength) || $minLength < 0) {
                    return response()->json(['error' => 'min_length must be a non-negative number'], 400);
                }
                
                $query->where('length', '>=', $minLength);
            }
            
            // Filter by maximum length
            if ($request->has('max_length')) {
                $maxLength = $request->max_length;
                
                if (!is_numeric($maxLength) || $maxLength < 0) {
                    return response()->json(['error' => 'max_length must be a non-negative number'], 400);
                }
                
                $query->where('length', '<=', $maxLength);
            }
            
            // Add pagination
            $perPage = $request->input('per_page', 15);
                return response()->json($query->get());
        
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreQuoteRequest $request)
    {
        // The request is already validated thanks to StoreQuoteRequest
        $validated = $request->validated();
        
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
    public function update(UpdateQuoteRequest $request, Quote $quote)
    {
        // The request is already validated thanks to UpdateQuoteRequest
        $validated = $request->validated();
        
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
        try {
            $count = $request->input('count', 1);
         
            if (!is_numeric($count) || $count < 1) {
                return response()->json(['error' => 'Count must be a positive number'], 400);
            }
            
            $quotes = Quote::inRandomOrder()->limit($count)->get();
            
            if ($quotes->isEmpty()) {
                return response()->json(['message' => 'No quotes found'], 404);
            }
            
            return response()->json($quotes);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
