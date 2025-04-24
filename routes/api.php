<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DictionaryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Unauthenticated Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return response()->json(['message' => 'Fullstack Challenge 🏅 - Dictionary']);
})->name('auth.index');

Route::get('/login', fn() => response()->json(['message' => 'Login']))->name('login');
Route::post('/auth/signup', [AuthController::class, 'signup']);
Route::post('/auth/signin', [AuthController::class, 'signin']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::get('/user/me', fn(Request $request) => $request->user())->middleware('auth:sanctum');

// grouping routes with the auth:sanctum middleware
Route::controller(DictionaryController::class)->middleware('auth:sanctum')->group(function () {

    // [GET] /entries/en
    Route::get('/entries/{lang}', 'index')->name('dictionary.index');

    // [GET] /entries/en/{word}
    Route::get('/entries/{lang}/{word}',  'show')->name('dictionary.show');
});
