<?php

namespace App\Domain\Partners\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\User;
use App\Domain\Partners\Partner;
use App\Domain\Partners\PartnerUser;
use DomainException;
use Illuminate\Support\Facades\DB;

class RemoveUserFromPartnerAction
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    /**
     * Remove a user from a partner.
     * Deletes the partner_users pivot record only. Never deletes the users record.
     * Admin-only authority pending confirmation (NEEDS BUSINESS CONFIRMATION #11).
     *
     * @throws DomainException
     */
    public function execute(Partner $partner, User $user, ?User $actor = null): bool
    {
        $partnerUser = $this->findAssignment($partner->id, $user->id);

        if (! $partnerUser) {
            throw new DomainException('User is not assigned to this partner.');
        }

        return DB::transaction(function () use ($partner, $user, $partnerUser, $actor) {
            $this->deleteAssignment($partnerUser);

            $this->auditLogger->log(
                event: 'partner.user_removed',
                user: $actor,
                auditable: $partner,
                oldValues: [
                    'partner_id' => $partner->id,
                    'user_id' => $user->id,
                ]
            );

            return true;
        });
    }

    /**
     * Find existing partner-user assignment.
     */
    protected function findAssignment(int|string $partnerId, int|string $userId): ?PartnerUser
    {
        return PartnerUser::where('partner_id', $partnerId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Delete the partner-user pivot record.
     */
    protected function deleteAssignment(PartnerUser $partnerUser): bool
    {
        return (bool) $partnerUser->delete();
    }
}
