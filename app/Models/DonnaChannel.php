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
        'donna_mode',
        'test_numbers_json',
        'excluded_numbers_json',
        'exclude_groups_in_production',
    ];

    protected $casts = [
        'last_connected_at'            => 'datetime',
        'activated_at'                 => 'datetime',
        'code_expires_at'              => 'datetime',
        'is_default'                   => 'boolean',
        'metadata_json'                => 'array',
        'test_numbers_json'            => 'array',
        'excluded_numbers_json'        => 'array',
        'exclude_groups_in_production' => 'boolean',
    ];

    /**
     * Deja solo dígitos (quita +, espacios, guiones, paréntesis, sufijos de
     * WhatsApp como @s.whatsapp.net/@g.us, etc.) para poder comparar
     * números/IDs de grupo escritos de cualquier forma contra el
     * sender_identifier que llega de n8n, que siempre es solo dígitos.
     */
    public static function normalizeIdentifier(?string $raw): string
    {
        return preg_replace('/\D/', '', (string) $raw) ?? '';
    }

    public function isTestModeNumberAllowed(?string $senderIdentifier): bool
    {
        $sender = self::normalizeIdentifier($senderIdentifier);
        if ($sender === '') {
            return false;
        }

        $allowed = collect($this->test_numbers_json ?? [])
            ->map(fn ($n) => self::normalizeIdentifier($n));

        return $allowed->contains($sender);
    }

    public function isExcludedInProduction(?string $senderIdentifier, ?string $remoteJid = null): bool
    {
        if ($this->exclude_groups_in_production && $remoteJid && str_ends_with($remoteJid, '@g.us')) {
            return true;
        }

        $sender = self::normalizeIdentifier($senderIdentifier);
        $chat   = self::normalizeIdentifier($remoteJid ? preg_replace('/@.+$/', '', $remoteJid) : null);

        $excluded = collect($this->excluded_numbers_json ?? [])
            ->map(fn ($n) => self::normalizeIdentifier($n));

        return ($sender !== '' && $excluded->contains($sender))
            || ($chat !== '' && $excluded->contains($chat));
    }

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
