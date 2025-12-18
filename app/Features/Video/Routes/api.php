<?php

use App\Features\Video\Controllers\VideoUploadController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/videos', VideoUploadController::class)
        ->middleware('auth:sanctum');
});
