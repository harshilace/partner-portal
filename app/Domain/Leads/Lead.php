<?php

namespace App\Domain\Leads;

use App\Domain\Customers\Customer;
use App\Domain\Partners\Partner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    use HasFactory;

    protected $table = 'leads';

    protected $fillable = [
        'name',
        'email',
        'email_normalized',
        'mobile',
        'mobile_normalized',
        'partner_id',
        'sub_partner_id',
        'customer_id',
        'status',
        'notes',
        'converted_at',
    ];

    protected function casts(): array
    {
        return [
            'converted_at' => 'datetime',
        ];
    }

    public function isConverted(): bool
    {
        return $this->status === 'converted';
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function subPartner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'sub_partner_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(LeadStatusHistory::class);
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(LeadFollowUp::class);
    }
}
