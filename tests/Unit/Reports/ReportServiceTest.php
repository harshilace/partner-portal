<?php

namespace Tests\Unit\Reports;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Reports\Services\ReportService;
use App\Policies\ReportPolicy;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReportServiceTest extends TestCase
{
    public function test_sales_report_returns_blocked_501_stub_without_executing_queries(): void
    {
        DB::enableQueryLog();

        $user = new User([
            'id' => 1,
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);

        $service = new ReportService;
        $result = $service->salesReport($user, ['from' => '2026-01-01']);

        $this->assertSame(501, $result['status']);
        $this->assertTrue($result['blocked']);
        $this->assertContains('BC-10-01', $result['blocked_items']);
        $this->assertContains('BC-10-02', $result['blocked_items']);
        $this->assertContains('BC-10-05', $result['blocked_items']);
        $this->assertContains('BC-10-06', $result['blocked_items']);
        $this->assertContains('BC-10-09', $result['blocked_items']);
        $this->assertEmpty(DB::getQueryLog(), 'ReportService::salesReport must not execute any database queries.');
    }

    public function test_customer_report_returns_blocked_501_stub_without_executing_queries(): void
    {
        DB::enableQueryLog();

        $user = new User([
            'id' => 2,
            'role' => Role::MAIN_PARTNER->value,
            'status' => 'active',
        ]);

        $service = new ReportService;
        $result = $service->customerReport($user);

        $this->assertSame(501, $result['status']);
        $this->assertTrue($result['blocked']);
        $this->assertContains('BC-10-01', $result['blocked_items']);
        $this->assertContains('BC-10-03', $result['blocked_items']);
        $this->assertContains('BC-10-05', $result['blocked_items']);
        $this->assertContains('BC-10-09', $result['blocked_items']);
        $this->assertEmpty(DB::getQueryLog(), 'ReportService::customerReport must not execute any database queries.');
    }

    public function test_renewal_report_returns_blocked_501_stub_without_executing_queries(): void
    {
        DB::enableQueryLog();

        $user = new User([
            'id' => 3,
            'role' => Role::SUB_PARTNER->value,
            'status' => 'active',
        ]);

        $service = new ReportService;
        $result = $service->renewalReport($user);

        $this->assertSame(501, $result['status']);
        $this->assertTrue($result['blocked']);
        $this->assertContains('BC-10-01', $result['blocked_items']);
        $this->assertContains('BC-10-04', $result['blocked_items']);
        $this->assertContains('BC-10-05', $result['blocked_items']);
        $this->assertContains('BC-10-06', $result['blocked_items']);
        $this->assertContains('BC-10-09', $result['blocked_items']);
        $this->assertEmpty(DB::getQueryLog(), 'ReportService::renewalReport must not execute any database queries.');
    }

    public function test_export_delegation_returns_501_for_all_supported_formats(): void
    {
        $user = new User([
            'id' => 1,
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);

        $service = new ReportService;

        foreach (['sales', 'customers', 'renewals'] as $report) {
            foreach (['excel', 'csv', 'pdf'] as $format) {
                $response = $service->export($report, $format, $user);
                $this->assertSame(501, $response->getStatusCode());
                $data = $response->getData(true);
                $this->assertTrue($data['blocked']);
                $this->assertSame($format, $data['format']);
            }
        }
    }

    public function test_report_policy_role_authorizations(): void
    {
        $policy = new ReportPolicy;

        $admin = new User(['id' => 1, 'role' => Role::ADMIN->value, 'status' => 'active']);
        $mainPartner = new User(['id' => 2, 'role' => Role::MAIN_PARTNER->value, 'status' => 'active']);
        $subPartner = new User(['id' => 3, 'role' => Role::SUB_PARTNER->value, 'status' => 'active']);
        $partnerUser = new User(['id' => 4, 'role' => Role::PARTNER_USER->value, 'status' => 'active']);
        $inactiveAdmin = new User(['id' => 5, 'role' => Role::ADMIN->value, 'status' => 'inactive']);
        $inactiveMain = new User(['id' => 6, 'role' => Role::MAIN_PARTNER->value, 'status' => 'inactive']);

        $this->assertTrue($policy->before($admin, 'viewAny'));
        $this->assertFalse($policy->before($inactiveAdmin, 'viewAny'));
        $this->assertNull($policy->before($mainPartner, 'viewAny'));

        $this->assertTrue($policy->viewAny($mainPartner));
        $this->assertTrue($policy->viewAny($subPartner));
        $this->assertFalse($policy->viewAny($partnerUser));
        $this->assertFalse($policy->viewAny($inactiveMain));
    }
}
