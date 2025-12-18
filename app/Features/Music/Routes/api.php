<?php

use App\Features\Music\Controllers\MusicController;
use Illuminate\Support\Facades\Route;

Route::get('/musics', MusicController::class)
    ->middleware('auth:sanctum');
