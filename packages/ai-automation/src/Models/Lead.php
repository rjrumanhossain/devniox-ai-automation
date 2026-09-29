<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $table = 'devniox_leads';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
    ];
}
