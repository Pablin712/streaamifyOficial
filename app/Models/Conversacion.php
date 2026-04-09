<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Conversacion extends Model
{
    use HasFactory;

    protected $table = 'conversaciones';
    protected $primaryKey = 'idconv';

    protected $fillable = [
        'idcli',
        'canal_principal',
        'canal_contacto_id',
        'origen',
        'subagente_codigo',
        'estado',
        'ultimo_idemp',
        'ultima_actividad',
        'mensajes_no_leidos',
        'requiere_humano',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'ultima_actividad' => 'datetime',
        'requiere_humano' => 'boolean',
    ];

    /**
     * Relación con cliente
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'idcli', 'idcli');
    }

    public function contactoCanal()
    {
        return $this->belongsTo(ChatContactoCanal::class, 'canal_contacto_id');
    }

    /**
     * Relación con último empleado que atendió
     */
    public function ultimoEmpleado()
    {
        return $this->belongsTo(Empleado::class, 'ultimo_idemp', 'idemp');
    }

    /**
     * Mensajes de la conversación
     */
    public function mensajes()
    {
        return $this->hasMany(Mensaje::class, 'idconv', 'idconv')
                    ->orderBy('created_at', 'asc');
    }

    /**
     * Último mensaje de la conversación
     */
    public function ultimoMensaje()
    {
        return $this->hasOne(Mensaje::class, 'idconv', 'idconv')
                    ->latestOfMany('idmsg');
    }

    /**
     * Marcar como leída (para empleados)
     */
    public function marcarComoLeida()
    {
        $this->update(['mensajes_no_leidos' => 0]);
    }

    /**
     * Cambiar estado
     */
    public function cambiarEstado(string $nuevoEstado, ?int $empleadoId = null)
    {
        $this->update([
            'estado' => $nuevoEstado,
            'ultimo_idemp' => $empleadoId,
            'ultima_actividad' => now(),
        ]);
    }

    /**
     * Scope: Conversaciones abiertas
     */
    public function scopeAbiertas($query)
    {
        return $query->whereIn('estado', ['abierta', 'en_atencion', 'en_espera', 'bot_activo']);
    }

    /**
     * Scope: Conversaciones con mensajes no leídos
     */
    public function scopeConMensajesNoLeidos($query)
    {
        return $query->where('mensajes_no_leidos', '>', 0);
    }
}
