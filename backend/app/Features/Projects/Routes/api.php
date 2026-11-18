<?php

use App\Features\Projects\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/projects', [ProjectController::class, 'getPaginatedProjects']);
    Route::post('/projects', [ProjectController::class, 'createProject']);
    Route::get('/projects/client/{client}', [ProjectController::class, 'clientProjects']);
    Route::get('/projects/company/{company}', [ProjectController::class, 'companyProjects']);
    Route::get('/projects/{project}', [ProjectController::class, 'getProjectDetails'])->whereNumber('project');
    Route::patch('/projects/{project}', [ProjectController::class, 'updateProject'])->whereNumber('project');
    Route::delete('/projects/{project}', [ProjectController::class, 'deleteProject'])->whereNumber('project');
    Route::get('/projects/{project}/edits', [ProjectController::class, 'projectEdits']);
});
