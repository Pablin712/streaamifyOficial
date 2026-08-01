<?php

namespace App\Services\WhatsApp;

use App\Models\Conversacion;
use App\Models\WhatsappAnalisisConversacion;
use App\Services\Anthropic\ClaudeClient;
use Illuminate\Support\Str;

/**
 * Fase 1 del plan en docs/optimizacion/idea-soporte.md: analiza una conversacion de
 * WhatsApp ya cerrada y guarda el resultado en whatsapp_analisis_conversacion.
 *
 * No genera puntos ni Tareas todavia (eso es Fase 2, una vez validada la calidad
 * de clasificacion contra el criterio real del negocio).
 *
 * Division deliberada de responsabilidades: los datos que se pueden calcular con
 * certeza (tiempos, conteos, quien participo) se calculan en PHP; solo se le pide
 * a la IA lo que requiere lectura semantica del contenido (satisfaccion, motivo de
 * perdida, si hubo cruce de empleados).
 */
class ConversacionSatisfaccionAnalyzer
{
    private const TOOL_NAME = 'registrar_analisis_conversacion';
    private const MAX_MENSAJES_EN_PROMPT = 200;
    private const MAX_CONTENIDO_CHARS = 500;

    public function __construct(private ClaudeClient $claude)
    {
    }

    /**
     * @return WhatsappAnalisisConversacion|null null si la conversacion no tiene
     *         mensajes de cliente (nada que evaluar).
     */
    public function analizar(Conversacion $conversacion): ?WhatsappAnalisisConversacion
    {
        $mensajes = $conversacion->mensajes()->get();

        $mensajesCliente = $mensajes->where('tipo_remitente', 'cliente');
        if ($mensajesCliente->isEmpty()) {
            return null;
        }

        $metricas = $this->calcularMetricasDeterministicas($mensajes);
        $juicioIa = $this->pedirJuicioIa($conversacion, $mensajes, $metricas);

        return WhatsappAnalisisConversacion::updateOrCreate(
            ['idconv' => $conversacion->idconv],
            [
                'idcli' => $conversacion->idcli,
                'empleados_involucrados' => $metricas['empleados_involucrados'],
                'empleado_principal_idemp' => $metricas['empleado_principal_idemp'],
                'mensajes_cliente_count' => $metricas['mensajes_cliente_count'],
                'respondido' => $metricas['respondido'],
                'tiempo_respuesta_promedio_segundos' => $metricas['tiempo_respuesta_promedio_segundos'],
                'satisfaccion_score' => $juicioIa['satisfaccion_score'],
                'satisfaccion_justificacion' => $juicioIa['satisfaccion_justificacion'],
                'motivo_perdida' => $juicioIa['motivo_perdida'],
                'cruce_empleados' => $juicioIa['cruce_empleados'],
                'cruce_detalle' => $juicioIa['cruce_detalle'],
                'fecha_conversacion' => ($conversacion->closed_at ?? $conversacion->ultima_actividad)?->toDateString(),
                'modelo_ia' => $juicioIa['modelo_ia'],
                'raw_response' => $juicioIa['raw_response'],
                'analizado_en' => now(),
            ]
        );
    }

    private function calcularMetricasDeterministicas($mensajes): array
    {
        $empleadosInvolucrados = $mensajes->where('tipo_remitente', 'empleado')
            ->pluck('idemp')
            ->filter()
            ->unique()
            ->values();

        $respondido = $mensajes->whereIn('tipo_remitente', ['empleado', 'ia'])->isNotEmpty();

        // Tiempo de respuesta: para cada mensaje de cliente, buscar el siguiente
        // mensaje de empleado/ia posterior y medir la diferencia. Mensajes de
        // cliente consecutivos comparten la misma respuesta (no se duplican).
        $tiempos = [];
        $ultimaRespuestaVista = null;
        foreach ($mensajes as $mensaje) {
            if ($mensaje->tipo_remitente === 'cliente') {
                $siguienteRespuesta = $mensajes->first(function ($m) use ($mensaje) {
                    return in_array($m->tipo_remitente, ['empleado', 'ia'], true)
                        && $m->created_at->greaterThan($mensaje->created_at);
                });

                if ($siguienteRespuesta && $siguienteRespuesta->created_at !== $ultimaRespuestaVista) {
                    $tiempos[] = $mensaje->created_at->diffInSeconds($siguienteRespuesta->created_at);
                    $ultimaRespuestaVista = $siguienteRespuesta->created_at;
                }
            }
        }

        return [
            'empleados_involucrados' => $empleadosInvolucrados->all(),
            'empleado_principal_idemp' => $empleadosInvolucrados->first(),
            'mensajes_cliente_count' => $mensajes->where('tipo_remitente', 'cliente')->count(),
            'respondido' => $respondido,
            'tiempo_respuesta_promedio_segundos' => count($tiempos) > 0
                ? (int) round(array_sum($tiempos) / count($tiempos))
                : null,
        ];
    }

