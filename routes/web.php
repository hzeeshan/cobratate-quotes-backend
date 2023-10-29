<?php

use App\Models\Quote;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/logout', function () {
    Auth::logout();
    return redirect('/dev');
});

Route::get('/dev', function () {
    dd(Auth::check());
});

Route::get('/import-csv', function () {
    $file = storage_path('app/data/quotes.csv');

    $data = [];

    if (($handle = fopen($file, 'r')) !== false) {
        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $data[] = $row[0]; // Since there's only one column, we can access it directly
        }
        fclose($handle);
    }

    // Insert data into the database
    foreach ($data as $quoteContent) {
        Quote::create([
            'content' => $quoteContent,
        ]);
    }

    dd("Data imported successfully!");
});

Route::get('login/google', [GoogleController::class, 'redirectToProvider']);
Route::get('login/google/callback', [GoogleController::class, 'handleProviderCallback']);
