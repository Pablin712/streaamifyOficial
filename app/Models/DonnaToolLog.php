<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonnaToolLog extends Model
{
    protected $table = 'donna_tool_logs';

    protected $fillable = [
        'client_id', 'subscription_id', 'channel_id',
        'conversation_id', 'message_id',
        'tool_name', 'request_json', 'response_json',
        'success', 'reason', 'duration_ms',
    ];

    protected $casts = [
        'request_json'  => 'array',
        'response_json' => 'array',
        'success'       => 'boolean',
    ];
}
