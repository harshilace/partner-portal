<?php

namespace App\Domain\Subscriptions;

use App\Domain\Authentication\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoDebitEvent extends Model
{
    use HasFactory;

    protected $table = 'auto_debit_events';

    protected $fillable = [
        'auto_debit_mandate_id',
        'subscription_id',
        'event_type',
        'performed_by_user_id',
        'details',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    public function mandate(): BelongsTo
    {
        return $this->belongsTo(AutoDebitMandate::class, 'auto_debit_mandate_id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function performedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }
}
