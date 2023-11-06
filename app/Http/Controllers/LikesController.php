<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quote;
use Illuminate\Support\Facades\Auth;

class LikesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store($quoteId)
    {
        if (!Quote::where('id', $quoteId)->exists()) {
            return response()->json(['message' => 'Quote not found.'], 404);
        }

        Auth::user()->likedQuotes()->attach($quoteId);

        return response()->json(['success' => true, 'message' => 'Successfully liked the quote.']);
    }

    public function destroy($quoteId)
    {
        if (!Quote::where('id', $quoteId)->exists()) {
            return response()->json(['message' => 'Quote not found.'], 404);
        }

        Auth::user()->likedQuotes()->detach($quoteId);

        return response()->json(['success' => true, 'message' => 'Successfully unliked the quote.']);
    }

    public function likedQuotes(Request $request)
    {
        $user = Auth::user();

        $likedQuotes = $user->likedQuotes()
            ->with('likedByUsers:id,name')
            ->get(['quotes.id', 'quotes.content', 'quotes.source', 'quotes.category']); // Prefix 'quotes.'

        return response()->json($likedQuotes);
    }
}
