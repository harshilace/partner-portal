<?php

namespace App\Domain\Customers;

use App\Domain\Authentication\User;
use App\Domain\Partners\Partner;
use App\Domain\Referrals\ReferralCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPartnerAttribution extends Model
{
    use HasFactory;

    protected $table = 'customer_partner_attributions';

    protected $fillable = [
        'customer_id',
        'partner_id',
        'sub_partner_id',
        'referral_code_id',
        'starts_at',
        'ends_at',
        'changed_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function isOpen(): bool
    {
        return $this->ends_at === null;
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('ends_at');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function subPartner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'sub_partner_id');
    }

    public function referralCode(): BelongsTo
    {
        return $this->belongsTo(ReferralCode::class);
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
