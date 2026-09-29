<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookEvent extends Model
{
    protected $table = 'devniox_webhook_events';

    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
    ];
}
