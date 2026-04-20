<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatWhatsappChannel extends Model
{
    use HasFactory;

    protected $table = 'chat_whatsapp_channels';

    protected $fillable = [
        'instance_name',
        'display_name',
        'api_key',
        'server_url',
        'color',
        'is_active',
        'outbound_enabled',
        'metadata',
    ];

    protected $casts = [
        'api_key' => 'encrypted',
        'is_active' => 'boolean',
        'outbound_enabled' => 'boolean',
        'metadata' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailableForOutbound($query)
    {
        return $query->where('is_active', true)
            ->where('outbound_enabled', true);
    }
}
