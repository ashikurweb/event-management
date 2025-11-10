<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard/Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/events', function () {
    return Inertia::render('Events/Events');
})->name('events');

Route::get('/events/{id}', function ($id) {
    return Inertia::render('Events/EventDetail', ['id' => $id]);
})->name('event.detail');

// Settings Routes
Route::middleware(['auth', 'verified'])->prefix('dashboard/settings')->name('settings.')->group(function () {
    Route::get('/general', [App\Http\Controllers\Settings\MailConfigurationController::class, 'index'])->name('general');
    Route::post('/mail-config', [App\Http\Controllers\Settings\MailConfigurationController::class, 'store'])->name('mail-config.store');
    Route::post('/mail-config/test', [App\Http\Controllers\Settings\MailConfigurationController::class, 'testConnection'])->name('mail-config.test');
});

// Authentication Routes
require __DIR__.'/auth.php';