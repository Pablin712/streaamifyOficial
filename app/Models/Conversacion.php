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
        'assigned_to',
        'operator_typing_id',
        'operator_typing_at',
        'ultimo_idemp',
        'ultima_actividad',
        'last_message_at',
        'mensajes_no_leidos',
        'unread_count',
        'prioridad',
        'requiere_humano',
        'closed_at',
        'pinned_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'ultima_actividad' => 'datetime',
        'last_message_at' => 'datetime',
        'operator_typing_at' => 'datetime',
        'closed_at' => 'datetime',
        'pinned_at' => 'datetime',
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

    public function operadorAsignado()
    {
        return $this->belongsTo(Empleado::class, 'assigned_to', 'idemp');
    }

    public function operadorEscribiendo()
    {
        return $this->belongsTo(Empleado::class, 'operator_typing_id', 'idemp');
    }

    /**
     * Etiquetas manuales asignadas a esta conversación (estilo WhatsApp Business)
     */
    public function etiquetas()
    {
        return $this->belongsToMany(
            ChatEtiqueta::class,
            'chat_conversacion_etiqueta',
            'conversacion_id',
            'etiqueta_id',
            'idconv',
            'id'
        );
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
        $this->update([
            'mensajes_no_leidos' => 0,
            'unread_count' => 0,
        ]);
    }

    /**
     * Cambiar estado
     */
    public function cambiarEstado(string $nuevoEstado, ?int $empleadoId = null)
    {
        $data = [
            'estado' => $nuevoEstado,
            'ultimo_idemp' => $empleadoId,
            'ultima_actividad' => now(),
        ];

        if (in_array($nuevoEstado, ['cerrado', 'cerrada'], true)) {
            $data['closed_at'] = now();
        }

        if (in_array($nuevoEstado, ['nuevo', 'nueva', 'abierto', 'abierta', 'asignado', 'atendiendo', 'pausado'], true)) {
            $data['closed_at'] = null;
        }

        $this->update($data);
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
