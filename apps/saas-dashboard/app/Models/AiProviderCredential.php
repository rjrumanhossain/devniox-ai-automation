<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiProviderCredential extends Model
{
    protected $fillable = [
        'business_id',
        'provider',
        'mode',
        'credential_payload',
        'monthly_token_limit',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'credential_payload' => 'encrypted:array',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
