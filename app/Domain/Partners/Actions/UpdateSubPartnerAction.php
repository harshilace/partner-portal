<?php

namespace App\Domain\Partners\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\User;
use App\Domain\Partners\Partner;
use Illuminate\Support\Facades\DB;

class UpdateSubPartnerAction
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    /**
     * Execute sub-partner update. Only name is updated pending partner_code mutability confirmation.
     *
     * @param  array{name: string}  $data
     */
    public function execute(Partner $subPartner, array $data, ?User $actor = null): Partner
    {
        $oldValues = [
            'name' => $subPartner->name,
        ];

        $newValues = [
            'name' => $data['name'],
        ];

        DB::transaction(function () use ($subPartner, $newValues, $oldValues, $actor) {
            $subPartner->update([
                'name' => $newValues['name'],
            ]);

            $this->auditLogger->log(
                event: 'partner.updated',
                user: $actor,
                auditable: $subPartner,
                oldValues: $oldValues,
                newValues: $newValues
            );
        });

        return $subPartner;
    }
}
