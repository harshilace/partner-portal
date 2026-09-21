<?php

namespace Tests\Feature\Hardening;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Customers\Customer;
use App\Domain\Partners\Partner;
use App\Domain\Products\Product;
use App\Domain\Subscriptions\AutoDebitMandate;
use App\Domain\Subscriptions\Subscription;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ServerSideAuthorizationHardeningTest extends TestCase
{
    protected User $mainPartnerUser;

    protected User $subPartnerUser;

    protected Partner $mainPartner;

    protected Partner $subPartner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mainPartner = new Partner(['type' => 'main', 'status' => 'active']);
        $this->mainPartner->id = 1;

        $this->subPartner = new Partner(['type' => 'sub', 'parent_partner_id' => 1, 'status' => 'active']);
        $this->subPartner->id = 10;

        $this->mainPartnerUser = new User(['role' => Role::MAIN_PARTNER->value, 'status' => 'active']);
        $this->mainPartnerUser->id = 101;
        $this->mainPartnerUser->setRelation('partners', collect([$this->mainPartner]));

        $this->subPartnerUser = new User(['role' => Role::SUB_PARTNER->value, 'status' => 'active']);
        $this->subPartnerUser->id = 102;
        $this->subPartnerUser->setRelation('partners', collect([$this->subPartner]));

        // Bind models for route model binding
        Route::bind('partner', fn ($id) => (int) $id === 10 ? $this->subPartner : $this->mainPartner);

        Route::bind('customer', function ($id) {
            $c = new Customer(['current_partner_id' => 1, 'current_sub_partner_id' => 10, 'status' => 'active']);
            $c->id = (int) $id;

            return $c;
        });

        Route::bind('subscription', function ($id) {
            $s = new Subscription(['partner_id' => 1, 'sub_partner_id' => 10, 'status' => 'active']);
            $s->id = (int) $id;

            return $s;
        });

        Route::bind('mandate', function ($id) {
            $m = new AutoDebitMandate(['status' => 'active']);
            $m->id = (int) $id;
            $m->setRelation('subscription', new Subscription(['partner_id' => 1, 'sub_partner_id' => 10]));

            return $m;
        });
    }

    /**
     * Sub-Partner attempting direct POST /partners is denied.
     */
    public function test_sub_partner_cannot_create_partner_via_direct_post(): void
    {
        $response = $this->actingAs($this->subPartnerUser)->postJson('/partners', [
            'name' => 'Forged Partner',
            'partner_code' => 'PRT-FORGED',
            'type' => 'main',
        ]);

        $this->assertTrue(
            in_array($response->getStatusCode(), [403, 404], true),
            "Expected 403 or 404, got {$response->getStatusCode()}"
        );
    }

    /**
     * Sub-Partner attempting direct POST /partners/{id}/sub-partners is denied.
     */
    public function test_sub_partner_cannot_create_sub_partner_via_direct_post(): void
    {
        $response = $this->actingAs($this->subPartnerUser)->postJson('/partners/10/sub-partners', [
            'name' => 'Forged Sub-Partner',
            'partner_code' => 'SUB-FORGED',
        ]);

        $this->assertTrue(
            in_array($response->getStatusCode(), [403, 404], true),
            "Expected 403 or 404, got {$response->getStatusCode()}"
        );
    }

    /**
     * Non-Admin attempting DELETE /partners/{id} is denied.
     */
    public function test_non_admin_cannot_delete_partner_via_direct_delete(): void
    {
        $response = $this->actingAs($this->mainPartnerUser)->deleteJson('/partners/1');

        $this->assertTrue(
            in_array($response->getStatusCode(), [403, 404], true),
            "Expected 403 or 404, got {$response->getStatusCode()}"
        );
    }

    /**
     * Non-Admin attempting to create a product is strictly denied server-side.
     */
    public function test_non_admin_cannot_create_product_server_side(): void
    {
        $this->assertFalse(
            Gate::forUser($this->mainPartnerUser)->allows('create', Product::class),
            'Main Partner should not be authorized to create products.'
        );

        $this->assertFalse(
            Gate::forUser($this->subPartnerUser)->allows('create', Product::class),
            'Sub-Partner should not be authorized to create products.'
        );
    }

    /**
     * Non-Admin attempting POST /subscriptions/{id}/cancel is denied.
     */
    public function test_non_admin_cannot_cancel_subscription_via_direct_post(): void
    {
        $response = $this->actingAs($this->subPartnerUser)->postJson('/subscriptions/1/cancel', [
            'reason' => 'Unauthorized cancellation attempt',
        ]);

        $this->assertTrue(
            in_array($response->getStatusCode(), [403, 404], true),
            "Expected 403 or 404, got {$response->getStatusCode()}"
        );
    }

    /**
     * Non-Admin attempting POST /auto-debit-mandates/{id}/stop is denied.
     */
    public function test_non_admin_cannot_stop_auto_debit_mandate_via_direct_post(): void
    {
        $response = $this->actingAs($this->mainPartnerUser)->postJson('/auto-debit-mandates/1/stop', [
            'reason' => 'Unauthorized stop attempt',
        ]);

        $this->assertTrue(
            in_array($response->getStatusCode(), [403, 404], true),
            "Expected 403 or 404, got {$response->getStatusCode()}"
        );
    }

    /**
     * Non-Admin attempting PUT /customers/{id}/mapping is denied.
     */
    public function test_non_admin_cannot_change_customer_mapping_via_direct_put(): void
    {
        $response = $this->actingAs($this->subPartnerUser)->putJson('/customers/1/mapping', [
            'partner_id' => 2,
        ]);

        $this->assertTrue(
            in_array($response->getStatusCode(), [403, 404], true),
            "Expected 403 or 404, got {$response->getStatusCode()}"
        );
    }
}
