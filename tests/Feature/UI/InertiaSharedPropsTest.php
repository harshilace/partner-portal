<?php

namespace Tests\Feature\UI;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InertiaSharedPropsTest extends TestCase
{
    /**
     * Authenticated user receives auth.user shared props via HandleInertiaRequests.
     */
    public function test_authenticated_user_shares_auth_props(): void
    {
        $user = new User([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);
        $user->id = 1;

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->has('auth.user')
            ->where('auth.user.id', 1)
            ->where('auth.user.name', 'Admin User')
            ->where('auth.user.email', 'admin@example.com')
            ->where('auth.user.role', 'admin')
            ->where('auth.user.status', 'active')
        );
    }

    /**
     * Unauthenticated request receives null auth.user shared prop.
     */
    public function test_unauthenticated_request_shares_null_user(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->where('auth.user', null)
        );
    }
}
