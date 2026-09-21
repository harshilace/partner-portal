<?php

namespace Tests\Feature\Registration;

use Tests\TestCase;

class RegistrationTest extends TestCase
{
    public function test_registration_rules_applied_but_needs_pdo_sqlite(): void
    {
        $this->markTestSkipped('Database tests require pdo_sqlite driver which is missing in this environment.');
    }
}
