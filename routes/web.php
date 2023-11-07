<?php

use App\Models\Quote;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\LikesController;
use App\Http\Controllers\QuotesController;
use App\Http\Controllers\UserController;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


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

Route::prefix('api')->group(function () {

    Route::get('/quotes-list', [QuotesController::class, 'index']);
    Route::get('/quotes/search', [QuotesController::class, 'fetchSearchResults']);

    Route::get('/check-logged-in', function () {
        if (Auth::check()) {
            $user = Auth::user();
            return response()->json([
                'loggedIn' => true,
                'user' => $user,
                'csrfToken' => csrf_token(),
                'roles' => $user->roles->pluck('name')
            ]);
        } else {
            return response()->json(['loggedIn' => false]);
        }
    });

    Route::post('quotes/{quote}/like', [LikesController::class, 'store'])->name('quotes.like');
    Route::delete('quotes/{quote}/unlike', [LikesController::class, 'destroy'])->name('quotes.unlike');
    Route::get('/user/liked-quotes', [LikesController::class, 'likedQuotes']);

    Route::get('/logout', function () {
        Auth::logout();
        return response()->json(['message' => 'Logged out successfully']);
    });
});

Route::get('login/google', [GoogleController::class, 'redirectToProvider']);
Route::get('login/google/callback', [GoogleController::class, 'handleProviderCallback']);

Route::get('/assign-role', [UserController::class, 'assignRole']);

Route::middleware(['role:admin'])->group(function () {
    Route::get('/quote/{quote}', [QuotesController::class, 'show'])->name('quotes.show');
    Route::post('/api/quotes', [QuotesController::class, 'store'])->name('quotes.store');
    Route::put('/quote/{quote}', [QuotesController::class, 'update'])->name('quotes.update');
    Route::delete('/quote/{quote}', [QuotesController::class, 'destroy'])->name('quotes.destroy');
});


/* ============== */

Route::get('/create-role', function () {
    // Create roles
    $adminRole = Role::create(['name' => 'admin']);

    // Create permissions
    $manageUsers = Permission::create(['name' => 'manage users']);
    $editQuotes = Permission::create(['name' => 'manage quotes']);

    // Assign permissions to roles
    $adminRole->givePermissionTo($manageUsers);
    $adminRole->givePermissionTo($editQuotes);

    dd('success ...');
});
