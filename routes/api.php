<?php

use App\Http\Controllers\AuthController;
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

Route::get('/login', fn () => response()->json(['message' => 'Login']))->name('login');
Route::post('/auth/signup', [AuthController::class, 'signup']);
Route::post('/auth/signin', [AuthController::class, 'signin']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::get('/user', fn(Request $request) => $request->user())->middleware('auth:sanctum');
