<?php

use App\Features\Feed\Controllers\GetFeedsController;
use App\Features\Feed\Controllers\GetFollowingFeedsController;
use App\Features\Feed\Controllers\ReactFeedController;
use App\Features\Feed\Controllers\ViewFeedController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/feeds', GetFeedsController::class);

    Route::get('/feeds/following', GetFollowingFeedsController::class);

    Route::post('/feeds/{feed_id}/react', ReactFeedController::class);
    Route::post('/feeds/{feed_id}/view', ViewFeedController::class);
});
