<?php

use App\Features\Attachments\Controllers\AttachmentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/edits/{edit}/attachments', [AttachmentController::class, 'getAttachmentsByEdit']);
    Route::post('/edits/{edit}/attachments', [AttachmentController::class, 'uploadAttachments']);
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'downloadAttachment']);
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'deleteAttachment']);
});

