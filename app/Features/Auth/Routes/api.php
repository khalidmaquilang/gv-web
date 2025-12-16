<?php

use App\Features\Auth\Controllers\LoginController;
use App\Features\Auth\Controllers\RegisterController;
use App\Features\Auth\Controllers\RequestPasswordController;
use Illuminate\Support\Facades\Route;

Route::post('/login', LoginController::class)->name('login');
Route::post('/register', RegisterController::class)->name('register');
Route::post('/forgot-password', RequestPasswordController::class)->name('password.email');
