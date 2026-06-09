<?php

namespace Database\Seeders;

use App\Models\ChatSubagente;
use Illuminate\Database\Seeder;

class ChatSubagenteSeeder extends Seeder
{
    // Regla estándar que se inyecta al prompt de todo subagente que puede usar imágenes.
    // n8n lee esto desde el campo prompt_base del contexto.
    private const IMAGEN_REGLAS = <<<'RULES'

REGLAS PARA ENVÍO DE IMÁGENES:
- Tienes las herramientas `obtener_imagenes_agente` y `enviar_imagen_agente`.
- `obtener_imagenes_agente` devuelve la lista con (id, nombre, descripcion, categoria, tags). Úsala SOLO si el cliente pregunta algo que una imagen podría aclarar mejor (catálogo, precios visuales, pasos de instalación, etc.).
- ANTES de enviar una imagen, revisa `imagenes_agente_recientes` del contexto. Si esa imagen ya fue enviada en esta conversación (campo imagen_id presente), NO la vuelvas a enviar aunque el cliente la pida de nuevo — redirígela con texto.
- Máximo UNA imagen por respuesta. Nunca envíes imágenes de forma decorativa o sin motivo claro.
- No envíes imágenes si en el `historial_reciente` ya hay un mensaje de tipo 'imagen' del agente en los últimos 3 mensajes del bot.
- Parámetros de `enviar_imagen_agente`: { id, numero: <external_chat_id del contacto>, instance_name: <canal.instance_name>, idconv: <idconv>, caption: <texto breve opcional> }.
RULES;

