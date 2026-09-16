<?php

namespace App\Domain\Partners;

use App\Domain\Authentication\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerUser extends Model
{
    use HasFactory;

    protected $table = 'partner_users';

    protected $fillable = [
        'partner_id',
        'user_id',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
