<?php

namespace App\Providers;

use App\Domain\Customers\Customer;
use App\Domain\Leads\Lead;
use App\Domain\Partners\Partner;
use App\Domain\Referrals\ReferralCode;
use App\Domain\Subscriptions\Subscription;
use App\Policies\CustomerPolicy;
use App\Policies\LeadPolicy;
use App\Policies\PartnerPolicy;
use App\Policies\ReferralCodePolicy;
use App\Policies\SubscriptionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Partner::class, PartnerPolicy::class);
        Gate::policy(Subscription::class, SubscriptionPolicy::class);
        Gate::policy(Lead::class, LeadPolicy::class);
        Gate::policy(ReferralCode::class, ReferralCodePolicy::class);
        Gate::policy(Customer::class, CustomerPolicy::class);
    }
}
