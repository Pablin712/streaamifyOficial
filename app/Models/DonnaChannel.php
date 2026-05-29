<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class DonnaChannel extends Model
{
    protected $table = 'donna_channels';

    protected $fillable = [
        'client_id',
        'subscription_id',
        'service_type',
        'channel_type',
        'provider',
        'instance_name',
        'phone_number',
        'owner_identifier',
        'telegram_username',
        'telegram_name',
        'activated_at',
        'api_key_encrypted',
        'api_base_url',
        'webhook_url',
        'status',
        'activation_code',
        'code_expires_at',
        'is_default',
        'last_connected_at',
        'last_error',
        'metadata_json',
    ];

    protected $casts = [
        'last_connected_at' => 'datetime',
        'activated_at'      => 'datetime',
        'code_expires_at'   => 'datetime',
        'is_default'        => 'boolean',
        'metadata_json'     => 'array',
    ];

    public function isCodeExpired(): bool
    {
        return $this->code_expires_at && $this->code_expires_at->isPast();
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'client_id', 'idcli');
    }

    public function subscription()
    {
        return $this->belongsTo(DonnaSubscription::class, 'subscription_id');
    }

    public function getApiKey(): ?string
    {
        return $this->api_key_encrypted
            ? Crypt::decryptString($this->api_key_encrypted)
            : null;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getApiKeyMaskedAttribute(): string
    {
        if (!$this->api_key_encrypted) return '—';
        $key = $this->getApiKey();
        return '****' . substr($key, -4);
    }
}
