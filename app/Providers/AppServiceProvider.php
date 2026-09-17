<?php

namespace App\Providers;

use App\Domain\Customers\Customer;
use App\Domain\Leads\Lead;
use App\Domain\Partners\Partner;
use App\Domain\Payments\Contracts\PaymentGatewayInterface;
use App\Domain\Payments\Order;
use App\Domain\Payments\Services\NullPaymentGateway;
use App\Domain\Products\Product;
use App\Domain\Products\ProductPlan;
use App\Domain\Referrals\ReferralCode;
use App\Domain\Renewals\Renewal;
use App\Domain\Subscriptions\AutoDebitMandate;
use App\Domain\Subscriptions\Subscription;
use App\Policies\AutoDebitMandatePolicy;
use App\Policies\CustomerPolicy;
use App\Policies\LeadPolicy;
use App\Policies\OrderPolicy;
use App\Policies\PartnerPolicy;
use App\Policies\ProductPlanPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ReferralCodePolicy;
use App\Policies\RenewalPolicy;
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
        $this->app->bind(PaymentGatewayInterface::class, NullPaymentGateway::class);
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
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(ProductPlan::class, ProductPlanPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(AutoDebitMandate::class, AutoDebitMandatePolicy::class);
        Gate::policy(Renewal::class, RenewalPolicy::class);
    }
}
