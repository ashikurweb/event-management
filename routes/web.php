<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard/Dashboard');
})->name('dashboard');

Route::get('/events', function () {
    return Inertia::render('Events/Events');
})->name('events');

Route::get('/events/{id}', function ($id) {
    return Inertia::render('Events/EventDetail', ['id' => $id]);
})->name('event.detail');

// Authentication Routes
require __DIR__.'/auth.php';