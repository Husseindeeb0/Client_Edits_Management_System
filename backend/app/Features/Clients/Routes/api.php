<?php

use App\Features\Clients\Controllers\ClientController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/clients', [ClientController::class, 'getPaginatedClients']);
    Route::post('/clients', [ClientController::class, 'createClient']);
    Route::get('/clients/{client}', [ClientController::class, 'getClientDetails']);
    Route::patch('/clients/{client}', [ClientController::class, 'updateClient']);
    Route::delete('/clients/{client}', [ClientController::class, 'deleteClient']);
});
