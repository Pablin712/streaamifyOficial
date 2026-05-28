<?php

namespace App\Services\Donna;

use App\Models\DonnaChannel;
use App\Models\DonnaIntegration;
use App\Models\DonnaSubscription;

class DonnaServiceValidator
{
    public function resolveByTelegram(string $telegramChatId): array
    {
        $channel = DonnaChannel::where('owner_identifier', $telegramChatId)
            ->where('channel_type', 'telegram')
            ->where('status', 'active')
            ->first();

        if (!$channel) {
            return $this->denied('telegram_not_registered',
                'Este chat aún no está vinculado. Ingresa el código de activación que aparece en Streamify.');
        }

        return $this->validateSubscription($channel);
    }

    public function validateForTool(int $clientId, int $subscriptionId, int $channelId): array
    {
        $channel = DonnaChannel::where('id', $channelId)
            ->where('client_id', $clientId)
            ->where('status', 'active')
            ->first();

        if (!$channel) {
            return $this->denied('channel_inactive', 'El canal Donna no está activo.');
        }

        return $this->validateSubscription($channel, $subscriptionId);
    }

    private function validateSubscription(DonnaChannel $channel, ?int $subscriptionId = null): array
    {
        $query = DonnaSubscription::where('client_id', $channel->client_id)
            ->where('status', 'active')
            ->where('is_enabled', true);

        if ($subscriptionId) {
            $query->where('id', $subscriptionId);
        } else {
            $query->where('id', $channel->subscription_id);
        }

        $sub = $query->first();

        if (!$sub) {
            return $this->denied('service_inactive', 'Tu plan Donna Personal no está activo.', [
                'name' => 'Donna Personal',
                'payment_url' => config('services.donna.personal_payment_url'),
            ]);
        }

        if ($sub->expires_at && $sub->expires_at->isPast()) {
            return $this->denied('service_expired',
                'Tu plan Donna Personal está vencido. Reactívalo para seguir usando Donna.', [
                    'name'        => 'Donna Personal',
                    'payment_url' => config('services.donna.personal_payment_url'),
                ]);
        }

        return [
            'allowed'  => true,
            'channel'  => $channel,
            'sub'      => $sub,
            'clientId' => $channel->client_id,
        ];
    }

    private function denied(string $reason, string $message, ?array $plan = null): array
    {
        $result = ['allowed' => false, 'reason' => $reason, 'message' => $message];
        if ($plan) $result['plan'] = $plan;
        return $result;
    }
}
