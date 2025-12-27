<?php

use App\Features\User\Controllers\UpdateProfileController;
use App\Features\User\Controllers\UploadProfileImageController;
use App\Features\User\Data\UserData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/user', fn (Request $request): \App\Features\User\Data\UserData => UserData::from($request->user()));
    Route::put('/profile', UpdateProfileController::class)->name('profile.update');
    Route::post('/profile/avatar', UploadProfileImageController::class)->name('profile.avatar');
});
