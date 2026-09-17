<?php

namespace App\Domain\Customers\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\User;
use App\Domain\Customers\Customer;
use App\Domain\Customers\CustomerPartnerAttribution;
use App\Domain\Partners\Partner;
use DomainException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ChangeCustomerPartnerMappingAction
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    /**
     * Execute Admin Partner Mapping Change workflow.
     * Section 14 transaction: Lock Customer -> Close old attribution -> Create new attribution -> Update current mapping -> Create Audit.
     *
     * @throws DomainException|RuntimeException
     */
    public function execute(
        Customer $customer,
        ?Partner $newPartner = null,
        ?Partner $newSubPartner = null,
        ?User $actor = null
    ): Customer {
        // Remapping to unassigned is blocked (NEEDS BUSINESS CONFIRMATION #8)
        if ($newPartner === null) {
            throw new RuntimeException(
                'Behavior when remapping a customer to unassigned is not confirmed — NEEDS BUSINESS CONFIRMATION #8'
            );
        }

        // Unassigned customer mapping workflow is blocked (NEEDS BUSINESS CONFIRMATION #6)
        if ($customer->current_partner_id === null) {
            throw new RuntimeException(
                'Behavior when mapping an unassigned customer to a partner is not confirmed — NEEDS BUSINESS CONFIRMATION #6'
            );
        }

        // Mapping to inactive/deactivated partner is blocked (NEEDS BUSINESS CONFIRMATION #7)
        if ($newPartner->status !== 'active' || ($newSubPartner && $newSubPartner->status !== 'active')) {
            throw new RuntimeException(
                'Behavior when mapping a customer to an inactive/deactivated partner is not confirmed — NEEDS BUSINESS CONFIRMATION #7'
            );
        }

        // Partner hierarchy and type validation
        if ($newPartner->type !== 'main') {
            throw new DomainException('Primary partner must be a Main Partner.');
        }

        if ($newSubPartner !== null) {
            if ($newSubPartner->type !== 'sub') {
                throw new DomainException('Secondary partner must be a Sub-Partner.');
            }

            if ((int) $newSubPartner->parent_partner_id !== (int) $newPartner->id) {
                throw new DomainException('Sub-partner does not belong to the selected main partner.');
            }
        }

        // Idempotency / unchanged mapping check
        $currentSubPartnerId = $customer->current_sub_partner_id ? (int) $customer->current_sub_partner_id : null;
        $targetSubPartnerId = $newSubPartner ? (int) $newSubPartner->id : null;

        if ((int) $customer->current_partner_id === (int) $newPartner->id && $currentSubPartnerId === $targetSubPartnerId) {
            throw new DomainException('Customer is already mapped to this partner configuration.');
        }

        return DB::transaction(function () use ($customer, $newPartner, $actor, $targetSubPartnerId) {
            // Step 1: Lock Customer
            $this->lockCustomer($customer->id);

            $oldPartnerId = $customer->current_partner_id;
            $oldSubPartnerId = $customer->current_sub_partner_id;
            $timestamp = now();

            // Step 2: Close old attribution
            $this->closeOpenAttributions($customer->id, $timestamp);

            // Step 3: Create new attribution
            $this->createAttribution([
                'customer_id' => $customer->id,
                'partner_id' => $newPartner->id,
                'sub_partner_id' => $targetSubPartnerId,
                'referral_code_id' => null,
                'starts_at' => $timestamp,
                'ends_at' => null,
                'changed_by_user_id' => $actor?->id,
            ]);

            // Step 4: Update current mapping
            $this->updateCustomerMapping($customer, $newPartner->id, $targetSubPartnerId);

            // Step 5: Create Audit
            $this->auditLogger->log(
                event: 'customer.mapping_changed',
                user: $actor,
                auditable: $customer,
                oldValues: [
                    'current_partner_id' => $oldPartnerId,
                    'current_sub_partner_id' => $oldSubPartnerId,
                ],
                newValues: [
                    'current_partner_id' => $newPartner->id,
                    'current_sub_partner_id' => $targetSubPartnerId,
                ]
            );

            return $customer;
        });
    }

    /**
     * Lock the customer row for update within the transaction.
     */
    protected function lockCustomer(int|string $customerId): ?Customer
    {
        return Customer::where('id', $customerId)->lockForUpdate()->first();
    }

    /**
     * Close all open attributions for the customer.
     */
    protected function closeOpenAttributions(int|string $customerId, mixed $timestamp): int
    {
        return CustomerPartnerAttribution::where('customer_id', $customerId)
            ->whereNull('ends_at')
            ->update(['ends_at' => $timestamp]);
    }

    /**
     * Create the new attribution record.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function createAttribution(array $attributes): CustomerPartnerAttribution
    {
        return CustomerPartnerAttribution::create($attributes);
    }

    /**
     * Update the customer's current partner mapping.
     */
    protected function updateCustomerMapping(Customer $customer, int|string $partnerId, int|string|null $subPartnerId): void
    {
        $customer->current_partner_id = $partnerId;
        $customer->current_sub_partner_id = $subPartnerId;
        $customer->save();
    }
}
