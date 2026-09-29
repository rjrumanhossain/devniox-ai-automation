<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $table = 'devniox_faqs';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'bool',
        'keywords' => 'array',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
