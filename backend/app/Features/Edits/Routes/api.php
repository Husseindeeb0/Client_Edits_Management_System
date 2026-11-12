<?php

use App\Features\Edits\Controllers\EditController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/edits', [EditController::class, 'getPaginatedEdits']);
    Route::post('/edits', [EditController::class, 'createEdit']);
    Route::get('/edits/{edit}', [EditController::class, 'getEditDetails']);
    Route::patch('/edits/{edit}', [EditController::class, 'updateEdit']);
    Route::delete('/edits/{edit}', [EditController::class, 'deleteEdit']);
});

