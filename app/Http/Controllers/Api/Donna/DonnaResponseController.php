<?php

namespace App\Http\Controllers\Api\Donna;

use App\Http\Controllers\Controller;
use App\Models\DonnaToolLog;
use Illuminate\Http\Request;

class DonnaResponseController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'          => 'required|integer',
            'service_id'         => 'required|integer',
            'channel_id'         => 'required|integer',
            'telegram_chat_id'   => 'required|string',
            'user_message'       => 'required|string',
            'assistant_response' => 'required|string',
            'message_type'       => 'nullable|string',
            'provider_message_id'=> 'nullable|string',
            'ai_metadata'        => 'nullable|array',
        ]);

        // Store as tool log until donna_conversations is built (Sprint 9)
        DonnaToolLog::create([
            'client_id'       => $data['client_id'],
            'subscription_id' => $data['service_id'],
            'channel_id'      => $data['channel_id'],
            'tool_name'       => 'conversation_turn',
            'request_json'    => [
                'telegram_chat_id' => $data['telegram_chat_id'],
                'user_message'     => $data['user_message'],
                'message_type'     => $data['message_type'] ?? 'text',
                'provider_msg_id'  => $data['provider_message_id'] ?? null,
            ],
            'response_json' => [
                'assistant_response' => $data['assistant_response'],
                'ai_metadata'        => $data['ai_metadata'] ?? [],
            ],
            'success' => true,
            'reason'  => 'conversation_stored',
        ]);

        return response()->json([
            'success'             => true,
            'stored'              => true,
            'conversation_id'     => null, // Will be real when Sprint 9 is built
            'inbound_message_id'  => null,
            'outbound_message_id' => null,
        ]);
    }
}