    public function run(): void
    {
        $subagentes = [
            [
                'codigo' => 'router_general',
                'nombre' => 'Router General',
                'tipo' => 'router',
                'descripcion' => 'Clasifica el mensaje, detecta estado del cliente y selecciona el subagente operativo.',
                'prompt_base' => 'Clasifica el mensaje y deriva al subagente correcto sin responder con texto largo.',
                'criterios' => [
                    'prioridad' => ['solicitud_humano', 'pago', 'compra_reciente', 'soporte_cliente', 'nuevo_lead'],
                    'handoff_humano' => [
                        'activar_si_intencion' => ['humano', 'asesor', 'persona', 'agente real', 'operador'],
                        'silencio_bot' => true,
                        'volver_a_ia_si_mensajes_cliente_sin_respuesta_humana' => 3,
                        'ventana_minutos' => 10,
                    ],
                ],
                'tools' => ['buscar_cliente', 'consultar_historial_cliente'],
                'prioridad' => 1,
                'activo' => true,
            ],
            [
                'codigo' => 'espera_humano',
                'nombre' => 'Espera de Humano',
                'tipo' => 'router',
                'descripcion' => 'La conversacion queda en espera de un humano. El bot no responde, salvo si el cliente insiste demasiado y nadie lo atiende.',
                'prompt_base' => 'No responder. Mantener silencio hasta intervencion humana o reactivacion del router.',
                'criterios' => [
                    'modo' => 'silencioso',
                    'activar_si_intencion' => ['humano', 'asesor', 'persona', 'operador'],
                    'reactivar_ia_si' => [
                        'mensajes_cliente_sin_respuesta_humana' => 3,
                        'ventana_minutos' => 10,
                        'sin_mensaje_empleado_desde_minutos' => 10,
                    ],
                ],
                'tools' => [],
                'prioridad' => 5,
                'activo' => true,
            ],
            [
                'codigo' => 'asistente_no_registrado',
                'nombre' => 'Asistente No Registrado',
                'tipo' => 'asistente',
                'descripcion' => 'Atiende leads nuevos, responde preguntas frecuentes y detecta intención comercial.',
                'prompt_base' => 'Responde rapido, firme y amable. Estilo formal y elegante. Mensajes cortos, directos, claros, maximo 1-2 emojis por respuesta. Siempre termina con una accion concreta.' . self::IMAGEN_REGLAS,
                'criterios' => [
                    'requiere_cliente' => false,
                    'estado_relacion' => ['lead'],
                    'estilo' => [
                        'tono' => 'formal_elegante',
                        'rapidez' => 'alta',
                        'seguridad' => 'alta',
                        'amabilidad' => 'alta',
                        'max_emojis' => 2,
                        'max_lineas' => 5,
                    ],
                    'flujo_identificacion' => [
                        'paso_1' => 'buscar_cliente_por_telefono',
                        'paso_2_si_no_existe' => 'solicitar_nombre_apellido',
                        'paso_3_si_nombre_disponible' => 'crear_cliente_rapido',
                    ],
                ],
                'tools' => [
                    'buscar_cliente_por_telefono',
                    'crear_cliente_rapido',
                    'consultar_precios',
                    'consultar_metodos_pago',
                    'consultar_bancos',
                    'consultar_banco_por_nombre',
                    'handoff_humano',
                    'obtener_imagenes_agente',
                    'enviar_imagen_agente',
                ],
                'prioridad' => 10,
                'activo' => true,
            ],
            [
                'codigo' => 'vendedor_cierre',
                'nombre' => 'Vendedor de Cierre',
                'tipo' => 'vendedor',
                'descripcion' => 'Cierra ventas, sugiere plan o combo y conduce a pago.',
                'prompt_base' => 'Prioriza cierre, claridad, margen y una sola llamada a la accion por mensaje.' . self::IMAGEN_REGLAS,
                'criterios' => [
                    'intenciones' => ['precio', 'plan', 'combo', 'comprar', 'descuento'],
                ],
                'tools' => [
                    'consultar_precios',
                    'consultar_planes_por_servicio',
                    'consultar_combos',
                    'calcular_descuento_permitido',
                    'consultar_metodos_pago',
                    'obtener_imagenes_agente',
                    'enviar_imagen_agente',
                ],
                'prioridad' => 20,
                'activo' => true,
            ],
            [
                'codigo' => 'soporte_cliente',
                'nombre' => 'Soporte Cliente',
                'tipo' => 'soporte',
                'descripcion' => 'Atiende clientes ya registrados con incidencias de acceso, servicio o estado.',
                'prompt_base' => 'Actua como soporte resolutivo, preciso y sereno. Primero diagnostica, luego indica accion.' . self::IMAGEN_REGLAS,
                'criterios' => [
                    'requiere_cliente' => true,
                    'intenciones' => ['soporte', 'falla', 'no entra', 'contrasena', 'pantalla'],
                ],
                'tools' => [
                    'buscar_cliente',
                    'consultar_historial_cliente',
                    'consultar_perfiles_disponibles',
                    'obtener_imagenes_agente',
                    'enviar_imagen_agente',
                ],
                'prioridad' => 30,
                'activo' => true,
            ],
            [
                'codigo' => 'cobranzas_pago',
                'nombre' => 'Cobranzas y Pago',
                'tipo' => 'cobranzas',
                'descripcion' => 'Guia metodos de pago y datos bancarios. La recepcion/validacion de comprobantes la maneja el flujo verificador de pagos.',
                'prompt_base' => 'Guia el pago con instrucciones cortas. Para comprobantes, deriva al flujo de subidor/verificador sin duplicar procesos.' . self::IMAGEN_REGLAS,
                'criterios' => [
                    'intenciones' => ['pagar', 'comprobante', 'transferencia', 'banco'],
                ],
                'tools' => [
                    'consultar_metodos_pago',
                    'consultar_banco_por_nombre',
                    'obtener_imagenes_agente',
                    'enviar_imagen_agente',
                ],
                'prioridad' => 40,
                'activo' => true,
            ],
            [
                'codigo' => 'postventa_reciente',
                'nombre' => 'Postventa Reciente',
                'tipo' => 'postventa',
                'descripcion' => 'Atiende clientes que acaban de comprar, confirma entrega, seguimiento inicial y deteccion temprana de incidencias.',
                'prompt_base' => 'Confirma contexto post-compra, responde dudas de seguimiento y rerutea a soporte/cobranzas cuando corresponda.',
                'criterios' => [
                    'compra_reciente_horas' => 48,
                ],
                'tools' => ['consultar_contexto_postventa', 'consultar_memoria_general', 'handoff_humano'],
                'prioridad' => 50,
                'activo' => true,
            ],
        ];

        foreach ($subagentes as $subagente) {
            ChatSubagente::updateOrCreate(
                ['codigo' => $subagente['codigo']],
                $subagente
            );
        }
    }
}
