<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        'tipo_contenido',
        'tipo',
        'archivo_url',
        'media_url',
        'mime_type',
        'external_id',
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
}
