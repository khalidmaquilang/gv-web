<?php

use App\Features\Auth\Controllers\ChangePasswordController;
use App\Features\Auth\Controllers\ChangePasswordPageController;
use App\Features\Auth\Controllers\EmailVerificationController;

Route::get('/email/verify/{id}/{hash}', EmailVerificationController::class)
    ->middleware(['guest', 'signed'])
    ->name('verification.verify');

Route::get('/password/reset/{token}', ChangePasswordPageController::class)
    ->name('password.reset');

Route::post('/password/reset', ChangePasswordController::class)
    ->name('password.change');
