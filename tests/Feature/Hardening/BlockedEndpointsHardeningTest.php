<?php

namespace Tests\Feature\Hardening;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Customers\Customer;
use App\Domain\Leads\Lead;
use App\Domain\Partners\Partner;
use App\Policies\AutoDebitMandatePolicy;
use App\Policies\CustomerPolicy;
use App\Policies\LeadPolicy;
use App\Policies\PartnerPolicy;
use Tests\TestCase;

class BlockedEndpointsHardeningTest extends TestCase
{
    protected User $admin;

    protected User $mainPartnerUser;

    protected User $subPartnerUser;

    protected Partner $mainPartner;

    protected Partner $subPartner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = new User(['id' => 1, 'role' => Role::ADMIN->value, 'status' => 'active']);

        $this->mainPartner = new Partner(['type' => 'main', 'status' => 'active']);
        $this->mainPartner->id = 10;

        $this->subPartner = new Partner(['type' => 'sub', 'parent_partner_id' => 10, 'status' => 'active']);
        $this->subPartner->id = 20;

        $this->mainPartnerUser = new User(['id' => 2, 'role' => Role::MAIN_PARTNER->value, 'status' => 'active']);
        $this->mainPartnerUser->setRelation('partners', collect([$this->mainPartner]));

        $this->subPartnerUser = new User(['id' => 3, 'role' => Role::SUB_PARTNER->value, 'status' => 'active']);
        $this->subPartnerUser->setRelation('partners', collect([$this->subPartner]));
    }

    /**
     * Reports retrieval endpoints strictly return HTTP 501 Not Implemented across authorized roles.
     */
    public function test_reports_retrieval_endpoints_strictly_return_501(): void
    {
        $reportRoutes = [
            '/reports/sales',
            '/reports/customers',
            '/reports/renewals',
        ];

        foreach ([$this->admin, $this->mainPartnerUser, $this->subPartnerUser] as $user) {
            foreach ($reportRoutes as $route) {
                $response = $this->actingAs($user)->getJson($route);

                $this->assertEquals(
                    501,
                    $response->getStatusCode(),
                    "Route [{$route}] did not return 501 for role [{$user->role}]."
                );
            }
        }
    }

    /**
     * Reports export endpoints strictly return HTTP 501 Not Implemented across authorized formats.
     */
    public function test_reports_export_endpoints_strictly_return_501(): void
    {
        $exportRoutes = [
            '/reports/sales/export/csv',
            '/reports/customers/export/excel',
            '/reports/renewals/export/pdf',
        ];

        foreach ($exportRoutes as $route) {
            $response = $this->actingAs($this->admin)->getJson($route);

            $this->assertEquals(
                501,
                $response->getStatusCode(),
                "Export route [{$route}] did not return 501."
            );
        }
    }

    /**
     * Audit endpoint returns HTTP 501 for Admin and HTTP 403 for non-Admin users.
     */
    public function test_audit_endpoint_preserves_501_for_admin_and_403_for_partners(): void
    {
        // Admin gets 501 blocked stub
        $adminResponse = $this->actingAs($this->admin)->getJson('/audit');
        $adminResponse->assertStatus(501);

        // Main Partner receives temporary security default 403
        $mainResponse = $this->actingAs($this->mainPartnerUser)->getJson('/audit');
        $mainResponse->assertStatus(403);

        // Sub-Partner receives temporary security default 403
        $subResponse = $this->actingAs($this->subPartnerUser)->getJson('/audit');
        $subResponse->assertStatus(403);
    }

    /**
     * Manual entity creation policies remain strictly blocked.
     */
    public function test_manual_entity_creation_policies_are_blocked(): void
    {
        $customerPolicy = new CustomerPolicy;
        $leadPolicy = new LeadPolicy;
        $mandatePolicy = new AutoDebitMandatePolicy;

        // Customer manual creation is blocked for all roles
        $this->assertFalse($customerPolicy->create($this->admin));
        $this->assertFalse($customerPolicy->create($this->mainPartnerUser));
        $this->assertFalse($customerPolicy->create($this->subPartnerUser));

        // Lead manual creation is blocked for all roles
        $this->assertFalse($leadPolicy->create($this->admin));
        $this->assertFalse($leadPolicy->create($this->mainPartnerUser));
        $this->assertFalse($leadPolicy->create($this->subPartnerUser));

        // Auto-debit mandate manual creation is blocked for all roles
        $this->assertFalse($mandatePolicy->create($this->admin));
        $this->assertFalse($mandatePolicy->create($this->mainPartnerUser));
        $this->assertFalse($mandatePolicy->create($this->subPartnerUser));
    }

    /**
     * Partner deletion remains strictly blocked across all roles.
     */
    public function test_partner_deletion_is_blocked_for_all_roles(): void
    {
        $partnerPolicy = new PartnerPolicy;

        $this->assertFalse($partnerPolicy->delete($this->admin, $this->mainPartner));
        $this->assertFalse($partnerPolicy->delete($this->mainPartnerUser, $this->mainPartner));
        $this->assertFalse($partnerPolicy->delete($this->subPartnerUser, $this->subPartner));
    }
}
