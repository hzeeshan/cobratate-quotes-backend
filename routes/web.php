<?php

use App\Models\Quote;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\LikesController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\QuotesController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactFormController;
use App\Models\Category;

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

Route::get('insert-categories', function () {
    $categories = [
        ['id' => 1, 'name' => 'General'],
        ['id' => 2, 'name' => 'Fitness'],
    ];
    foreach ($categories as $category) {
        //print_r();
        Category::create([
            'id' => $category['id'],
            'name' => $category['name'],
            'createt_at' => now(),
            'updated_at' => now(),

        ]);
    }
});

Route::get('/import-csv-data', function () {
    $file = storage_path('app/data/data.csv');

    // Open the file
    if (($handle = fopen($file, 'r')) !== false) {
        // Skip the header row
        fgetcsv($handle, 0, ',');

        // Read each line of the CSV
        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            // Assuming the CSV columns are in the order: id, content, category_id, source, created_at, updated_at
            // Since 'id' is auto-incremented, we don't need to import it
            $content = $row[1];
            $categoryId = $row[2] === 'NULL' ? null : $row[2]; // Convert 'NULL' string to actual null
            $source = $row[3] === 'NULL' ? null : $row[3]; // Convert 'NULL' string to actual null
            $createdAt = $row[4];
            $updatedAt = $row[5];

            // Insert data into the database
            Quote::create([
                'content' => $content,
                'category_id' => $categoryId,
                'source' => $source,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]);
        }

        // Close the file
        fclose($handle);
    }

    dd("Data imported successfully!");
});

Route::prefix('api')->group(function () {

    Route::get('/quotes-list', [QuotesController::class, 'index']);
    Route::get('/quotes/search', [QuotesController::class, 'fetchSearchResults']);

    Route::get('/categories', [CategoryController::class, 'index']);

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

    /* Contact */
    Route::post('/contact-form', [ContactFormController::class, 'store']);
});

Route::get('login/google', [GoogleController::class, 'redirectToProvider']);
Route::get('login/google/callback', [GoogleController::class, 'handleProviderCallback']);



Route::middleware(['role:admin'])->group(function () {
    Route::get('/quote/{quote}', [QuotesController::class, 'show'])->name('quotes.show');
    Route::post('/api/quotes', [QuotesController::class, 'store'])->name('quotes.store');
    Route::put('/quote/{quote}', [QuotesController::class, 'update'])->name('quotes.update');
    Route::delete('/quote/{quote}', [QuotesController::class, 'destroy'])->name('quotes.destroy');
});

Route::get('/csrf-token', function () {
    return csrf_token();
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

Route::get('/assign-role', [UserController::class, 'assignRole']);
