<?php

use Illuminate\Support\Facades\Route;
use App\Models\Quote;

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
