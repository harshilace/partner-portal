<?php

use App\Http\Controllers\Audit\AuditController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Customers\CustomerController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Leads\LeadController;
use App\Http\Controllers\Leads\LeadFollowUpController;
use App\Http\Controllers\Notifications\NotificationController;
use App\Http\Controllers\Partners\PartnerController;
use App\Http\Controllers\Partners\PartnerUserController;
use App\Http\Controllers\Partners\SubPartnerController;
use App\Http\Controllers\Products\ProductController;
use App\Http\Controllers\Products\ProductPlanController;
use App\Http\Controllers\ReferralCodes\ReferralCodeController;
use App\Http\Controllers\Register\RegistrationController;
use App\Http\Controllers\Renewals\RenewalController;
use App\Http\Controllers\Reports\ReportController;
use App\Http\Controllers\Sales\OrderController;
use App\Http\Controllers\Sales\SaleController;
use App\Http\Controllers\Subscriptions\AutoDebitController;
use App\Http\Controllers\Subscriptions\SubscriptionController;
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
    // Role-Aware Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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

    // Product & Plan Management
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');

    Route::get('/products/{product}/plans', [ProductPlanController::class, 'index'])->name('products.plans.index');
    Route::post('/products/{product}/plans', [ProductPlanController::class, 'store'])->name('products.plans.store');
    Route::get('/products/{product}/plans/{plan}', [ProductPlanController::class, 'show'])->name('products.plans.show');
    Route::put('/products/{product}/plans/{plan}', [ProductPlanController::class, 'update'])->name('products.plans.update');

    // Order & Sales Management
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');

    // Subscription Management
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('/subscriptions/{subscription}', [SubscriptionController::class, 'show'])->name('subscriptions.show');
    Route::post('/subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');

    // Auto-Debit Mandates
    Route::get('/auto-debit-mandates', [AutoDebitController::class, 'index'])->name('auto-debit-mandates.index');
    Route::get('/auto-debit-mandates/{mandate}', [AutoDebitController::class, 'show'])->name('auto-debit-mandates.show');
    Route::post('/auto-debit-mandates/{mandate}/stop', [AutoDebitController::class, 'stop'])->name('auto-debit-mandates.stop');

    // Renewals
    Route::get('/renewals', [RenewalController::class, 'index'])->name('renewals.index');
    Route::get('/renewals/{renewal}', [RenewalController::class, 'show'])->name('renewals.show');
    Route::post('/renewals/{renewal}/reminder', [RenewalController::class, 'recordReminder'])->name('renewals.reminder');
    Route::post('/renewals/{renewal}/process', [RenewalController::class, 'process'])->name('renewals.process');

    // In-App Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.mark-read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // Reports
    Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/customers', [ReportController::class, 'customers'])->name('reports.customers');
    Route::get('/reports/renewals', [ReportController::class, 'renewals'])->name('reports.renewals');

    // Report Exports — blocked stubs until BC-10-07/08 resolved
    Route::get('/reports/sales/export/{format}', [ReportController::class, 'export'])->name('reports.sales.export')->defaults('report', 'sales');
    Route::get('/reports/customers/export/{format}', [ReportController::class, 'export'])->name('reports.customers.export')->defaults('report', 'customers');
    Route::get('/reports/renewals/export/{format}', [ReportController::class, 'export'])->name('reports.renewals.export')->defaults('report', 'renewals');

    // Audit History — Admin only (temporary security default) until BC-11-01 resolved
    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
});
