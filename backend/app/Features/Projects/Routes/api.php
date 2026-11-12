<?php

use App\Features\Projects\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/projects/client/{client}', [ProjectController::class, 'clientProjects']);
    Route::get('/projects/company/{company}', [ProjectController::class, 'companyProjects']);
    Route::get('/projects/{project}/edits', [ProjectController::class, 'projectEdits']);
});
