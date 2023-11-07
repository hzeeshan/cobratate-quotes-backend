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
        $pageSize = 10;
        //sleep(1);
        $quotes = Quote::withCount('likedByUsers')
            ->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $pageSize)->take($pageSize)->get();

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
            'content' => 'required|string|max:1000', // Example validation rules
            // Add other fields if necessary
        ]);

        // Create a new quote with the validated data
        $quote = Quote::create([
            'content' => $data['content'],
            // Set other fields if necessary
            //'user_id' => Auth::id(), // Assuming you want to save the ID of the admin who created the quote
        ]);

        // Return a success response
        return response()->json([
            'success' => true,
            'message' => 'Quote saved successfully',
            'quote' => $quote,
        ], 201);
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
