<?php

namespace App\Domain\Partners;

use App\Domain\Authentication\User;
use App\Domain\Customers\Customer;
use App\Domain\Customers\CustomerPartnerAttribution;
use App\Domain\Leads\Lead;
use App\Domain\Payments\Order;
use App\Domain\Referrals\ReferralCode;
use App\Domain\Subscriptions\Subscription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partner extends Model
{
    use HasFactory;

    protected $table = 'partners';

    protected $fillable = [
        'partner_code',
        'name',
        'type',
        'parent_partner_id',
        'status',
    ];

    public function isMain(): bool
    {
        return $this->type === 'main';
    }

    public function isSub(): bool
    {
        return $this->type === 'sub';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function parentPartner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'parent_partner_id');
    }

    public function subPartners(): HasMany
    {
        return $this->hasMany(Partner::class, 'parent_partner_id');
    }

    public function partnerUsers(): HasMany
    {
        return $this->hasMany(PartnerUser::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'partner_users');
    }

    public function referralCodes(): HasMany
    {
        return $this->hasMany(ReferralCode::class);
    }

    public function currentCustomers(): HasMany
    {
        return $this->hasMany(Customer::class, 'current_partner_id');
    }

    public function currentSubPartnerCustomers(): HasMany
    {
        return $this->hasMany(Customer::class, 'current_sub_partner_id');
    }

    public function attributions(): HasMany
    {
        return $this->hasMany(CustomerPartnerAttribution::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function subPartnerLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'sub_partner_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function subPartnerOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'sub_partner_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function subPartnerSubscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'sub_partner_id');
    }
}
