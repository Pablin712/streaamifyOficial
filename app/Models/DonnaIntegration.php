<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class DonnaIntegration extends Model
{
    protected $table = 'donna_integrations';

    protected $fillable = [
        'client_id',
        'subscription_id',
        'integration_type',
        'name',
        'access_token_encrypted',
        'refresh_token_encrypted',
        'token_expires_at',
        'scopes_json',
        'status',
        'last_sync_at',
        'last_error',
        'metadata_json',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'last_sync_at'     => 'datetime',
        'scopes_json'      => 'array',
        'metadata_json'    => 'array',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'client_id', 'idcli');
    }

    public function subscription()
    {
        return $this->belongsTo(DonnaSubscription::class, 'subscription_id');
    }

    public function getAccessToken(): ?string
    {
        return $this->access_token_encrypted
            ? Crypt::decryptString($this->access_token_encrypted)
            : null;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refresh_token_encrypted
            ? Crypt::decryptString($this->refresh_token_encrypted)
            : null;
    }

    public function isTokenExpired(): bool
    {
        return $this->token_expires_at && $this->token_expires_at->isPast();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function scopeGoogle($query)
    {
        return $query->where('integration_type', 'google');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public static function googleConnected(int $clientId): bool
    {
        return static::where('client_id', $clientId)
            ->where('integration_type', 'google')
            ->where('status', 'active')
            ->exists();
    }
}
