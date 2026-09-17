<?php

namespace App\Domain\Partners\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\User;
use App\Domain\Partners\Partner;
use App\Domain\Partners\PartnerUser;
use DomainException;
use Illuminate\Support\Facades\DB;

class AssignUserToPartnerAction
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    /**
     * Assign a user to a partner.
     * Admin-only authority pending confirmation (NEEDS BUSINESS CONFIRMATION #11).
     * Does NOT modify users.role (NEEDS BUSINESS CONFIRMATION #13).
     *
     * @throws DomainException
     */
    public function execute(Partner $partner, User $user, ?User $actor = null): PartnerUser
    {
        $existing = $this->assignmentExists($partner->id, $user->id);

        if ($existing) {
            throw new DomainException('User is already assigned to this partner.');
        }

        return DB::transaction(function () use ($partner, $user, $actor) {
            $partnerUser = $this->createAssignment($partner->id, $user->id);

            $this->auditLogger->log(
                event: 'partner.user_assigned',
                user: $actor,
                auditable: $partner,
                newValues: [
                    'partner_id' => $partner->id,
                    'user_id' => $user->id,
                ]
            );

            return $partnerUser;
        });
    }

    /**
     * Check if the partner-user assignment already exists.
     */
    protected function assignmentExists(int|string $partnerId, int|string $userId): bool
    {
        return PartnerUser::where('partner_id', $partnerId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Create the partner-user record.
     */
    protected function createAssignment(int|string $partnerId, int|string $userId): PartnerUser
    {
        return PartnerUser::create([
            'partner_id' => $partnerId,
            'user_id' => $userId,
        ]);
    }
}
