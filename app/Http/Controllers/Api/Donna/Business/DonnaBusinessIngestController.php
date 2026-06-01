<?php

namespace App\Http\Controllers\Api\Donna\Business;

use App\Http\Controllers\Controller;
use App\Services\Donna\DonnaBusinessIngestService;
use Illuminate\Http\Request;

class DonnaBusinessIngestController extends Controller
{
    public function __construct(private DonnaBusinessIngestService $ingest) {}

    public function store(Request $request)
    {
        if ($request->has('has_media')) {
            $request->merge([
                'has_media' => filter_var($request->input('has_media'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            ]);
        }

        $data = $request->validate([
            'provider'            => 'nullable|string',
            'instance_name'       => 'required|string',
            'remote_jid'          => 'nullable|string',
            'from_me'             => 'required|boolean',
            'message_type'        => 'required|string',   // text | audio | image | video | document
            'provider_message_id' => 'nullable|string',
            'sender_identifier'   => 'nullable|string',
            'sender_name'         => 'nullable|string',

            // Texto puro (mensajes de texto)
            'content_text'        => 'nullable|string',

            // Transcripción de audio (n8n ya transcribió antes de llamar a ingest)
            'transcription_text'  => 'nullable|string',

            // Caption de imagen/video + descripción OCR/visión
            'caption_text'        => 'nullable|string',
            'ocr_text'            => 'nullable|string',

            // Media
            'has_media'           => 'nullable|boolean',
            'media_url'           => 'nullable|string',
            'media_mime_type'     => 'nullable|string',
            'media_size'          => 'nullable|integer',

            'message_timestamp'   => 'nullable|integer',
            'raw_payload'         => 'nullable|array',
            'server_url'          => 'nullable|string',
            'instance_id'         => 'nullable|string',
            'event'               => 'nullable|string',
        ]);

        $result = $this->ingest->ingest($data);

        $status = ($result['allowed'] ?? false) ? 200 : 200; // siempre 200 para n8n
        return response()->json($result, $status);
    }
}
