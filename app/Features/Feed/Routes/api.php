<?php

use App\Features\Feed\Controllers\GetFeedsController;
use App\Features\Feed\Controllers\ReactFeedController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/feeds', GetFeedsController::class);

    Route::post('/feeds/{feed_id}/react', ReactFeedController::class);
});
