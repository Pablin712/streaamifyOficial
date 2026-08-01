<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Resultado del analisis de calidad de atencion (IA) de una conversacion de WhatsApp cerrada.
 * Fase 1 del plan en docs/optimizacion/idea-soporte.md: solo guarda el analisis, todavia no
 * genera puntos ni Tareas.
 */
class WhatsappAnalisisConversacion extends Model
{
    protected $table = 'whatsapp_analisis_conversacion';

    protected $fillable = [
        'idconv',
        'idcli',
        'empleados_involucrados',
        'empleado_principal_idemp',
        'servicio_idser',
        'motivo_contacto',
        'mensajes_cliente_count',
        'respondido',
        'tiempo_respuesta_promedio_segundos',
        'satisfaccion_score',
        'satisfaccion_justificacion',
        'motivo_perdida',
        'cruce_empleados',
        'cruce_detalle',
        'fecha_conversacion',
        'modelo_ia',
        'raw_response',
        'analizado_en',
    ];

    protected $casts = [
        'empleados_involucrados' => 'array',
        'raw_response' => 'array',
        'respondido' => 'boolean',
        'fecha_conversacion' => 'date',
        'analizado_en' => 'datetime',
    ];

    public function conversacion()
    {
        return $this->belongsTo(Conversacion::class, 'idconv', 'idconv');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'idcli', 'idcli');
    }

    public function empleadoPrincipal()
    {
        return $this->belongsTo(Empleado::class, 'empleado_principal_idemp', 'idemp');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_idser', 'idser');
    }
}
