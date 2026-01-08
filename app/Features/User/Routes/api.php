<?php

use App\Features\User\Actions\GetUserDataAction;
use App\Features\User\Controllers\FollowUserController;
use App\Features\User\Controllers\GetUserProfileController;
use App\Features\User\Controllers\GetUserVideosController;
use App\Features\User\Controllers\UnfollowUserController;
use App\Features\User\Controllers\UpdateProfileController;
use App\Features\User\Controllers\UploadProfileImageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/user', fn (Request $request) => app(GetUserDataAction::class)->handle($request->user()));
    Route::put('/profile', UpdateProfileController::class)->name('profile.update');
    Route::post('/profile/avatar', UploadProfileImageController::class)->name('profile.avatar');

    Route::get('/users/{user_id}', GetUserProfileController::class)->name('users.show');
    Route::post('/users/{user_id}/follow', FollowUserController::class)->name('users.follow');
    Route::delete('/users/{user_id}/follow', UnfollowUserController::class)->name('users.unfollow');
    Route::get('/users/{user_id}/videos', GetUserVideosController::class)->name('users.videos');
});
