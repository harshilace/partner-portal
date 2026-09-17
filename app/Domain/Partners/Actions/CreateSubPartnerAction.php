<?php

namespace App\Domain\Partners\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\User;
use App\Domain\Partners\Partner;
use App\Events\SubPartnerCreated;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreateSubPartnerAction
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    /**
     * Execute sub-partner creation.
     * Blocked when partner_code generation policy is unresolved (NEEDS BUSINESS CONFIRMATION #1).
     *
     * @param  array<string, mixed>  $data
     *
     * @throws RuntimeException
     */
    public function execute(array $data, Partner $parentPartner, ?User $actor = null): Partner
    {
        if (empty($data['partner_code'])) {
            throw new RuntimeException(
                'Sub-Partner creation is blocked: partner_code generation policy is not yet confirmed (NEEDS BUSINESS CONFIRMATION #1).'
            );
        }

        $subPartner = $this->runInTransaction(function () use ($data, $parentPartner, $actor) {
            $subPartner = $this->createPartner([
                'partner_code' => $data['partner_code'],
                'name' => $data['name'],
                'type' => 'sub',
                'parent_partner_id' => $parentPartner->id,
                'status' => $data['status'] ?? 'active',
            ]);

            $this->auditLogger->log(
                'sub_partner.created',
                $actor,
                $subPartner,
                null,
                $subPartner->toArray()
            );

            return $subPartner;
        });

        event(new SubPartnerCreated($subPartner, $actor));

        return $subPartner;
    }

    protected function createPartner(array $attributes): Partner
    {
        return Partner::create($attributes);
    }

    protected function runInTransaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
