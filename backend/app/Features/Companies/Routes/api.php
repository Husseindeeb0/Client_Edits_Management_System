<?php

use App\Features\Companies\Controllers\CompanyController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/companies', [CompanyController::class, 'getPaginatedCompanies']);
    Route::post('/companies', [CompanyController::class, 'createCompany']);
    Route::get('/companies/{company}', [CompanyController::class, 'getCompanyDetails']);
    Route::patch('/companies/{company}', [CompanyController::class, 'updateCompany']);
    Route::delete('/companies/{company}', [CompanyController::class, 'deleteCompany']);
});

