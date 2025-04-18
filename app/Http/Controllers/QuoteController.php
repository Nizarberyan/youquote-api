<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuoteRequest;
use App\Http\Requests\UpdateQuoteRequest;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class QuoteController extends Controller
{
    public function __construct()
    {
        // Apply authorization to certain methods
        $this->authorizeResource(Quote::class, 'quote', [
            'except' => ['index', 'show', 'random', 'popular']
        ]);
    }

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

            // Apply filters based on request parameters
            if ($request->has('min_length')) {
                $minLength = $request->min_length;
                \Log::info('min_length: ' . $minLength);

                if (!is_numeric($minLength) || $minLength < 0) {
                    return response()->json(['error' => 'min_length must be a non-negative number'], 400);
                }

                $query->where('length', '>=', $minLength);
            }

            if ($request->has('max_length')) {
                $maxLength = $request->max_length;
                \Log::info('max_length: ' . $maxLength);

                if (!is_numeric($maxLength) || $maxLength < 0) {
                    return response()->json(['error' => 'max_length must be a non-negative number'], 400);
                }

                $query->where('length', '<=', $maxLength);
            }

            // Eager load the 'user' relationship to get the user who created each quote
            $query->with('user');

            \Log::info('Final Query: ' . $query->toSql());

            // Get all quotes
            $quotes = $query->get();

            // Transform the quotes to match the desired structure
            $quotesData = $quotes->map(function ($quote) use ($request) {
                return [
                    'quote' => [
                        'id' => $quote->id,
                        'content' => $quote->content,
                        'author' => $quote->author,
                        'length' => $quote->length,
                        'popularity_count' => $quote->popularity_count,
                        'created_at' => $quote->created_at,
                        'updated_at' => $quote->updated_at,
                        'user_id' => $quote->user_id,
                        'deleted_at' => $quote->deleted_at,
                        'user' => auth('sanctum')->check() && auth('sanctum')->user()->role === 'admin' && $quote->user ? [
                            'id' => $quote->user->id,
                            'name' => $quote->user->name,
                            'email' => $quote->user->email,
                            'email_verified_at' => $quote->user->email_verified_at,
                            'created_at' => $quote->user->created_at,
                            'updated_at' => $quote->user->updated_at,
                            'role' => $quote->user->role,
                        ] : null,
                    ],
                ];
            });

            // Return the transformed quotes
            return response()->json($quotesData);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreQuoteRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreQuoteRequest $request)
    {

        $validated = $request->validated();

        $validated['length'] = str_word_count($validated['content']);
        $validated['popularity_count'] = 0;
        $validated['user_id'] = auth()->id();

        $quote = Quote::create($validated);


        return response()->json([
            'quote' => $quote,
            'user' => $quote->user
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Quote $quote)
    {
        $quote->increment('popularity_count');


        $quote->load('user');

        return response()->json([
            'quote' => $quote,
        ]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateQuoteRequest  $request
     * @param  \App\Models\Quote  $quote
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateQuoteRequest $request, Quote $quote)
    {
        // Authorization is handled by the controller constructor
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
        // Authorization handled by the controller constructor

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

    /**
     * Like a quote.
     *
     * @param  \App\Models\Quote  $quote
     * @return \Illuminate\Http\JsonResponse
     */
    public function like(Quote $quote)
    {
        $this->authorize('like', $quote);

        auth()->user()->likedQuotes()->toggle($quote->id);

        return response()->json(['liked' => auth()->user()->likedQuotes()->where('quote_id', $quote->id)->exists()]);
    }

    /**
     * Favorite a quote.
     *
     * @param  \App\Models\Quote  $quote
     * @return \Illuminate\Http\JsonResponse
     */
    public function favorite(Quote $quote)
    {
        $this->authorize('favorite', $quote);

        auth()->user()->favoriteQuotes()->toggle($quote->id);

        return response()->json(['favorited' => auth()->user()->favoriteQuotes()->where('quote_id', $quote->id)->exists()]);
    }

    /**
     * Restore a deleted quote.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        $quote = Quote::withTrashed()->findOrFail($id);

        $this->authorize('restore', $quote);

        $quote->restore();

        return response()->json($quote);
    }
}
