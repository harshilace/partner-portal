<?php

namespace App\Domain\Referrals;

use App\Domain\Customers\CustomerPartnerAttribution;
use App\Domain\Partners\Partner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReferralCode extends Model
{
    use HasFactory;

    protected $table = 'referral_codes';

    protected $fillable = [
        'code',
        'partner_id',
        'sub_partner_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }

    public function subPartner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'sub_partner_id');
    }

    public function attributions(): HasMany
    {
        return $this->hasMany(CustomerPartnerAttribution::class);
    }
}
