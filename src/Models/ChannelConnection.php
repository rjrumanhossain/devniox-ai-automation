<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelConnection extends Model
{
    protected $table = 'devniox_channel_connections';

    protected $guarded = [];

    protected $casts = [
        'enabled' => 'bool',
    ];
}
