<?php

namespace App\Http\Controllers\Api\Donna;

use App\Http\Controllers\Controller;
use App\Models\DonnaChannel;
use App\Models\DonnaSubscription;
use Illuminate\Http\Request;

class DonnaTelegramController extends Controller
{
    public function register(Request $request)
    {
        $code           = strtoupper(trim($request->input('code', '')));
        $telegramChatId = (string) $request->input('telegram_chat_id', '');
        $username       = $request->input('telegram_username');
        $name           = $request->input('telegram_name');

        if (!$code || !$telegramChatId) {
            return response()->json([
                'success'    => false,
                'registered' => false,
                'allowed'    => false,
                'reason'     => 'missing_fields',
                'message'    => 'Faltan campos: code y telegram_chat_id.',
            ], 422);
        }

        $channel = DonnaChannel::where('activation_code', $code)
            ->where('channel_type', 'telegram')
            ->where('service_type', 'personal')
            ->where('status', 'pending')
            ->first();

        if (!$channel) {
            return response()->json([
                'success'    => false,
                'registered' => false,
                'allowed'    => false,
                'reason'     => 'invalid_activation_code',
                'message'    => 'El código no es válido o ya venció. Genera uno nuevo desde Streamify.',
            ]);
        }

        $sub = DonnaSubscription::where('id', $channel->subscription_id)
            ->where('status', 'active')
            ->first();

        if (!$sub) {
            return response()->json([
                'success'    => false,
                'registered' => false,
                'allowed'    => false,
                'reason'     => 'service_inactive',
                'message'    => 'Tu plan Donna Personal no está activo.',
                'plan'       => [
                    'name'        => 'Donna Personal',
                    'payment_url' => config('services.donna.personal_payment_url'),
                ],
            ]);
        }

        $channel->update([
            'owner_identifier' => $telegramChatId,
            'telegram_username'=> $username,
            'telegram_name'    => $name,
            'status'           => 'active',
            'activation_code'  => null,
            'activated_at'     => now(),
            'last_connected_at'=> now(),
        ]);

        return response()->json([
            'success'    => true,
            'registered' => true,
            'allowed'    => true,
            'reason'     => 'telegram_registered',
            'message'    => '¡Listo! Donna Personal fue vinculada correctamente.',
            'client'     => [
                'id'   => $channel->client_id,
                'name' => $channel->cliente?->nombrecli ?? 'Cliente',
            ],
            'service' => [
                'id'         => $sub->id,
                'type'       => 'personal',
                'code'       => 'donna_personal',
                'status'     => $sub->status,
                'expires_at' => $sub->expires_at?->toDateString(),
            ],
            'channel' => [
                'id'               => $channel->id,
                'type'             => 'telegram',
                'telegram_chat_id' => $telegramChatId,
                'status'           => 'active',
            ],
        ]);
    }
}
