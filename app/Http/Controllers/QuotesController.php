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
        sleep(1);
        $quotes = Quote::withCount('likedByUsers')->skip(($page - 1) * $pageSize)->take($pageSize)->get(); // Add the `withCount` method to get the number of likes

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
}
