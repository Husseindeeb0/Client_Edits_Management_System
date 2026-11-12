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

// Clients Feature Routes
Route::group([], base_path('app/Features/Clients/Routes/api.php'));

// Companies Feature Routes
Route::group([], base_path('app/Features/Companies/Routes/api.php'));

// Projects Feature Routes
Route::group([], base_path('app/Features/Projects/Routes/api.php'));

// Edits Feature Routes
Route::group([], base_path('app/Features/Edits/Routes/api.php'));

// Attachments Feature Routes
Route::group([], base_path('app/Features/Attachments/Routes/api.php'));
