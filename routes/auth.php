<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\Login\LoginController;
use App\Http\Controllers\Auth\Register\RegisterController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| Here are the authentication routes for the application. These routes
| handle user login, registration, and social authentication.
|
*/

// Login Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

// Register Routes
Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');

// Social Login Routes
Route::get('/auth/{provider}/redirect', [LoginController::class, 'redirectToProvider'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [LoginController::class, 'handleProviderCallback'])->name('social.callback');

// Logout Route (can be added later)
// Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

