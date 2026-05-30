<?php

namespace App\Http\Controllers\Api\Donna\Business;

use App\Http\Controllers\Controller;
use App\Models\DonnaChannel;
use App\Models\DonnaConversation;
use App\Models\DonnaMessage;
use App\Models\DonnaSubscription;
use App\Services\Donna\DonnaBusinessContextService;
use Illuminate\Http\Request;

class DonnaBusinessContextController extends Controller
{
    public function __construct(private DonnaBusinessContextService $contextService) {}

    /**
     * GET /api/donna/business/context
     * Parámetros: client_id + service_id + channel_id + conversation_id + (opcional) message_id
     * Fallback: instance_name + remote_jid
     */
    public function show(Request $request)
    {
        // Resolución primaria por IDs
        if ($request->filled('client_id') && $request->filled('channel_id') && $request->filled('conversation_id')) {
            $channel      = DonnaChannel::where('id', $request->integer('channel_id'))->firstOrFail();
            $sub          = DonnaSubscription::where('id', $request->integer('service_id'))->firstOrFail();
            $conversation = DonnaConversation::where('id', $request->integer('conversation_id'))->firstOrFail();
        } elseif ($request->filled('instance_name') && $request->filled('remote_jid')) {
            // Resolución fallback por instance_name + remote_jid
            $channel = DonnaChannel::where('instance_name', $request->input('instance_name'))
                ->where('channel_type', 'whatsapp')
                ->firstOrFail();
            $sub = DonnaSubscription::where('id', $channel->subscription_id)->firstOrFail();
            $conversation = DonnaConversation::where('channel_id', $channel->id)
                ->where('external_chat_id', $request->input('remote_jid'))
                ->firstOrFail();
        } else {
            return response()->json([
                'success' => false,
                'allowed' => false,
                'reason'  => 'missing_parameters',
                'message' => 'Se requieren (client_id + channel_id + conversation_id) o (instance_name + remote_jid).',
            ], 422);
        }

        $message = null;
        if ($request->filled('message_id')) {
            $message = DonnaMessage::find($request->integer('message_id'));
        }

        // Validar que suscripción siga activa
        if ($sub->status !== 'active' || !$sub->is_enabled) {
            return response()->json([
                'success' => false,
                'allowed' => false,
                'reason'  => 'service_inactive',
                'message' => 'La suscripción Donna Business no está activa.',
            ]);
        }

        $context = $this->contextService->build($channel, $sub, $conversation, $message);
        return response()->json($context);
    }
}
