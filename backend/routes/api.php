<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\AttachmentController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\IngestController;
use App\Http\Controllers\Api\V1\WebhookController;

Route::prefix('v1')->middleware(['api'])->group(function () {

    Route::middleware(['tenant'])->group(function () {

        Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

        Route::apiResource('projects', ProjectController::class);

        Route::middleware(['api.key'])->group(function () {
            Route::apiResource('documents', DocumentController::class);
            Route::apiResource('attachments', AttachmentController::class)->except(['index', 'show']);

            Route::prefix('ingest')->name('api.v1.ingest.')->group(function () {
                Route::post('/{project_slug}', [IngestController::class, 'ingest'])->name('store');
                Route::get('/{queue_id}/status', [IngestController::class, 'status'])->name('status');
            });

            Route::apiResource('webhooks', WebhookController::class);
        });
    });
});
