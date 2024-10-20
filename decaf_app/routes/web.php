<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Log;

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

// Default doesn't have a name
Route::get('/', function () {
    return view('index');
});


// Testing login when Decaf is live but not finished
Route::get('/login.php', function () {
    return view('login');
})->name("loginGet"); 

Route::post('/login.php', [UserController::class, 'loginPost'])->name("loginPost");



Route::get('/about.php', function () {
    return view('about');
})->name("aboutGet");

Route::get('/story.php', function () {
    return view('story');
})->name("storyGet");

Route::post('/story.php', [UserController::class, 'storyPost'])->name("storyPost"); 

// Route::post('/story.php', [UserController::class, 'createStory'])->name("createStory"); 
