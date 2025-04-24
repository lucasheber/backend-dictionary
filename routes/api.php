<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auhtentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return response()->json([
        'message' => 'Fullstack Challenge 🏅 - Dictionary',
    ]);
})->name('auth.index');
Route::post('/auth/signup', [AuthController::class, 'signup']);
Route::post('/auth/signin', [AuthController::class, 'signin']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


