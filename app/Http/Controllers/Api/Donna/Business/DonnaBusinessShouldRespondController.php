<?php

namespace App\Http\Controllers\Api\Donna\Business;

use App\Http\Controllers\Controller;
use App\Models\DonnaMessage;
use Illuminate\Http\Request;

class DonnaBusinessShouldRespondController extends Controller
{
    /**
     * Verifica si el message_id dado es todavía el último mensaje inbound
     * de la conversación. Si llegó un mensaje más nuevo durante el Wait,
     * este trigger ya no debe responder (la ejecución del mensaje nuevo lo hará).
     */
    public function check(Request $request)
    {
        $data = $request->validate([
            'conversation_id' => 'required|integer',
            'message_id'      => 'required|integer',
        ]);

        $hasNewer = DonnaMessage::where('conversation_id', $data['conversation_id'])
            ->where('id', '>', $data['message_id'])
            ->where('direction', 'inbound')
            ->exists();

        return response()->json([
            'debe_responder' => !$hasNewer,
            'message_id'     => $data['message_id'],
            'conversation_id'=> $data['conversation_id'],
        ]);
    }
}
