<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageLedger extends Model
{
    protected $fillable = [
        'business_id',
        'channel_connection_id',
        'provider',
        'message_count',
        'input_tokens',
        'output_tokens',
        'cost',
        'period_started_at',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'decimal:4',
            'period_started_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
