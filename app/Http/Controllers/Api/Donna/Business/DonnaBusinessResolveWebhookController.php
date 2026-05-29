<?php

namespace App\Http\Controllers\Api\Donna\Business;

use App\Http\Controllers\Controller;
use App\Models\DonnaChannel;
use App\Models\DonnaSubscription;
use Illuminate\Http\Request;

class DonnaBusinessResolveWebhookController extends Controller
{
    /**
     * POST /api/donna/business/resolve-webhook
     *
     * Resuelve el canal y cliente SaaS a partir del instance_name del webhook de Evo API.
     * Devuelve apikey desencriptada solo si la suscripción Donna Business está activa.
     */
    public function resolve(Request $request)
    {
        $instanceName = $request->input('instance_name') ?: $request->input('instance');

        if (!$instanceName) {
            return response()->json([
                'success' => false,
                'message' => 'instance_name requerido.',
                'reason'  => 'missing_instance_name',
            ], 422);
        }

        $channel = DonnaChannel::where('instance_name', $instanceName)
            ->where('channel_type', 'whatsapp')
            ->where('status', 'active')
            ->first();

        if (!$channel) {
            return response()->json([
                'success' => false,
                'message' => "No se encontró un canal WhatsApp activo para la instancia '{$instanceName}'.",
                'reason'  => 'channel_not_found',
            ]);
        }

        $subscription = DonnaSubscription::find($channel->subscription_id);

        if (!$subscription || !$subscription->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'La suscripción Donna Business no está activa para este canal.',
                'reason'  => 'service_inactive',
                'service' => $subscription ? [
                    'id'     => $subscription->id,
                    'type'   => $subscription->service_type,
                    'status' => $subscription->status,
                ] : null,
            ]);
        }

        $cliente = $channel->cliente;

        // Extraer teléfono y nombre del cliente final desde el payload del webhook
        $remoteJid  = $request->input('remote_jid', '');
        $pushName   = $request->input('push_name', '');
        $phone      = explode('@', $remoteJid)[0] ?? $remoteJid;

        return response()->json([
            'success' => true,
            'client'  => [
                'id'   => $cliente?->idcli,
                'name' => $cliente?->nombrecli,
            ],
            'service' => [
                'id'     => $subscription->id,
                'type'   => $subscription->service_type ?? 'business',
                'status' => $subscription->status,
            ],
            'channel' => [
                'id'            => $channel->id,
                'instance_name' => $channel->instance_name,
                'server_url'    => $channel->api_base_url,
                'apikey'        => $channel->getApiKey(),
            ],
            'final_customer' => [
                'phone' => $phone,
                'name'  => $pushName,
            ],
        ]);
    }
}
