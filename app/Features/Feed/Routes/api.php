<?php

use App\Features\Feed\Controllers\ReactFeedController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/feeds/{feed_id}/react', ReactFeedController::class);
});
