<?php

namespace App\Domain\Customers;

use App\Domain\Leads\Lead;
use App\Domain\Partners\Partner;
use App\Domain\Payments\Order;
use App\Domain\Payments\Payment;
use App\Domain\Subscriptions\AutoDebitMandate;
use App\Domain\Subscriptions\Subscription;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';

    protected $fillable = [
        'customer_code',
        'name',
        'email',
        'email_normalized',
        'mobile',
        'mobile_normalized',
        'current_partner_id',
        'current_sub_partner_id',
        'status',
    ];

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isAssigned(): bool
    {
        return $this->current_partner_id !== null;
    }

    public static function normalizeEmail(?string $email): ?string
    {
        return $email !== null ? strtolower(trim($email)) : null;
    }

    public static function normalizeMobile(?string $mobile): ?string
    {
        return $mobile !== null ? preg_replace('/\D/', '', $mobile) : null;
    }

    public static function findByIdentity(string $email, string $mobile): ?Customer
    {
        $en = static::normalizeEmail($email);
        $mn = static::normalizeMobile($mobile);

        if ($en === null || $mn === null) {
            return null;
        }

        return static::where('email_normalized', $en)
            ->where('mobile_normalized', $mn)
            ->first();
    }

    public function currentPartner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'current_partner_id');
    }

    public function currentSubPartner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'current_sub_partner_id');
    }

    public function attributions(): HasMany
    {
        return $this->hasMany(CustomerPartnerAttribution::class);
    }

    public function currentAttribution(): HasOne
    {
        return $this->hasOne(CustomerPartnerAttribution::class)->whereNull('ends_at');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function autoDebitMandates(): HasMany
    {
        return $this->hasMany(AutoDebitMandate::class);
    }
}
