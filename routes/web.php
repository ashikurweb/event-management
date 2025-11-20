<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamMemberController;
use App\Http\Controllers\TeamInvitationController;
use App\Http\Controllers\SpeakerController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\SponsorController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventTagController;
use App\Http\Controllers\PromoCodeController;
use App\Http\Controllers\TrashController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewReplyController;
use App\Http\Controllers\AIAssistantController;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard/Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile Routes
Route::middleware(['auth', 'verified'])->prefix('dashboard/profile')->name('profile.')->group(function () {
    Route::get('/', [ProfileController::class, 'index'])->name('index');
    Route::put('/', [ProfileController::class, 'update'])->name('update');
    Route::post('/avatar', [ProfileController::class, 'updateAvatar'])->name('avatar');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
});

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
    
    // Database Backup Routes
    Route::get('/database-backup', [App\Http\Controllers\Settings\DatabaseBackupController::class, 'index'])->name('database-backup');
    Route::post('/database-backup/create', [App\Http\Controllers\Settings\DatabaseBackupController::class, 'create'])->name('database-backup.create');
    Route::get('/database-backup/download/{filename}', [App\Http\Controllers\Settings\DatabaseBackupController::class, 'download'])->name('database-backup.download');
    Route::delete('/database-backup/delete/{filename}', [App\Http\Controllers\Settings\DatabaseBackupController::class, 'delete'])->name('database-backup.delete');
    Route::post('/database-backup/config', [App\Http\Controllers\Settings\DatabaseBackupController::class, 'saveConfig'])->name('database-backup.config');
    
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

// Team Invitation Routes (must come before teams routes to avoid route conflict)
Route::middleware(['auth', 'verified'])->prefix('dashboard/team-invitations')->name('team-invitations.')->group(function () {
    Route::get('/', [TeamInvitationController::class, 'index'])->name('index');
    Route::get('/search', [TeamInvitationController::class, 'search'])->name('search');
    Route::get('/create', [TeamInvitationController::class, 'create'])->name('create');
    Route::post('/', [TeamInvitationController::class, 'store'])->name('store');
    Route::get('/{teamInvitation}/activities', [TeamInvitationController::class, 'activities'])->name('activities');
    Route::get('/{teamInvitation}', [TeamInvitationController::class, 'show'])->name('show');
    Route::get('/{teamInvitation}/edit', [TeamInvitationController::class, 'edit'])->name('edit');
    Route::put('/{teamInvitation}', [TeamInvitationController::class, 'update'])->name('update');
    Route::delete('/{teamInvitation}', [TeamInvitationController::class, 'destroy'])->name('destroy');
    Route::post('/{teamInvitation}/resend', [TeamInvitationController::class, 'resend'])->name('resend');
    Route::post('/bulk-action', [TeamInvitationController::class, 'bulkAction'])->name('bulk-action');
});

