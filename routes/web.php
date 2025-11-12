<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;

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

// Category Routes
Route::middleware(['auth', 'verified'])->prefix('dashboard/categories')->name('categories.')->group(function () {
    Route::get('/', [App\Http\Controllers\CategoryController::class, 'index'])->name('index');
    Route::get('/search', [CategoryController::class, 'search'])->name('search');
    Route::get('/create', [CategoryController::class, 'create'])->name('create');
    Route::post('/', [CategoryController::class, 'store'])->name('store');
    Route::get('/{category}', [CategoryController::class, 'show'])->name('show');
    Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
    Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
    Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
    Route::post('/bulk-action', [CategoryController::class, 'bulkAction'])->name('bulk-action');
});

// Authentication Routes
require __DIR__.'/auth.php';