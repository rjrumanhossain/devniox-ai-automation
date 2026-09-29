<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChannelConnection extends Model
{
    protected $fillable = [
        'business_id',
        'type',
        'external_id',
        'display_name',
        'credential_payload',
        'webhook_secret',
        'status',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'credential_payload' => 'encrypted:array',
            'last_seen_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
