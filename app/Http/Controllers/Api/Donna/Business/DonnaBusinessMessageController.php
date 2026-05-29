<?php

namespace App\Http\Controllers\Api\Donna\Business;

use App\Http\Controllers\Controller;
use App\Models\DonnaMessage;
use Illuminate\Http\Request;

class DonnaBusinessMessageController extends Controller
{
    /**
     * PATCH /api/donna/business/messages/{message_id}/extractions
     * Actualiza un mensaje con transcripción de audio, OCR de imagen u otro texto extraído.
     */
    public function updateExtractions(Request $request, int $messageId)
    {
        $data = $request->validate([
            'client_id'          => 'required|integer',
            'service_id'         => 'required|integer',
            'channel_id'         => 'required|integer',
            'conversation_id'    => 'required|integer',
            'transcription_text' => 'nullable|string',
            'ocr_text'           => 'nullable|string',
            'extracted_text'     => 'nullable|string',
            'media_metadata'     => 'nullable|array',
        ]);

        $message = DonnaMessage::where('id', $messageId)
            ->where('client_id', $data['client_id'])
            ->where('conversation_id', $data['conversation_id'])
            ->firstOrFail();

        $updates = ['processing_status' => 'processing'];

        if (!empty($data['transcription_text'])) {
            $updates['transcription_text'] = $data['transcription_text'];
        }
        if (!empty($data['ocr_text'])) {
            $updates['ocr_text'] = $data['ocr_text'];
        }
        if (!empty($data['media_metadata'])) {
            $updates['metadata_json'] = array_merge($message->metadata_json ?? [], $data['media_metadata']);
        }

        $message->update($updates);

        $contentForAgent = $message->fresh()->effective_text;

        return response()->json([
            'success'           => true,
            'message_id'        => $message->id,
            'content_for_agent' => $contentForAgent,
        ]);
    }
}
