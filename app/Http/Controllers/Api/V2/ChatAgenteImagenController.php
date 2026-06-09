<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\ChatAgenteImagen;
use App\Models\ChatMensajeCanal;
use App\Models\ChatWhatsappChannel;
use App\Models\Conversacion;
use App\Models\Mensaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ChatAgenteImagenController extends Controller
{
    /**
     * GET /api/v2/chat/agente/imagenes
     * Devuelve lista de imágenes activas: id, nombre, descripcion, categoria, tags.
     * El agente usa este endpoint para saber qué imágenes puede enviar.
     */
    public function index(Request $request)
    {
        $query = ChatAgenteImagen::where('activo', true)
            ->orderBy('prioridad')
            ->orderBy('categoria');

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->input('categoria'));
        }

        $imagenes = $query->get(['id', 'nombre', 'descripcion', 'categoria', 'tags', 'prioridad']);

        return response()->json([
            'success' => true,
            'data'    => $imagenes,
            'total'   => $imagenes->count(),
        ]);
    }

    /**
     * POST /api/v2/chat/agente/imagenes/{id}/enviar
     * Envía una imagen al cliente via Evolution API y la registra en el historial.
     *
     * Body:
     *   - numero        : número WhatsApp del destinatario (ej: "593991234567@s.whatsapp.net")
     *   - instance_name : nombre de la instancia Evo para obtener credenciales del canal
     *   - idconv        : (opcional) ID de la conversación para guardar en historial
     *   - caption       : (opcional) texto para acompañar la imagen
     */
    public function enviar(Request $request, int $id)
    {
        $request->validate([
            'numero'        => 'required|string',
            'instance_name' => 'required|string',
            'idconv'        => 'nullable|integer|exists:conversaciones,idconv',
            'caption'       => 'nullable|string|max:1024',
        ]);

        $imagen = ChatAgenteImagen::where('id', $id)->where('activo', true)->first();
        if (!$imagen) {
            return response()->json(['success' => false, 'message' => 'Imagen no encontrada o inactiva.'], 404);
        }

        $channel = ChatWhatsappChannel::where('instance_name', $request->input('instance_name'))
            ->where('is_active', true)
            ->first();

        if (!$channel) {
            return response()->json(['success' => false, 'message' => 'Canal no encontrado o inactivo.'], 404);
        }

        $serverUrl    = rtrim($channel->server_url, '/');
        $apiKey       = $channel->api_key;
        $instanceName = $channel->instance_name;
        $numero       = $request->input('numero');
        $caption      = $request->input('caption', '');

        $mediaUrl = url(Storage::url('agente/' . $imagen->archivo));
        $mimeType = $imagen->mime_type ?? 'image/jpeg';
        $fileName = basename($imagen->archivo);

        $payload = [
            'number'    => $numero,
            'mediatype' => 'image',
            'mimetype'  => $mimeType,
            'caption'   => $caption,
            'media'     => $mediaUrl,
            'fileName'  => $fileName,
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'apikey'       => $apiKey,
            ])->post("{$serverUrl}/message/sendMedia/{$instanceName}", $payload);

            if (!$response->successful()) {
                Log::warning('chat_agente_imagen.evo_error', [
                    'imagen_id' => $id,
                    'status'    => $response->status(),
                    'body'      => $response->body(),
                ]);

                return response()->json([
                    'success'    => false,
                    'message'    => 'Error al enviar imagen via Evolution API.',
                    'evo_status' => $response->status(),
                    'evo_body'   => $response->json(),
                ], 502);
            }

            $evoData      = $response->json();
            $externalMsgId = data_get($evoData, 'key.id') ?? data_get($evoData, 'id');

            // Guardar en historial de conversación si se provee idconv
            $mensajeId = null;
            if ($request->filled('idconv')) {
                $mensajeId = $this->guardarEnHistorial(
                    $request->integer('idconv'),
                    $imagen,
                    $mediaUrl,
                    $mimeType,
                    $caption,
                    $instanceName,
                    $externalMsgId
                );
            }

            Log::info('chat_agente_imagen.enviada', [
                'imagen_id'     => $id,
                'instance_name' => $instanceName,
                'numero'        => $numero,
                'idconv'        => $request->input('idconv'),
                'idmsg'         => $mensajeId,
            ]);

            return response()->json([
                'success'              => true,
                'message'              => 'Imagen enviada y registrada en historial.',
                'imagen_id'            => $imagen->id,
                'imagen_nombre'        => $imagen->nombre,
                'imagen_descripcion'   => $imagen->descripcion,
                'media_url'            => $mediaUrl,
                'idmsg'                => $mensajeId,
                'evo_response'         => $evoData,
            ]);

        } catch (\Throwable $e) {
            Log::error('chat_agente_imagen.exception', [
                'imagen_id' => $id,
                'error'     => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno al enviar imagen.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crea Mensaje + ChatMensajeCanal en la conversación para que:
     * - El empleado vea la imagen en el panel de chat.
     * - El agente lea en el próximo turno qué imágenes ya envió.
     */
    private function guardarEnHistorial(
        int $idconv,
        ChatAgenteImagen $imagen,
        string $mediaUrl,
        string $mimeType,
        string $caption,
        string $instanceName,
        ?string $externalMsgId
    ): ?int {
        return DB::transaction(function () use ($idconv, $imagen, $mediaUrl, $mimeType, $caption, $instanceName, $externalMsgId) {
            $conversacion = Conversacion::where('idconv', $idconv)->first();
            if (!$conversacion) {
                return null;
            }

            // contenido = descripción para que el agente la lea en historial_reciente
            $contenido = "[Imagen enviada] {$imagen->nombre}: {$imagen->descripcion}";
            if ($caption) {
                $contenido .= "\nCaption: {$caption}";
            }

            $mensaje = Mensaje::create([
                'idconv'          => $idconv,
                'tipo_remitente'  => 'ia',
                'tipo_contenido'  => 'imagen',
                'contenido'       => $contenido,
                'media_url'       => $mediaUrl,
                'archivo_url'     => $mediaUrl,
                'mime_type'       => $mimeType,
                'media_caption'   => $caption ?: null,
                'leido'           => true,
                'respondido_por_ai' => true,
                'metadata'        => [
                    'imagen_agente_id'          => $imagen->id,
                    'imagen_agente_nombre'      => $imagen->nombre,
                    'imagen_agente_descripcion' => $imagen->descripcion,
                    'instance'                  => $instanceName,
                    'source'                    => 'enviar_imagen_agente',
                ],
            ]);

            $contacto = $conversacion->contactoCanal;
            if ($contacto && $conversacion->canal_principal) {
                ChatMensajeCanal::create([
                    'idmsg'              => $mensaje->idmsg,
                    'idconv'             => $idconv,
                    'contacto_canal_id'  => $contacto->id,
                    'canal'              => $conversacion->canal_principal,
                    'direccion'          => 'outbound',
                    'external_message_id'=> $externalMsgId,
                    'external_status'    => $externalMsgId ? 'sent' : 'accepted',
                    'media_url'          => $mediaUrl,
                    'media_mime_type'    => $mimeType,
                    'payload'            => [
                        'imagen_agente_id'   => $imagen->id,
                        'imagen_agente_nombre' => $imagen->nombre,
                        'source'             => 'enviar_imagen_agente',
                    ],
                ]);
            }

            // Actualizar marca de última actividad de la conversación
            $conversacion->touch();

            return $mensaje->idmsg;
        });
    }
}
