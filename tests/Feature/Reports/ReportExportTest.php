<?php

namespace Tests\Feature\Reports;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Partners\Partner;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    public function test_unauthenticated_request_to_export_redirects_to_login(): void
    {
        $response = $this->get('/reports/sales/export/csv');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_json_request_to_export_returns_401(): void
    {
        $response = $this->getJson('/reports/sales/export/csv');

        $response->assertStatus(401);
    }

    public function test_inactive_user_is_blocked_from_export(): void
    {
        $inactiveUser = new User([
            'id' => 99,
            'name' => 'Inactive User',
            'email' => 'inactive@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'inactive',
        ]);

        $response = $this->actingAs($inactiveUser)->getJson('/reports/sales/export/csv');

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Account is inactive.']);
    }

    public function test_unauthorized_role_cannot_export_reports(): void
    {
        $user = new User([
            'id' => 4,
            'name' => 'Generic Partner User',
            'email' => 'generic@example.com',
            'role' => Role::PARTNER_USER->value,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->getJson('/reports/sales/export/csv');

        $response->assertStatus(403);
    }

    public function test_invalid_export_format_returns_422_for_sales(): void
    {
        $admin = new User([
            'id' => 1,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->getJson('/reports/sales/export/invalid_format');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['format']);
    }

    public function test_invalid_export_format_returns_422_for_customers(): void
    {
        $admin = new User([
            'id' => 1,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->getJson('/reports/customers/export/xml');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['format']);
    }

    public function test_invalid_export_format_returns_422_for_renewals(): void
    {
        $admin = new User([
            'id' => 1,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->getJson('/reports/renewals/export/doc');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['format']);
    }

    public function test_valid_export_format_csv_returns_501_blocked_stub_for_sales(): void
    {
        $admin = new User([
            'id' => 1,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->getJson('/reports/sales/export/csv');

        $response->assertStatus(501);
        $response->assertJson([
            'message' => 'Sales report export is blocked pending business confirmation.',
            'format' => 'csv',
            'blocked' => true,
            'blocked_items' => [
                'BC-10-01',
                'BC-10-02',
                'BC-10-05',
                'BC-10-06',
                'BC-10-07',
                'BC-10-08',
                'BC-10-09',
                'BC-10-10',
            ],
        ]);
    }

    public function test_valid_export_format_excel_returns_501_blocked_stub_for_sales(): void
    {
        $partner = new Partner([
            'id' => 10,
            'partner_code' => 'MAIN-001',
            'name' => 'Main Partner Inc',
            'type' => 'main',
            'status' => 'active',
        ]);

        $mainUser = new User([
            'id' => 2,
            'name' => 'Main Partner User',
            'email' => 'main@example.com',
            'role' => Role::MAIN_PARTNER->value,
            'status' => 'active',
        ]);
        $mainUser->setRelation('partners', collect([$partner]));

        $response = $this->actingAs($mainUser)->getJson('/reports/sales/export/excel');

        $response->assertStatus(501);
        $response->assertJson([
            'format' => 'excel',
            'blocked' => true,
        ]);
    }

    public function test_valid_export_format_pdf_returns_501_blocked_stub_for_sales(): void
    {
        $subPartner = new Partner([
            'id' => 20,
            'partner_code' => 'SUB-001',
            'name' => 'Sub Partner LLC',
            'type' => 'sub',
            'parent_partner_id' => 10,
            'status' => 'active',
        ]);

        $subUser = new User([
            'id' => 3,
            'name' => 'Sub Partner User',
            'email' => 'sub@example.com',
            'role' => Role::SUB_PARTNER->value,
            'status' => 'active',
        ]);
        $subUser->setRelation('partners', collect([$subPartner]));

        $response = $this->actingAs($subUser)->getJson('/reports/sales/export/pdf');

        $response->assertStatus(501);
        $response->assertJson([
            'format' => 'pdf',
            'blocked' => true,
        ]);
    }

    public function test_valid_export_formats_return_501_blocked_stub_for_customers(): void
    {
        $admin = new User([
            'id' => 1,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);

        foreach (['csv', 'excel', 'pdf'] as $format) {
            $response = $this->actingAs($admin)->getJson("/reports/customers/export/{$format}");

            $response->assertStatus(501);
            $response->assertJson([
                'format' => $format,
                'blocked' => true,
                'blocked_items' => [
                    'BC-10-01',
                    'BC-10-03',
                    'BC-10-05',
                    'BC-10-07',
                    'BC-10-08',
                    'BC-10-09',
                    'BC-10-10',
                ],
            ]);
        }
    }

    public function test_valid_export_formats_return_501_blocked_stub_for_renewals(): void
    {
        $admin = new User([
            'id' => 1,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);

        foreach (['csv', 'excel', 'pdf'] as $format) {
            $response = $this->actingAs($admin)->getJson("/reports/renewals/export/{$format}");

            $response->assertStatus(501);
            $response->assertJson([
                'format' => $format,
                'blocked' => true,
                'blocked_items' => [
                    'BC-10-01',
                    'BC-10-04',
                    'BC-10-05',
                    'BC-10-06',
                    'BC-10-07',
                    'BC-10-08',
                    'BC-10-09',
                    'BC-10-10',
                ],
            ]);
        }
    }
}
