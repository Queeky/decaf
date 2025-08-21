<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\StoryPostController;
use App\Http\Controllers\StoryGetController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\AjaxController;
use Illuminate\Support\Facades\Log;

use App\Http\Middleware\CheckBetaPass;
use App\Http\Middleware\RedirectLogin;

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

Route::any('/login', [LoginController::class, 'login'])
    ->middleware(CheckBetaPass::class)
    ->name('login'); 

Route::middleware([RedirectLogin::class])->group(function() {
    Route::view('/about', 'about')->name('about'); 
    Route::post('/story', [StoryPostController::class, 'main'])->name("storyPost"); 
    Route::get('/story', [StoryGetController::class, 'main'])->name("storyGet"); 
    Route::get('/{slug?}', [IndexController::class, 'get'])->name("index"); 
    Route::post('/{slug?}', [IndexController::class, 'post'])->name("indexPost");
    // Route::view('/{slug?}', 'index')->name('index'); 
}); 
