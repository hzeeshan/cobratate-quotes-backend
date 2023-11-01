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

        return response()->json(['message' => 'Successfully liked the quote.']);
    }

    public function destroy($quoteId)
    {
        if (!Quote::where('id', $quoteId)->exists()) {
            return response()->json(['message' => 'Quote not found.'], 404);
        }

        Auth::user()->likedQuotes()->detach($quoteId);

        return response()->json(['message' => 'Successfully unliked the quote.']);
    }
}
