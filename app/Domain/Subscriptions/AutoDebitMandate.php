<?php

namespace App\Domain\Subscriptions;

use App\Domain\Authentication\User;
use App\Domain\Customers\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutoDebitMandate extends Model
{
    use HasFactory;

    protected $table = 'auto_debit_mandates';

    protected $fillable = [
        'subscription_id',
        'customer_id',
        'mandate_reference',
        'status',
        'stopped_by_user_id',
        'stopped_at',
        'stop_reason',
    ];

    protected function casts(): array
    {
        return [
            'stopped_at' => 'datetime',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isStopped(): bool
    {
        return $this->status === 'stopped';
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function stoppedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'stopped_by_user_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(AutoDebitEvent::class);
    }
}
