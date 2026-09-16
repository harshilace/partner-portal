<?php

namespace App\Domain\Subscriptions;

use App\Domain\Customers\Customer;
use App\Domain\Partners\Partner;
use App\Domain\Payments\Order;
use App\Domain\Products\Product;
use App\Domain\Products\ProductPlan;
use App\Domain\Renewals\Renewal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;

    protected $table = 'subscriptions';

    protected $fillable = [
        'subscription_number',
        'customer_id',
        'order_id',
        'product_id',
        'product_plan_id',
        'partner_id',
        'sub_partner_id',
        'status',
        'auto_debit_enabled',
        'starts_at',
        'expires_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'auto_debit_enabled' => 'boolean',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productPlan(): BelongsTo
    {
        return $this->belongsTo(ProductPlan::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function subPartner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'sub_partner_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(SubscriptionStatusHistory::class);
    }

    public function autoDebitMandates(): HasMany
    {
        return $this->hasMany(AutoDebitMandate::class);
    }

    public function renewals(): HasMany
    {
        return $this->hasMany(Renewal::class);
    }
}
