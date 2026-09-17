<?php

namespace App\Domain\Renewals;

use App\Domain\Customers\Customer;
use App\Domain\Partners\Partner;
use App\Domain\Subscriptions\Subscription;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Renewal extends Model
{
    use HasFactory;

    protected $table = 'renewals';

    protected $fillable = [
        'subscription_id',
        'customer_id',
        'partner_id',
        'sub_partner_id',
        'due_date',
        'status',
        'reminder_30d_sent_at',
        'reminder_15d_sent_at',
        'reminder_7d_sent_at',
        'reminder_1d_sent_at',
        'renewed_subscription_id',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'reminder_30d_sent_at' => 'datetime',
            'reminder_15d_sent_at' => 'datetime',
            'reminder_7d_sent_at' => 'datetime',
            'reminder_1d_sent_at' => 'datetime',
        ];
    }

    public static function validMilestones(): array
    {
        return ['30d', '15d', '7d', '1d'];
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRenewed(): bool
    {
        return $this->status === 'renewed';
    }

    public function hasReminderSent(string $milestone): bool
    {
        $column = "reminder_{$milestone}_sent_at";

        return ! empty($this->{$column});
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
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

    public function renewedSubscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'renewed_subscription_id');
    }
}
