<?php

namespace App\Providers;

use App\Domain\Partners\Partner;
use App\Domain\Subscriptions\Subscription;
use App\Policies\PartnerPolicy;
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
    }
}
