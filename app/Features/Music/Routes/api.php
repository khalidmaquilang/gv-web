<?php

use App\Features\Music\Controllers\GetVideoConnectedMusicController;
use App\Features\Music\Controllers\MusicsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/musics', MusicsController::class);
    Route::get('/musics/{music_id}/videos', GetVideoConnectedMusicController::class);
});
