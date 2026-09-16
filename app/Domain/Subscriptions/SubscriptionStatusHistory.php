<?php

namespace App\Domain\Subscriptions;

use App\Domain\Authentication\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'subscription_status_histories';

    protected $fillable = [
        'subscription_id',
        'from_status',
        'to_status',
        'changed_by_user_id',
        'reason',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
