<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class QuotesController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $categoryName = $request->get('category');

        $pageSize = 10;

        $quotesQuery = Quote::withCount('likedByUsers')
            ->orderBy('id', 'desc');

        $quotesQuery = $quotesQuery->whereHas('category', function ($query) use ($categoryName) {
            $query->where('name', $categoryName);
        });

        $quotes = $quotesQuery->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get();

        // If the user is authenticated, augment each quote with the isLikedByUser property
        if (Auth::check()) {
            $likedQuoteIds = Auth::user()->likedQuotes()->pluck('quotes.id')->toArray();

            $quotes->transform(function ($quote) use ($likedQuoteIds) {
                $quote->isLikedByUser = in_array($quote->id, $likedQuoteIds);
                return $quote;
            });
        }

        return $quotes;
    }


    public function show($quoteId)
    {
        // Fetch the quote by id
        $quote = Quote::find($quoteId);

        // If the quote doesn't exist, return a 404 error
        if (!$quote) {
            return response()->json(['message' => 'Quote not found.'], 404);
        }

        // Return the quote details
        return response()->json($quote);
    }

    public function update(Request $request, Quote $quote)
    {
        // Validate the request data
        $data = $request->validate([
            'content' => 'required|string|max:1000', // Example validation rules
            // Add other fields if necessary
        ]);

        // Update the quote with the validated data
        $quote->update([
            'content' => $data['content'],
            'category_id' => empty($request->category) ? null : $request->category,
        ]);

        // Return a success response
        return response()->json([
            'success' => true,
            'message' => 'Quote updated successfully',
            'quote' => $quote,
        ]);
    }

    public function store(Request $request)
    {
        // Validate the request data
        $data = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        // Create a new quote with the validated data
        $quote = Quote::firstOrCreate(
            ['content' => $data['content']],
            [
                'content' => $data['content'],
                'category_id' => empty($request->category) ? null : $request->category,

            ]
        );

        if ($quote->wasRecentlyCreated) {
            return response()->json([
                'success' => true,
                'message' => 'Quote saved successfully',
                'quote' => $quote,
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'This quote already exists',
            ], 409);
        }
    }

    public function destroy(Quote $quote)
    {
        // Delete the quote
        $quote->delete();

        // Return a success response
        return response()->json([
            'success' => true,
            'message' => 'Quote deleted successfully',
        ]);
    }

    function fetchSearchResults(Request $request)
    {
        $query = $request->input('query');

        $results = Quote::where('content', 'like', '%' . $query . '%')->get();

        return response()->json($results);
    }
}
