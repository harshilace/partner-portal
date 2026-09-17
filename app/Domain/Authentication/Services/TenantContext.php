<?php

namespace App\Domain\Authentication\Services;

use App\Domain\Authentication\User;
use App\Domain\Partners\Partner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TenantContext
{
    public function __construct(
        protected ?User $user = null
    ) {
        $this->user = $user ?? Auth::user();
    }

    public function user(): ?User
    {
        return $this->user ?? Auth::user();
    }

    public function partner(): ?Partner
    {
        return $this->user()?->partner();
    }

    public function isAdmin(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function isMainPartner(): bool
    {
        return $this->user()?->isMainPartner() ?? false;
    }

    public function isSubPartner(): bool
    {
        return $this->user()?->isSubPartner() ?? false;
    }

    /**
     * Determine whether the authenticated context has authorization to access a given partner ID.
     */
    public function canAccessPartner(int|string|Partner $partner): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $targetPartnerId = $partner instanceof Partner ? $partner->id : (int) $partner;
        $currentPartner = $this->partner();

        if (! $currentPartner) {
            return false;
        }

        // Exact match for own partner
        if ($currentPartner->id === $targetPartnerId) {
            return true;
        }

        // Main Partner can access their own direct sub-partners
        if ($this->isMainPartner()) {
            if ($partner instanceof Partner && (int) $partner->parent_partner_id === (int) $currentPartner->id) {
                return true;
            }

            if ($currentPartner->relationLoaded('subPartners')) {
                return $currentPartner->subPartners->contains('id', $targetPartnerId);
            }

            if (! $currentPartner->exists) {
                return false;
            }

            return $currentPartner->subPartners()->where('id', $targetPartnerId)->exists();
        }

        // Sub-Partner cannot access any other partner
        return false;
    }

    /**
     * Determine whether the authenticated context can access a given scoped record.
     * Prevents IDOR by verifying ownership against the authenticated tenant context.
     */
    public function canAccessRecord(Model $record): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $currentPartner = $this->partner();
        if (! $currentPartner) {
            return false;
        }

        if ($this->isSubPartner()) {
            // Sub-partner check: record must belong directly to this sub-partner
            $subPartnerId = $record->sub_partner_id
                ?? $record->current_sub_partner_id
                ?? ($record instanceof Partner ? $record->id : null);

            return (int) $subPartnerId === (int) $currentPartner->id;
        }

        if ($this->isMainPartner()) {
            // Main partner check: record must belong to this main partner or one of its sub-partners
            $partnerId = $record->partner_id
                ?? $record->current_partner_id
                ?? ($record instanceof Partner ? $record->id : null);

            if ((int) $partnerId === (int) $currentPartner->id) {
                return true;
            }

            // If the record has a sub_partner_id, check if it belongs to this main partner
            $subPartnerId = $record->sub_partner_id ?? $record->current_sub_partner_id ?? null;
            if ($subPartnerId) {
                return $this->canAccessPartner((int) $subPartnerId);
            }

            return false;
        }

        return false;
    }

    /**
     * Scope an Eloquent query to the authenticated tenant context.
     */
    public function scopeQuery(
        Builder $query,
        string $partnerColumn = 'partner_id',
        ?string $subPartnerColumn = 'sub_partner_id'
    ): Builder {
        if ($this->isAdmin()) {
            return $query;
        }

        $partner = $this->partner();
        if (! $partner) {
            return $query->whereRaw('1 = 0');
        }

        if ($this->isSubPartner()) {
            $column = $subPartnerColumn ?? $partnerColumn;

            return $query->where($column, $partner->id);
        }

        if ($this->isMainPartner()) {
            return $query->where($partnerColumn, $partner->id);
        }

        return $query->whereRaw('1 = 0');
    }
}
