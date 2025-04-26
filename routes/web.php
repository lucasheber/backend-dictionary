<?php

use Illuminate\Support\Facades\Route;

// redirect to the API documentation
Route::get('/', function () {
   return redirect(config('app.api_docs_url'));
});
