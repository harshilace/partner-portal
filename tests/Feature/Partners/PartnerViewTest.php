<?php

namespace Tests\Feature\Partners;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use App\Domain\Partners\GetPartnersQuery;
use App\Domain\Partners\Partner;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PartnerViewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $mockQuery = $this->createMock(GetPartnersQuery::class);
        $mockQuery->method('execute')->willReturn(collect([
            new Partner([
                'id' => 1,
                'partner_code' => 'PARTNER-MAIN',
                'name' => 'Main Partner Organization',
                'type' => 'main',
                'status' => 'active',
            ]),
        ]));

        $this->app->instance(GetPartnersQuery::class, $mockQuery);
    }

    /**
     * Unauthenticated user is redirected to login.
     */
    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/partners');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Admin user receives Inertia Partners/Index view with partners collection.
     */
    public function test_admin_receives_inertia_partners_index_view(): void
    {
        $admin = new User([
            'id' => 1,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get('/partners');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Partners/Index')
            ->has('partners')
        );
    }

    /**
     * Main Partner user receives Inertia Partners/Index view scoped to their partner hierarchy.
     */
    public function test_main_partner_receives_inertia_partners_index_view(): void
    {
        $mainPartner = new Partner([
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
        $mainUser->setRelation('partners', collect([$mainPartner]));

        $response = $this->actingAs($mainUser)->get('/partners');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Partners/Index')
            ->has('partners')
        );
    }

    /**
     * Pure JSON requests without X-Inertia continue to receive JsonResponse.
     */
    public function test_json_request_receives_json_response(): void
    {
        $admin = new User([
            'id' => 1,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->getJson('/partners');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    }
}
