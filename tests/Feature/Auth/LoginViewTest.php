<?php

namespace Tests\Feature\Auth;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Authentication\User;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LoginViewTest extends TestCase
{
    /**
     * Guest can access the login page and sees Auth/Login component.
     */
    public function test_guest_can_access_login_page(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Auth/Login')
        );
    }

    /**
     * Authenticated user is redirected away from login page by guest middleware.
     */
    public function test_authenticated_user_redirected_away_from_login(): void
    {
        $user = new User([
            'id' => 1,
            'name' => 'Active Admin',
            'email' => 'admin@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get('/login');

        $response->assertStatus(302);
    }

    /**
     * Guest visiting root is redirected to login page.
     */
    public function test_guest_visiting_root_is_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    /**
     * Authenticated user visiting root is redirected to dashboard.
     */
    public function test_authenticated_user_visiting_root_is_redirected_to_dashboard(): void
    {
        $user = new User([
            'id' => 1,
            'name' => 'Active Admin',
            'email' => 'admin@example.com',
            'role' => Role::ADMIN->value,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect('/dashboard');
    }
}
