<?php

namespace App\Domain\Notifications;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'notifications';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function markAsRead(): void
    {
        $this->forceFill(['read_at' => $this->freshTimestamp()]);

        if ($this->exists) {
            $this->save();
        }
    }

    public function markAsUnread(): void
    {
        $this->forceFill(['read_at' => null]);

        if ($this->exists) {
            $this->save();
        }
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }
}
