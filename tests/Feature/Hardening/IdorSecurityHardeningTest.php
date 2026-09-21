<?php

namespace Tests\Feature\Hardening;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Customers\Customer;
use App\Domain\Notifications\Notification;
use App\Domain\Partners\Partner;
use App\Domain\Payments\Order;
use App\Domain\Renewals\Renewal;
use App\Domain\Subscriptions\AutoDebitMandate;
use App\Domain\Subscriptions\Subscription;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class IdorSecurityHardeningTest extends TestCase
{
    protected Partner $partnerA;

    protected Partner $partnerB;

    protected Partner $subPartnerA;

    protected Partner $subPartnerB;

    protected User $userSubPartnerA;

    protected User $userSubPartnerB;

    protected User $userMainPartnerA;

    protected function setUp(): void
    {
        parent::setUp();

        // Main Partners
        $this->partnerA = new Partner(['type' => 'main', 'status' => 'active']);
        $this->partnerA->id = 1;

        $this->partnerB = new Partner(['type' => 'main', 'status' => 'active']);
        $this->partnerB->id = 2;

        // Sub-Partners
        $this->subPartnerA = new Partner(['type' => 'sub', 'parent_partner_id' => 1, 'status' => 'active']);
        $this->subPartnerA->id = 10;

        $this->subPartnerB = new Partner(['type' => 'sub', 'parent_partner_id' => 2, 'status' => 'active']);
        $this->subPartnerB->id = 20;

        // Users
        $this->userSubPartnerA = new User(['role' => Role::SUB_PARTNER->value, 'status' => 'active']);
        $this->userSubPartnerA->id = 100;
        $this->userSubPartnerA->setRelation('partners', collect([$this->subPartnerA]));

        $this->userSubPartnerB = new User(['role' => Role::SUB_PARTNER->value, 'status' => 'active']);
        $this->userSubPartnerB->id = 200;
        $this->userSubPartnerB->setRelation('partners', collect([$this->subPartnerB]));

        $this->userMainPartnerA = new User(['role' => Role::MAIN_PARTNER->value, 'status' => 'active']);
        $this->userMainPartnerA->id = 101;
        $this->userMainPartnerA->setRelation('partners', collect([$this->partnerA]));
    }

    /**
     * Sub-Partner A attempting to access Sub-Partner B's partner record is denied.
     */
    public function test_sub_partner_cannot_access_foreign_partner_record(): void
    {
        Route::bind('partner', fn ($id) => (int) $id === 20 ? $this->subPartnerB : $this->subPartnerA);

        $response = $this->actingAs($this->userSubPartnerA)->getJson('/partners/20');

        $this->assertTrue(
            in_array($response->getStatusCode(), [403, 404], true),
            "Expected 403 or 404 for IDOR partner access, got {$response->getStatusCode()}"
        );
    }

    /**
     * Sub-Partner A attempting to access Sub-Partner B's customer is denied.
     */
    public function test_sub_partner_cannot_access_foreign_customer(): void
    {
        $foreignCustomer = new Customer([
            'current_partner_id' => 2,
            'current_sub_partner_id' => 20,
            'status' => 'active',
        ]);
        $foreignCustomer->id = 500;

        Route::bind('customer', fn () => $foreignCustomer);

        $response = $this->actingAs($this->userSubPartnerA)->getJson('/customers/500');

        $this->assertTrue(
            in_array($response->getStatusCode(), [403, 404], true),
            "Expected 403 or 404 for IDOR customer access, got {$response->getStatusCode()}"
        );
    }

    /**
     * Sub-Partner A attempting to access Sub-Partner B's order is denied.
     */
    public function test_sub_partner_cannot_access_foreign_order(): void
    {
        $foreignOrder = new Order([
            'partner_id' => 2,
            'sub_partner_id' => 20,
            'status' => 'completed',
        ]);
        $foreignOrder->id = 600;

        Route::bind('order', fn () => $foreignOrder);

        $response = $this->actingAs($this->userSubPartnerA)->getJson('/orders/600');

        $this->assertTrue(
            in_array($response->getStatusCode(), [403, 404], true),
            "Expected 403 or 404 for IDOR order access, got {$response->getStatusCode()}"
        );
    }

    /**
     * Sub-Partner A attempting to access Sub-Partner B's subscription is denied.
     */
    public function test_sub_partner_cannot_access_foreign_subscription(): void
    {
        $foreignSub = new Subscription([
            'partner_id' => 2,
            'sub_partner_id' => 20,
            'status' => 'active',
        ]);
        $foreignSub->id = 700;

        Route::bind('subscription', fn () => $foreignSub);

        $response = $this->actingAs($this->userSubPartnerA)->getJson('/subscriptions/700');

        $this->assertTrue(
            in_array($response->getStatusCode(), [403, 404], true),
            "Expected 403 or 404 for IDOR subscription access, got {$response->getStatusCode()}"
        );
    }

    /**
     * Sub-Partner A attempting to access Sub-Partner B's auto-debit mandate is denied.
     */
    public function test_sub_partner_cannot_access_foreign_auto_debit_mandate(): void
    {
        $foreignSubscription = new Subscription([
            'partner_id' => 2,
            'sub_partner_id' => 20,
        ]);
        $foreignSubscription->id = 700;

        $foreignMandate = new AutoDebitMandate([
            'subscription_id' => 700,
            'status' => 'active',
        ]);
        $foreignMandate->id = 800;
        $foreignMandate->setRelation('subscription', $foreignSubscription);

        Route::bind('mandate', fn () => $foreignMandate);

        $response = $this->actingAs($this->userSubPartnerA)->getJson('/auto-debit-mandates/800');

        $this->assertTrue(
            in_array($response->getStatusCode(), [403, 404], true),
            "Expected 403 or 404 for IDOR mandate access, got {$response->getStatusCode()}"
        );
    }

    /**
     * Sub-Partner A attempting to access Sub-Partner B's renewal is denied.
     */
    public function test_sub_partner_cannot_access_foreign_renewal(): void
    {
        $foreignSubscription = new Subscription([
            'partner_id' => 2,
            'sub_partner_id' => 20,
        ]);
        $foreignSubscription->id = 700;

        $foreignRenewal = new Renewal([
            'subscription_id' => 700,
            'status' => 'pending',
        ]);
        $foreignRenewal->id = 900;
        $foreignRenewal->setRelation('subscription', $foreignSubscription);

        Route::bind('renewal', fn () => $foreignRenewal);

        $response = $this->actingAs($this->userSubPartnerA)->getJson('/renewals/900');

        $this->assertTrue(
            in_array($response->getStatusCode(), [403, 404], true),
            "Expected 403 or 404 for IDOR renewal access, got {$response->getStatusCode()}"
        );
    }

    /**
     * User A attempting to mark User B's notification as read is denied.
     */
    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $foreignNotification = new Notification([
            'type' => 'App\\Notifications\\SaleCompletedNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => 200,
            'data' => ['message' => 'For User B'],
        ]);
        $foreignNotification->id = '00000000-0000-0000-0000-000000000099';

        $this->userSubPartnerA->setRelation('accessibleNotifications', collect([$foreignNotification]));

        $response = $this->actingAs($this->userSubPartnerA)->postJson('/notifications/00000000-0000-0000-0000-000000000099/read');

        $this->assertTrue(
            in_array($response->getStatusCode(), [403, 404], true),
            "Expected 403 or 404 for foreign notification read, got {$response->getStatusCode()}"
        );
    }

    /**
     * Unauthenticated guest attempting to access protected domain resources is redirected to login.
     */
    public function test_unauthenticated_guest_is_redirected_to_login_on_resource_routes(): void
    {
        $routes = ['/customers', '/orders', '/subscriptions', '/auto-debit-mandates', '/renewals', '/notifications'];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertStatus(302);
            $response->assertRedirect('/login');
        }
    }
}
