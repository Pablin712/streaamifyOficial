<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class Mensaje extends Model
{
    use HasFactory;

    protected $table = 'mensajes';
    protected $primaryKey = 'idmsg';

    protected $fillable = [
        'idconv',
        'tipo_remitente',
        'idcli',
        'idemp',
        'contenido',
        'mensaje_original',
        'texto_extraido',
        'texto_agente',
        'tipo_contenido',
        'tipo',
        'archivo_url',
        'media_url',
        'mime_type',
        'media_kind',
        'media_file_name',
        'media_transcription',
        'media_caption',
        'media_analysis_json',
        'external_id',
        'reply_to_idmsg',
        'delivered_at',
        'read_at',
        'error_message',
        'leido',
        'leido_at',
        'respondido_por_ai',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'media_analysis_json' => 'array',
        'leido' => 'boolean',
        'respondido_por_ai' => 'boolean',
        'leido_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    /**
     * Relación con la conversación
     */
    public function conversacion()
    {
        return $this->belongsTo(Conversacion::class, 'idconv', 'idconv');
    }

    /**
     * Relación con cliente (si es remitente)
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'idcli', 'idcli');
    }

    /**
     * Relación con empleado (si es remitente)
     */
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'idemp', 'idemp');
    }

    /**
     * Mensaje al que responde este (hilo/cita estilo WhatsApp)
     */
    public function replyTo()
    {
        return $this->belongsTo(Mensaje::class, 'reply_to_idmsg', 'idmsg');
    }

    /**
     * Marcar como leído
     */
    public function marcarComoLeido()
    {
        if (!$this->leido) {
            $this->update([
                'leido' => true,
                'leido_at' => now(),
            ]);
        }
    }

    /**
     * Obtener nombre del remitente
     */
    public function getNombreRemitenteAttribute()
    {
        switch ($this->tipo_remitente) {
            case 'cliente':
                return $this->cliente?->nombrecli ?? 'Cliente';
            case 'empleado':
                return $this->empleado?->nombreemp ?? 'Soporte';
            case 'ia':
                return 'Asistente Virtual';
            case 'sistema':
                return 'Sistema';
            default:
                return 'Desconocido';
        }
    }

    /**
     * URL reproducible para media, compatible con despliegues en subcarpeta (/public).
     */
    public function getMediaPlayableUrlAttribute(): ?string
    {
        $raw = $this->media_url ?: $this->archivo_url;

        if (! $raw) {
            return null;
        }

        if (Str::startsWith($raw, ['data:', 'blob:'])) {
            return $raw;
        }

        // URL firmada: el hosting bloquea el acceso directo a /storage, así que
        // servimos el archivo a través de una ruta de Laravel sin requerir sesión
        // (necesario para que Evolution API pueda descargarlo al reenviarlo por WhatsApp).
        return URL::temporarySignedRoute('chat.media', now()->addDays(7), ['mensaje' => $this->idmsg]);
    }
}
