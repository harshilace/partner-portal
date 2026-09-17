<?php

namespace App\Providers;

use App\Domain\Authentication\User;
use App\Domain\Customers\Customer;
use App\Domain\Leads\Lead;
use App\Domain\Notifications\Notification;
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
use App\Events\AutoDebitStopped;
use App\Events\CustomerCreated;
use App\Events\LeadCreated;
use App\Events\PaymentFailed;
use App\Events\PaymentReceived;
use App\Events\RenewalDue;
use App\Events\SaleCompleted;
use App\Events\SubPartnerCreated;
use App\Events\SubscriptionCancelled;
use App\Listeners\SendNotificationListener;
use App\Policies\AutoDebitMandatePolicy;
use App\Policies\CustomerPolicy;
use App\Policies\LeadPolicy;
use App\Policies\NotificationPolicy;
use App\Policies\OrderPolicy;
use App\Policies\PartnerPolicy;
use App\Policies\ProductPlanPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ReferralCodePolicy;
use App\Policies\RenewalPolicy;
use App\Policies\SubscriptionPolicy;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Event;
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
        config()->set('inertia.pages.paths', [resource_path('js/Pages'), resource_path('js/pages')]);

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
        Gate::policy(Notification::class, NotificationPolicy::class);
        Gate::policy(DatabaseNotification::class, NotificationPolicy::class);

        Event::listen(AutoDebitStopped::class, SendNotificationListener::class);
        Event::listen(SubPartnerCreated::class, SendNotificationListener::class);
        Event::listen(SaleCompleted::class, SendNotificationListener::class);
        Event::listen(PaymentReceived::class, SendNotificationListener::class);
        Event::listen(RenewalDue::class, SendNotificationListener::class);
        Event::listen(LeadCreated::class, SendNotificationListener::class);
        Event::listen(CustomerCreated::class, SendNotificationListener::class);
        Event::listen(PaymentFailed::class, SendNotificationListener::class);
        Event::listen(SubscriptionCancelled::class, SendNotificationListener::class);

        Gate::define('viewDashboard', function (User $user): bool {
            if (! $user->isActive()) {
                return false;
            }

            if ($user->isAdmin()) {
                return true;
            }

            if ($user->isMainPartner()) {
                return $user->partner() !== null;
            }

            if ($user->isSubPartner()) {
                return $user->partner() !== null;
            }

            return false;
        });
    }
}
