<?php

use App\Features\Comment\Controllers\CommentsController;
use App\Features\Comment\Controllers\PostVideoCommentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/feeds/{feed_id}/comments', CommentsController::class);
    Route::post('/feeds/{feed_id}/comments', PostVideoCommentController::class);
});
