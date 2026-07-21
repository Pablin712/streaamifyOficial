<?php

namespace App\Services\Chat;

use App\Models\Chat\ChatSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class ChatSettingsService
{
    private const DEFAULTS = [
        'chat_allow_text' => true,
        'chat_allow_image' => true,
        'chat_allow_audio' => true,
        'chat_allow_document' => false,
        'chat_allow_location' => false,
        'chat_allow_template' => false,
        'chat_realtime_enabled' => true,
        'chat_auto_assign' => false,
        'chat_max_upload_mb' => 10,
        'chat_outbound_burst_limit' => 4,
        'chat_outbound_burst_window_seconds' => 12,
        'chat_outbound_rate_limit' => 20,
        'chat_outbound_rate_window_seconds' => 60,
        'chat_outbound_min_gap_seconds' => 2,
        'chat_outbound_max_gap_seconds' => 6,
        'chat_outbound_admin_max_wait_seconds' => 12,
        'chat_webhook_token' => '',
        'n8n_webhook_url' => '',
        'evoapi_base_url' => '',
        'evoapi_api_key' => '',
    ];

    public function all(): array
    {
        return Cache::remember('chat.settings', 60, function () {
            if (! Schema::hasTable('chat_settings')) {
                return self::DEFAULTS;
            }

            $settings = ChatSetting::query()->get()->keyBy('key');

            return collect(self::DEFAULTS)
                ->mapWithKeys(function ($default, string $key) use ($settings) {
                    $setting = $settings->get($key);

                    if (! $setting) {
                        return [$key => $default];
                    }

                    return [$key => $this->castValue($setting->value, $setting->type, $default)];
                })
                ->all();
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function isTypeAllowed(string $type): bool
    {
        return match ($type) {
            'texto' => (bool) $this->get('chat_allow_text', true),
            'imagen' => (bool) $this->get('chat_allow_image', true),
            'audio' => (bool) $this->get('chat_allow_audio', true),
            'documento', 'archivo' => (bool) $this->get('chat_allow_document', false),
            'ubicacion' => (bool) $this->get('chat_allow_location', false),
            'plantilla' => (bool) $this->get('chat_allow_template', false),
            'sistema' => true,
            default => false,
        };
    }

    public function maxUploadKilobytes(): int
    {
        return max(1, (int) $this->get('chat_max_upload_mb', 10)) * 1024;
    }

    private function castValue(?string $value, string $type, mixed $default): mixed
    {
        if ($value === null) {
            return $default;
        }

        return match ($type) {
            'bool' => in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true),
            'int' => (int) $value,
            default => $value,
        };
    }
}
