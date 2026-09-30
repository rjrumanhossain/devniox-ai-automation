<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    protected $fillable = [
        'owner_id',
        'plan_id',
        'name',
        'slug',
        'tenant_domain',
        'status',
        'timezone',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(BusinessApiKey::class);
    }

    public function channels(): HasMany
    {
        return $this->hasMany(ChannelConnection::class);
    }

    public function aiProviders(): HasMany
    {
        return $this->hasMany(AiProviderCredential::class);
    }
}
