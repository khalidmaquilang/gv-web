<?php

use App\Features\Auth\Controllers\EmailVerificationController;

Route::get('/email/verify/{id}/{hash}', EmailVerificationController::class)
    ->middleware(['guest', 'signed'])
    ->name('verification.verify');
