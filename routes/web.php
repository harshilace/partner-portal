<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Customers\CustomerController;
use App\Http\Controllers\Leads\LeadController;
use App\Http\Controllers\Leads\LeadFollowUpController;
use App\Http\Controllers\Partners\PartnerController;
use App\Http\Controllers\Partners\PartnerUserController;
use App\Http\Controllers\Partners\SubPartnerController;
use App\Http\Controllers\ReferralCodes\ReferralCodeController;
use App\Http\Controllers\Register\RegistrationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

// Public Registration
Route::get('/register', [RegistrationController::class, 'show'])->name('register');
Route::post('/register', [RegistrationController::class, 'store'])->name('register.store');

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

    // Referral Code Management
    Route::get('/referral-codes', [ReferralCodeController::class, 'index'])->name('referral-codes.index');
    Route::post('/referral-codes', [ReferralCodeController::class, 'store'])->name('referral-codes.store');
    Route::get('/referral-codes/{referralCode}', [ReferralCodeController::class, 'show'])->name('referral-codes.show');
    Route::put('/referral-codes/{referralCode}', [ReferralCodeController::class, 'update'])->name('referral-codes.update');

    // Lead Management
    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
    Route::put('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');

    // Lead Follow-Ups
    Route::post('/leads/{lead}/follow-ups', [LeadFollowUpController::class, 'store'])->name('leads.follow-ups.store');
    Route::put('/leads/{lead}/follow-ups/{followUp}', [LeadFollowUpController::class, 'update'])->name('leads.follow-ups.update');

    // Customer Management
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::put('/customers/{customer}/mapping', [CustomerController::class, 'updateMapping'])->name('customers.mapping.update');
});
