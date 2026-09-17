<?php

namespace App\Domain\Leads\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Customers\Customer;
use App\Domain\Referrals\Actions\ResolveReferralCodeAction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RegisterViaReferralAction
{
    public function __construct(
        protected ResolveReferralCodeAction $resolveReferralCodeAction,
        protected AuditLogger $auditLogger
    ) {}

    /**
     * Execute the referral/unassigned registration transaction.
     * Blocked by unresolved business rules (NEEDS BUSINESS CONFIRMATION #8, #9, #10).
     *
     * @param  array<string, mixed>  $data
     *
     * @throws RuntimeException
     */
    public function execute(array $data, ?string $referralCode = null): mixed
    {
        return DB::transaction(function () use ($data, $referralCode) {
            // Step 1: Resolve referral code (may throw for #5, #6, #7)
            $resolvedReferral = $this->resolveReferralCodeAction->execute($referralCode);

            // Step 2: Normalize customer identity and detect duplicate
            $emailNormalized = Customer::normalizeEmail($data['email'] ?? null);
            $mobileNormalized = Customer::normalizeMobile($data['mobile'] ?? null);

            $existingCustomer = $this->findCustomer($emailNormalized, $mobileNormalized);

            if ($existingCustomer) {
                throw new RuntimeException(
                    'Behavior when existing customer uses a referral link is not confirmed — NEEDS BUSINESS CONFIRMATION #9 / #10'
                );
            }

            // New customer registration path blocked pending customer_code generation format
            throw new RuntimeException(
                'customer_code generation policy not confirmed — NEEDS BUSINESS CONFIRMATION #8'
            );
        });
    }

    /**
     * Find an existing customer by normalized identity.
     */
    protected function findCustomer(?string $emailNormalized, ?string $mobileNormalized): ?Customer
    {
        if (! $emailNormalized || ! $mobileNormalized) {
            return null;
        }

        return Customer::where('email_normalized', $emailNormalized)
            ->where('mobile_normalized', $mobileNormalized)
            ->first();
    }
}
