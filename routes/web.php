<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Partners\PartnerController;
use App\Http\Controllers\Partners\PartnerUserController;
use App\Http\Controllers\Partners\SubPartnerController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::middleware(['auth', 'active'])->group(function () {
    // Partner Management
    Route::get('/partners', [PartnerController::class, 'index'])->name('partners.index');
    Route::post('/partners', [PartnerController::class, 'store'])->name('partners.store');
    Route::get('/partners/{partner}', [PartnerController::class, 'show'])->name('partners.show');
    Route::put('/partners/{partner}', [PartnerController::class, 'update'])->name('partners.update');
    Route::delete('/partners/{partner}', [PartnerController::class, 'destroy'])->name('partners.destroy');

    // Sub-Partner Management (scoped to partner)
    Route::get('/partners/{partner}/sub-partners', [SubPartnerController::class, 'index'])->name('partners.sub-partners.index');
    Route::post('/partners/{partner}/sub-partners', [SubPartnerController::class, 'store'])->name('partners.sub-partners.store');
    Route::get('/partners/{partner}/sub-partners/{subPartner}', [SubPartnerController::class, 'show'])->name('partners.sub-partners.show');
    Route::put('/partners/{partner}/sub-partners/{subPartner}', [SubPartnerController::class, 'update'])->name('partners.sub-partners.update');
    Route::delete('/partners/{partner}/sub-partners/{subPartner}', [SubPartnerController::class, 'destroy'])->name('partners.sub-partners.destroy');

    // Partner-User Management
    Route::post('/partners/{partner}/users', [PartnerUserController::class, 'store'])->name('partners.users.store');
    Route::delete('/partners/{partner}/users/{user}', [PartnerUserController::class, 'destroy'])->name('partners.users.destroy');
});
