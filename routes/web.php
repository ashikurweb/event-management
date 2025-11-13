<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TrashController;

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
    Route::get('/system', function () {
        return Inertia::render('Dashboard/Settings/SystemSettings');
    })->name('system');
    
    // Recycle Bin Routes
    Route::get('/recycle-bin', [TrashController::class, 'index'])->name('recycle-bin');
    Route::post('/recycle-bin/{type}/{id}/restore', [TrashController::class, 'restore'])->name('recycle-bin.restore');
    Route::delete('/recycle-bin/{type}/{id}', [TrashController::class, 'forceDelete'])->name('recycle-bin.force-delete');
    Route::post('/recycle-bin/bulk-restore', [TrashController::class, 'bulkRestore'])->name('recycle-bin.bulk-restore');
    Route::post('/recycle-bin/bulk-delete', [TrashController::class, 'bulkForceDelete'])->name('recycle-bin.bulk-delete');
});

// Category Routes
Route::middleware(['auth', 'verified'])->prefix('dashboard/categories')->name('categories.')->group(function () {
    Route::get('/', [App\Http\Controllers\CategoryController::class, 'index'])->name('index');
    Route::get('/search', [CategoryController::class, 'search'])->name('search');
    Route::get('/create', [CategoryController::class, 'create'])->name('create');
    Route::post('/', [CategoryController::class, 'store'])->name('store');
    Route::get('/{category}/activities', [CategoryController::class, 'activities'])->name('activities');
    Route::get('/{category:slug}', [CategoryController::class, 'show'])->name('show');
    Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
    Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
    Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
    Route::post('/bulk-action', [CategoryController::class, 'bulkAction'])->name('bulk-action');
});

// Team Routes
Route::middleware(['auth', 'verified'])->prefix('dashboard/teams')->name('teams.')->group(function () {
    Route::get('/', [TeamController::class, 'index'])->name('index');
    Route::get('/search', [TeamController::class, 'search'])->name('search');
    Route::get('/create', [TeamController::class, 'create'])->name('create');
    Route::post('/', [TeamController::class, 'store'])->name('store');
    Route::get('/{team}/activities', [TeamController::class, 'activities'])->name('activities');
    Route::get('/{team:slug}', [TeamController::class, 'show'])->name('show');
    Route::get('/{team}/edit', [TeamController::class, 'edit'])->name('edit');
    Route::put('/{team}', [TeamController::class, 'update'])->name('update');
    Route::delete('/{team}', [TeamController::class, 'destroy'])->name('destroy');
    Route::post('/bulk-action', [TeamController::class, 'bulkAction'])->name('bulk-action');
});

// Authentication Routes
require __DIR__.'/auth.php';