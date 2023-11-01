<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LikesController;
use App\Http\Controllers\QuotesController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::middleware('web')->get('/quotes-list', [QuotesController::class, 'index']);

Route::middleware('web')->get('/check-logged-in', function () {
    if (Auth::check()) {
        $user = Auth::user();
        return response()->json(['loggedIn' => true, 'user' => $user, 'csrfToken' => csrf_token()]);
    } else {
        return response()->json(['loggedIn' => false]);
    }
});

Route::middleware('web')->group(function () {
    Route::post('quotes/{quote}/like', [LikesController::class, 'store'])->name('quotes.like');
    Route::delete('quotes/{quote}/unlike', [LikesController::class, 'destroy'])->name('quotes.unlike');
});


Route::middleware('web')->get('/logout', function () {
    Auth::logout();
    return response()->json(['message' => 'Logged out successfully']);
});
