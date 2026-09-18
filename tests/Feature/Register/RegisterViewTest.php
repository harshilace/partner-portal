<?php

namespace Tests\Feature\Register;

use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RegisterViewTest extends TestCase
{
    /**
     * Guest can access the register page without partner referral code.
     */
    public function test_guest_can_access_register_page(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Register')
            ->where('partner', null)
        );
    }

    /**
     * Guest accessing register with ?partner=CODE receives the partner prop.
     */
    public function test_register_page_receives_partner_code_prop(): void
    {
        $response = $this->get('/register?partner=PARTNER123');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Register')
            ->where('partner', 'PARTNER123')
        );
    }
}
