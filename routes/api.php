<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
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


Route::get('/quotes-list', [QuotesController::class, 'index']);

Route::middleware('web')->get('/check-logged-in', function () {
    return response()->json(['loggedIn' => Auth::check()]);
});

Route::middleware('web')->get('/logout', function () {
    Auth::logout();
    return response()->json(['message' => 'Logged out successfully']);
});
