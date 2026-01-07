<?php

use App\Features\Live\Controllers\CreateLiveController;
use App\Features\Live\Controllers\EndLiveController;
use App\Features\Live\Controllers\StartLiveController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/lives', CreateLiveController::class);
    Route::post('/lives/{live_id}/start', StartLiveController::class);
    Route::post('/lives/{live_id}/end', EndLiveController::class);
});
