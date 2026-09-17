<?php

namespace App\Domain\Partners\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\User;
use App\Domain\Partners\Partner;
use Illuminate\Support\Facades\DB;

class UpdatePartnerAction
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    /**
     * Execute partner update. Only name is updated pending partner_code mutability confirmation.
     *
     * @param  array{name: string}  $data
     */
    public function execute(Partner $partner, array $data, ?User $actor = null): Partner
    {
        $oldValues = [
            'name' => $partner->name,
        ];

        $newValues = [
            'name' => $data['name'],
        ];

        DB::transaction(function () use ($partner, $newValues, $oldValues, $actor) {
            $partner->update([
                'name' => $newValues['name'],
            ]);

            $this->auditLogger->log(
                event: 'partner.updated',
                user: $actor,
                auditable: $partner,
                oldValues: $oldValues,
                newValues: $newValues
            );
        });

        return $partner;
    }
}
