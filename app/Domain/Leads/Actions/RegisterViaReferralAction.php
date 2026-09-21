<?php

namespace App\Domain\Leads\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Customers\Customer;
use App\Domain\Leads\Lead;
use App\Domain\Referrals\Actions\ResolveReferralCodeAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
                // Existing customer clicks another referral: Keep existing Partner
                $lead = Lead::create([
                    'name' => $data['name'] ?? $existingCustomer->name,
                    'email' => $data['email'] ?? $existingCustomer->email,
                    'email_normalized' => $emailNormalized,
                    'mobile' => $data['mobile'] ?? $existingCustomer->mobile,
                    'mobile_normalized' => $mobileNormalized,
                    'partner_id' => $existingCustomer->current_partner_id,
                    'sub_partner_id' => $existingCustomer->current_sub_partner_id,
                    'customer_id' => $existingCustomer->id,
                    'status' => 'new',
                ]);

                $lead->statusHistories()->create([
                    'from_status' => null,
                    'to_status' => 'new',
                    'changed_by_user_id' => null,
                    'remarks' => 'Existing customer re-registered (Kept existing partner mapping).',
                ]);

                return $existingCustomer;
            }

            // Generate unique customer code
            do {
                $customerCode = 'CUST-'.strtoupper(Str::random(8));
            } while (Customer::where('customer_code', $customerCode)->exists());

            $partnerId = $resolvedReferral?->partner_id;
            $subPartnerId = $resolvedReferral?->sub_partner_id;

            $customer = Customer::create([
                'customer_code' => $customerCode,
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'email_normalized' => $emailNormalized,
                'mobile' => $data['mobile'] ?? null,
                'mobile_normalized' => $mobileNormalized,
                'current_partner_id' => $partnerId,
                'current_sub_partner_id' => $subPartnerId,
                'status' => 'active',
            ]);

            if ($partnerId) {
                $customer->attributions()->create([
                    'partner_id' => $partnerId,
                    'sub_partner_id' => $subPartnerId,
                    'referral_code_id' => $resolvedReferral?->id,
                    'starts_at' => now(),
                    'changed_by_user_id' => null,
                ]);
            }

            $lead = Lead::create([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'email_normalized' => $emailNormalized,
                'mobile' => $data['mobile'] ?? null,
                'mobile_normalized' => $mobileNormalized,
                'partner_id' => $partnerId,
                'sub_partner_id' => $subPartnerId,
                'customer_id' => $customer->id,
                'status' => 'new',
            ]);

            $lead->statusHistories()->create([
                'from_status' => null,
                'to_status' => 'new',
                'changed_by_user_id' => null,
                'remarks' => 'Registered '.($resolvedReferral ? 'via referral' : 'unassigned').'.',
            ]);

            return $customer;
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
