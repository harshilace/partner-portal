<?php

namespace App\Domain\Leads;

use App\Domain\Authentication\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'lead_status_histories';

    protected $fillable = [
        'lead_id',
        'from_status',
        'to_status',
        'changed_by_user_id',
        'remarks',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