// Team Member Routes (must come before teams routes to avoid route conflict)
Route::middleware(['auth', 'verified'])->prefix('dashboard/team-members')->name('team-members.')->group(function () {
    Route::get('/', [TeamMemberController::class, 'index'])->name('index');
    Route::get('/search', [TeamMemberController::class, 'search'])->name('search');
    Route::get('/create', [TeamMemberController::class, 'create'])->name('create');
    Route::post('/', [TeamMemberController::class, 'store'])->name('store');
    Route::get('/{teamMember}/activities', [TeamMemberController::class, 'activities'])->name('activities');
    Route::get('/{teamMember}', [TeamMemberController::class, 'show'])->name('show');
    Route::get('/{teamMember}/edit', [TeamMemberController::class, 'edit'])->name('edit');
    Route::put('/{teamMember}', [TeamMemberController::class, 'update'])->name('update');
    Route::delete('/{teamMember}', [TeamMemberController::class, 'destroy'])->name('destroy');
    Route::post('/bulk-action', [TeamMemberController::class, 'bulkAction'])->name('bulk-action');
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

// Speaker Routes
Route::middleware(['auth', 'verified'])->prefix('dashboard/speakers')->name('speakers.')->group(function () {
    Route::get('/', [SpeakerController::class, 'index'])->name('index');
    Route::get('/search', [SpeakerController::class, 'search'])->name('search');
    Route::get('/create', [SpeakerController::class, 'create'])->name('create');
    Route::post('/', [SpeakerController::class, 'store'])->name('store');
    Route::get('/{speaker}/activities', [SpeakerController::class, 'activities'])->name('activities');
    Route::get('/{speaker}', [SpeakerController::class, 'show'])->name('show');
    Route::get('/{speaker}/edit', [SpeakerController::class, 'edit'])->name('edit');
    Route::put('/{speaker}', [SpeakerController::class, 'update'])->name('update');
    Route::delete('/{speaker}', [SpeakerController::class, 'destroy'])->name('destroy');
    Route::post('/bulk-action', [SpeakerController::class, 'bulkAction'])->name('bulk-action');
});

// Vendor Routes
Route::middleware(['auth', 'verified'])->prefix('dashboard/vendors')->name('vendors.')->group(function () {
    Route::get('/', [VendorController::class, 'index'])->name('index');
    Route::get('/search', [VendorController::class, 'search'])->name('search');
    Route::get('/create', [VendorController::class, 'create'])->name('create');
    Route::post('/', [VendorController::class, 'store'])->name('store');
    Route::get('/{vendor}/activities', [VendorController::class, 'activities'])->name('activities');
    Route::get('/{vendor}', [VendorController::class, 'show'])->name('show');
    Route::get('/{vendor}/edit', [VendorController::class, 'edit'])->name('edit');
    Route::put('/{vendor}', [VendorController::class, 'update'])->name('update');
    Route::delete('/{vendor}', [VendorController::class, 'destroy'])->name('destroy');
    Route::post('/bulk-action', [VendorController::class, 'bulkAction'])->name('bulk-action');
});

// Venue Routes
Route::middleware(['auth', 'verified'])->prefix('dashboard/venues')->name('venues.')->group(function () {
    Route::get('/', [VenueController::class, 'index'])->name('index');
    Route::get('/search', [VenueController::class, 'search'])->name('search');
    Route::get('/create', [VenueController::class, 'create'])->name('create');
    Route::post('/', [VenueController::class, 'store'])->name('store');
    Route::get('/{venue}/activities', [VenueController::class, 'activities'])->name('activities');
    Route::get('/{venue:slug}', [VenueController::class, 'show'])->name('show');
    Route::get('/{venue}/edit', [VenueController::class, 'edit'])->name('edit');
    Route::put('/{venue}', [VenueController::class, 'update'])->name('update');
    Route::delete('/{venue}', [VenueController::class, 'destroy'])->name('destroy');
    Route::post('/bulk-action', [VenueController::class, 'bulkAction'])->name('bulk-action');
});

// Sponsor Routes
Route::middleware(['auth', 'verified'])->prefix('dashboard/sponsors')->name('sponsors.')->group(function () {
    Route::get('/', [SponsorController::class, 'index'])->name('index');
    Route::get('/search', [SponsorController::class, 'search'])->name('search');
    Route::get('/create', [SponsorController::class, 'create'])->name('create');
    Route::post('/', [SponsorController::class, 'store'])->name('store');
    Route::get('/{sponsor}/activities', [SponsorController::class, 'activities'])->name('activities');
    Route::get('/{sponsor}', [SponsorController::class, 'show'])->name('show');
    Route::get('/{sponsor}/edit', [SponsorController::class, 'edit'])->name('edit');
    Route::put('/{sponsor}', [SponsorController::class, 'update'])->name('update');
    Route::delete('/{sponsor}', [SponsorController::class, 'destroy'])->name('destroy');
    Route::post('/bulk-action', [SponsorController::class, 'bulkAction'])->name('bulk-action');
});

// Survey Routes
Route::middleware(['auth', 'verified'])->prefix('dashboard/surveys')->name('surveys.')->group(function () {
    Route::get('/', [SurveyController::class, 'index'])->name('index');
    Route::get('/search', [SurveyController::class, 'search'])->name('search');
    Route::get('/create', [SurveyController::class, 'create'])->name('create');
    Route::post('/', [SurveyController::class, 'store'])->name('store');
    Route::get('/{survey}/activities', [SurveyController::class, 'activities'])->name('activities');
    Route::get('/{survey}', [SurveyController::class, 'show'])->name('show');
    Route::get('/{survey}/edit', [SurveyController::class, 'edit'])->name('edit');
    Route::put('/{survey}', [SurveyController::class, 'update'])->name('update');
    Route::delete('/{survey}', [SurveyController::class, 'destroy'])->name('destroy');
    Route::post('/bulk-action', [SurveyController::class, 'bulkAction'])->name('bulk-action');
});

// Certificate Routes
Route::middleware(['auth', 'verified'])->prefix('dashboard/certificates')->name('certificates.')->group(function () {
    Route::get('/', [CertificateController::class, 'index'])->name('index');
    Route::get('/search', [CertificateController::class, 'search'])->name('search');
    Route::get('/create', [CertificateController::class, 'create'])->name('create');
    Route::post('/', [CertificateController::class, 'store'])->name('store');
    Route::get('/{certificate}/activities', [CertificateController::class, 'activities'])->name('activities');
    Route::get('/{certificate}', [CertificateController::class, 'show'])->name('show');
    Route::get('/{certificate}/edit', [CertificateController::class, 'edit'])->name('edit');
    Route::put('/{certificate}', [CertificateController::class, 'update'])->name('update');
    Route::delete('/{certificate}', [CertificateController::class, 'destroy'])->name('destroy');
    Route::post('/{certificate}/download', [CertificateController::class, 'download'])->name('download');
    Route::post('/bulk-action', [CertificateController::class, 'bulkAction'])->name('bulk-action');
});

// Event Routes
Route::middleware(['auth', 'verified'])->prefix('dashboard/events')->name('events.')->group(function () {
    Route::get('/', [EventController::class, 'index'])->name('index');
    Route::get('/search', [EventController::class, 'search'])->name('search');
    Route::get('/create', [EventController::class, 'create'])->name('create');
    Route::post('/', [EventController::class, 'store'])->name('store');
    Route::get('/{event}/activities', [EventController::class, 'activities'])->name('activities');
    Route::get('/{event}', [EventController::class, 'show'])->name('show');
    Route::get('/{event}/edit', [EventController::class, 'edit'])->name('edit');
    Route::put('/{event}', [EventController::class, 'update'])->name('update');
    Route::delete('/{event}', [EventController::class, 'destroy'])->name('destroy');
    Route::post('/bulk-action', [EventController::class, 'bulkAction'])->name('bulk-action');
});

// Event Tag Routes
Route::middleware(['auth', 'verified'])->prefix('dashboard/event-tags')->name('event-tags.')->group(function () {
    Route::get('/', [EventTagController::class, 'index'])->name('index');
    Route::get('/search', [EventTagController::class, 'search'])->name('search');
    Route::get('/create', [EventTagController::class, 'create'])->name('create');
    Route::post('/', [EventTagController::class, 'store'])->name('store');
    Route::get('/{eventTag}/activities', [EventTagController::class, 'activities'])->name('activities');
    Route::get('/{eventTag}', [EventTagController::class, 'show'])->name('show');
    Route::get('/{eventTag}/edit', [EventTagController::class, 'edit'])->name('edit');
    Route::put('/{eventTag}', [EventTagController::class, 'update'])->name('update');
    Route::delete('/{eventTag}', [EventTagController::class, 'destroy'])->name('destroy');
    Route::post('/bulk-action', [EventTagController::class, 'bulkAction'])->name('bulk-action');
});

// Promo Code Routes
Route::middleware(['auth', 'verified'])->prefix('dashboard/promo-codes')->name('promo-codes.')->group(function () {
    Route::get('/', [PromoCodeController::class, 'index'])->name('index');
    Route::get('/search', [PromoCodeController::class, 'search'])->name('search');
    Route::get('/create', [PromoCodeController::class, 'create'])->name('create');
    Route::post('/', [PromoCodeController::class, 'store'])->name('store');
    Route::get('/{promoCode}', [PromoCodeController::class, 'show'])->name('show');
    Route::get('/{promoCode}/edit', [PromoCodeController::class, 'edit'])->name('edit');
    Route::put('/{promoCode}', [PromoCodeController::class, 'update'])->name('update');
    Route::delete('/{promoCode}', [PromoCodeController::class, 'destroy'])->name('destroy');
    Route::post('/bulk-action', [PromoCodeController::class, 'bulkAction'])->name('bulk-action');
});

// Review Routes
Route::middleware(['auth', 'verified'])->prefix('dashboard/reviews')->name('reviews.')->group(function () {
    Route::get('/', [ReviewController::class, 'index'])->name('index');
    Route::get('/search', [ReviewController::class, 'search'])->name('search');
    Route::post('/', [ReviewController::class, 'store'])->name('store');
    Route::get('/{review}', [ReviewController::class, 'show'])->name('show');
    Route::put('/{review}', [ReviewController::class, 'update'])->name('update');
    Route::delete('/{review}', [ReviewController::class, 'destroy'])->name('destroy');
    Route::post('/{review}/approve', [ReviewController::class, 'approve'])->name('approve');
    Route::post('/{review}/reject', [ReviewController::class, 'reject'])->name('reject');
    Route::post('/bulk-action', [ReviewController::class, 'bulkAction'])->name('bulk-action');
});

// Review Reply Routes
Route::middleware(['auth', 'verified'])->prefix('dashboard/review-replies')->name('review-replies.')->group(function () {
    Route::post('/', [ReviewReplyController::class, 'store'])->name('store');
    Route::put('/{reviewReply}', [ReviewReplyController::class, 'update'])->name('update');
    Route::delete('/{reviewReply}', [ReviewReplyController::class, 'destroy'])->name('destroy');
});

// AI Assistant Routes
Route::middleware(['auth', 'verified'])->prefix('api/ai')->name('ai.')->group(function () {
    Route::post('/chat', [AIAssistantController::class, 'chat'])->name('chat');
    Route::get('/context', [AIAssistantController::class, 'getContext'])->name('context');
});

// Authentication Routes
require __DIR__.'/auth.php';