    private function pedirJuicioIa(Conversacion $conversacion, $mensajes, array $metricas): array
    {
        $transcript = $this->construirTranscript($mensajes);
        $empleadosResumen = $this->resumenEmpleados($metricas['empleados_involucrados']);

        $system = <<<TXT
        Sos un analista de calidad de atencion al cliente para Streamify, un negocio que revende
        cuentas de streaming (Netflix, Disney+, etc.) y atiende clientes por WhatsApp. Vas a leer
        la transcripcion de UNA conversacion ya cerrada y evaluar la calidad de la atencion que
        recibio el cliente, sin importar si el cliente fue amable o grosero -- evalua el servicio
        que se le dio, no su actitud.

        Reglas de evaluacion:
        - satisfaccion_score (1 a 5): que tan bien fue atendido el cliente en base al contenido real
          de la conversacion (resolucion, claridad, tono profesional del empleado). 5 = resuelto y
          bien atendido. 1 = mal atendido o no resuelto. Si el cliente esta molesto pero el problema
          se resolvio correctamente y a tiempo, no bajes el score solo por el tono del cliente.
        - motivo_perdida: usa "no_aplica" salvo que la conversacion muestre evidencia clara de que el
          cliente se va o no continuara (cancelacion, queja de que no volvera, etc). Elegi la categoria
          que mejor describa el motivo; si no hay evidencia clara del motivo especifico usa "otro".
        - cruce_empleados: "ninguno" si un solo empleado atendio, o si varios empleados participaron
          sin problema (ej. turno relevo limpio). "tardado" si dos o mas empleados respondieron de forma
          redundante o contradictoria al mismo mensaje del cliente (se cruzaron). "dividido" si un
          empleado inicio la atencion y otro distinto la resolvio, de forma coordinada.
        - cruce_detalle: breve explicacion (1-2 lineas) SOLO si cruce_empleados no es "ninguno". Si es
          "ninguno" dejalo vacio.

        Metadatos ya calculados (no los recalcules, son exactos):
        - Empleados que participaron: {$empleadosResumen}
        - Mensajes del cliente en esta conversacion: {$metricas['mensajes_cliente_count']}
        - Fue respondida por al menos un empleado/IA: {$this->boolAString($metricas['respondido'])}

        Respondé unicamente llamando a la herramienta registrar_analisis_conversacion.
        TXT;

        $tool = [
            'name' => self::TOOL_NAME,
            'description' => 'Registra el analisis de calidad de atencion de una conversacion de WhatsApp.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'satisfaccion_score' => [
                        'type' => 'integer',
                        'enum' => [1, 2, 3, 4, 5],
                        'description' => 'Calificacion de 1 (mal atendido) a 5 (bien atendido y resuelto).',
                    ],
                    'satisfaccion_justificacion' => [
                        'type' => 'string',
                        'description' => 'Justificacion breve (1-2 lineas) del score.',
                    ],
                    'motivo_perdida' => [
                        'type' => 'string',
                        'enum' => ['no_aplica', 'mala_atencion', 'sin_respuesta', 'sin_presupuesto', 'no_continuara', 'otro_servicio', 'otro'],
                    ],
                    'cruce_empleados' => [
                        'type' => 'string',
                        'enum' => ['ninguno', 'tardado', 'dividido'],
                    ],
                    'cruce_detalle' => [
                        'type' => 'string',
                        'description' => 'Explicacion breve si hubo cruce; string vacio si no.',
                    ],
                ],
                'required' => ['satisfaccion_score', 'satisfaccion_justificacion', 'motivo_perdida', 'cruce_empleados'],
            ],
        ];

        $resultado = $this->claude->callTool($system, $transcript, $tool);
        $input = $resultado['input'];

        return [
            'satisfaccion_score' => (int) ($input['satisfaccion_score'] ?? 1),
            'satisfaccion_justificacion' => (string) ($input['satisfaccion_justificacion'] ?? ''),
            'motivo_perdida' => in_array($input['motivo_perdida'] ?? null, [
                'no_aplica', 'mala_atencion', 'sin_respuesta', 'sin_presupuesto', 'no_continuara', 'otro_servicio', 'otro',
            ], true) ? $input['motivo_perdida'] : 'no_aplica',
            'cruce_empleados' => in_array($input['cruce_empleados'] ?? null, ['ninguno', 'tardado', 'dividido'], true)
                ? $input['cruce_empleados']
                : 'ninguno',
            'cruce_detalle' => $input['cruce_detalle'] ?? null,
            'modelo_ia' => $resultado['raw']['model'] ?? config('services.anthropic.model'),
            'raw_response' => $resultado['raw'],
        ];
    }

    private function construirTranscript($mensajes): string
    {
        $lineas = [];
        foreach ($mensajes->take(self::MAX_MENSAJES_EN_PROMPT) as $mensaje) {
            $hora = $mensaje->created_at?->format('Y-m-d H:i') ?? '?';
            $quien = match ($mensaje->tipo_remitente) {
                'cliente' => 'Cliente',
                'empleado' => 'Empleado #' . ($mensaje->idemp ?? '?'),
                'ia' => 'IA/Bot',
                'sistema' => 'Sistema',
                default => 'Desconocido',
            };
            $contenido = Str::limit((string) $mensaje->contenido, self::MAX_CONTENIDO_CHARS);
            $lineas[] = "[{$hora}] {$quien}: {$contenido}";
        }

        return implode("\n", $lineas);
    }

    private function resumenEmpleados(array $idsEmpleados): string
    {
        if (empty($idsEmpleados)) {
            return 'ninguno (no hay mensajes de empleados)';
        }

        return implode(', ', array_map(fn ($id) => "#{$id}", $idsEmpleados));
    }

    private function boolAString(bool $valor): string
    {
        return $valor ? 'si' : 'no';
    }
}
