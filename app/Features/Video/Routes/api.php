<?php

use App\Features\Video\Controllers\MyVideosController;
use App\Features\Video\Controllers\VideoUploadController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/videos', VideoUploadController::class);

    Route::get('/my-videos', MyVideosController::class);
});
