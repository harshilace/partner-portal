<?php

namespace App\Domain\Referrals\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\User;
use App\Domain\Partners\Partner;
use App\Domain\Referrals\ReferralCode;
use DomainException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreateReferralCodeAction
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    /**
     * Create a referral code for a partner.
     * Admin-supplied code only. Auto-generation format is unconfirmed (NEEDS BUSINESS CONFIRMATION #1).
     *
     * @throws RuntimeException|DomainException
     */
    public function execute(
        Partner $partner,
        ?string $code = null,
        ?Partner $subPartner = null,
        ?User $actor = null
    ): ReferralCode {
        if ($code === null || trim($code) === '') {
            throw new RuntimeException(
                'Referral-code generation format not confirmed — NEEDS BUSINESS CONFIRMATION #1'
            );
        }

        $trimmedCode = trim($code);

        if ($this->codeExists($trimmedCode)) {
            throw new DomainException('Referral code already exists.');
        }

        return DB::transaction(function () use ($partner, $trimmedCode, $subPartner, $actor) {
            $referralCode = $this->insertReferralCode([
                'code' => $trimmedCode,
                'partner_id' => $partner->id,
                'sub_partner_id' => $subPartner?->id,
                'is_active' => true,
            ]);

            $this->auditLogger->log(
                event: 'referral_code.created',
                user: $actor,
                auditable: $referralCode,
                newValues: [
                    'code' => $trimmedCode,
                    'partner_id' => $partner->id,
                    'sub_partner_id' => $subPartner?->id,
                    'is_active' => true,
                ]
            );

            return $referralCode;
        });
    }

    /**
     * Check if the referral code already exists.
     */
    protected function codeExists(string $code): bool
    {
        return ReferralCode::where('code', $code)->exists();
    }

    /**
     * Insert the referral code record.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function insertReferralCode(array $attributes): ReferralCode
    {
        return ReferralCode::create($attributes);
    }
}
