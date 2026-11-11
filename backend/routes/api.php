<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| Each feature encapsulates its own route definitions within its Routes/ directory.
|
*/

// Authentication Feature Routes
Route::group([], base_path('app/Features/Authentication/Routes/api.php'));
