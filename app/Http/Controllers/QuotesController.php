<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class QuotesController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = 10;

        sleep(1);
        $quotes = Quote::skip(($page - 1) * $pageSize)->take($pageSize)->get();

        return $quotes;
    }
}
