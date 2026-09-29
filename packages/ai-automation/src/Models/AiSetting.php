<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Models;

use Illuminate\Database\Eloquent\Model;

class AiSetting extends Model
{
    protected $table = 'devniox_ai_settings';

    protected $guarded = [];

    protected $casts = [
        'value' => 'array',
    ];
}
