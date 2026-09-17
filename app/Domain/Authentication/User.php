<?php

namespace App\Domain\Authentication;

use App\Domain\Audit\AuditLog;
use App\Domain\Authentication\Enums\Role;
use App\Domain\Partners\Partner;
use App\Domain\Partners\PartnerUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function partnerUsers(): HasMany
    {
        return $this->hasMany(PartnerUser::class);
    }

    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class, 'partner_users');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function isAdmin(): bool
    {
        $roleValue = $this->role instanceof Role
            ? $this->role->value
            : $this->role;

        return $roleValue === Role::ADMIN->value;
    }

    public function isMainPartner(): bool
    {
        $roleValue = $this->role instanceof Role
            ? $this->role->value
            : $this->role;

        if ($roleValue === Role::MAIN_PARTNER->value) {
            return true;
        }

        if ($roleValue === Role::ADMIN->value ||
            $roleValue === Role::SUB_PARTNER->value) {
            return false;
        }

        return $this->partner()?->type === 'main';
    }

    public function isSubPartner(): bool
    {
        $roleValue = $this->role instanceof Role
            ? $this->role->value
            : $this->role;

        if ($roleValue === Role::SUB_PARTNER->value) {
            return true;
        }

        if ($roleValue === Role::ADMIN->value ||
            $roleValue === Role::MAIN_PARTNER->value) {
            return false;
        }

        return $this->partner()?->type === 'sub';
    }

    public function partner(): ?Partner
    {
        if ($this->relationLoaded('partners')) {
            return $this->partners->first();
        }

        if (! $this->exists) {
            return null;
        }

        return $this->partners()->first();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
