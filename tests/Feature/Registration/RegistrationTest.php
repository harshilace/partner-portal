<?php

namespace Tests\Feature\Registration;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Customers\Customer;
use App\Domain\Leads\Actions\RegisterViaReferralAction;
use App\Domain\Referrals\Actions\ResolveReferralCodeAction;
use App\Domain\Referrals\ReferralCode;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    public function test_new_customer_registration_throws_for_unconfirmed_customer_code_generation(): void
    {
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());

        $resolver = $this->createMock(ResolveReferralCodeAction::class);
        $resolver->method('execute')->willReturn(new ReferralCode(['code' => 'P123', 'is_active' => true]));

        $logger = $this->createMock(AuditLogger::class);

        $action = new class($resolver, $logger) extends RegisterViaReferralAction
        {
            protected function findCustomer(?string $emailNormalized, ?string $mobileNormalized): ?Customer
            {
                return null;
            }
        };

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('NEEDS BUSINESS CONFIRMATION #8');

        $action->execute([
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'mobile' => '1234567890',
        ], 'P123');
    }

    public function test_existing_customer_registration_throws_for_unconfirmed_existing_customer_behavior(): void
    {
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());

        $resolver = $this->createMock(ResolveReferralCodeAction::class);
        $resolver->method('execute')->willReturn(new ReferralCode(['code' => 'P123', 'is_active' => true]));

        $logger = $this->createMock(AuditLogger::class);

        $action = new class($resolver, $logger) extends RegisterViaReferralAction
        {
            protected function findCustomer(?string $emailNormalized, ?string $mobileNormalized): ?Customer
            {
                return new Customer([
                    'email_normalized' => $emailNormalized,
                    'mobile_normalized' => $mobileNormalized,
                ]);
            }
        };

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('NEEDS BUSINESS CONFIRMATION #9 / #10');

        $action->execute([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'mobile' => '9876543210',
        ], 'P123');
    }

    public function test_unassigned_new_customer_registration_also_throws_for_customer_code(): void
    {
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());

        $resolver = $this->createMock(ResolveReferralCodeAction::class);
        $resolver->method('execute')->willReturn(null);

        $logger = $this->createMock(AuditLogger::class);

        $action = new class($resolver, $logger) extends RegisterViaReferralAction
        {
            protected function findCustomer(?string $emailNormalized, ?string $mobileNormalized): ?Customer
            {
                return null;
            }
        };

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('NEEDS BUSINESS CONFIRMATION #8');

        $action->execute([
            'name' => 'Charlie',
            'email' => 'charlie@example.com',
            'mobile' => '5551234567',
        ], null);
    }
}
