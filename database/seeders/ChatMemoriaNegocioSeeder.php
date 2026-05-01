<?php

namespace Database\Seeders;

use App\Models\ChatMemoriaNegocio;
use Illuminate\Database\Seeder;

class ChatMemoriaNegocioSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'tipo' => 'guion',
                'clave' => 'asistente_estilo_general',
                'titulo' => 'Estilo de respuesta del asistente',
                'contenido' => 'Responder rapido, firme, seguro y amable. Mantener tono formal y elegante. Usar mensajes cortos y accionables. No usar mas de 2 emojis por respuesta.',
                'resumen' => 'Tono formal, breve y seguro con maximo 2 emojis.',
                'visibilidad' => 'ambas',
                'tags' => ['estilo', 'tono', 'asistente', 'rapidez'],
                'fuente' => 'subagente_asistente',
                'prioridad' => 1,
                'activo' => true,
            ],
            [
                'tipo' => 'faq',
                'clave' => 'faq_horario_atencion',
                'titulo' => 'Horario de atencion',
                'contenido' => 'Nuestro horario de atencion es de lunes a viernes de 9am a 6pm.',
                'resumen' => 'Atencion de lunes a viernes de 9am a 6pm.',
                'visibilidad' => 'cliente',
                'tags' => ['faq', 'horario'],
                'fuente' => 'negocio',
                'prioridad' => 5,
                'activo' => true,
            ],
            [
                'tipo' => 'faq',
                'clave' => 'faq_quienes_somos',
                'titulo' => 'Quienes somos',
                'contenido' => 'Somos una empresa dedicada a la venta de cuentas de entretenimiento digital. Brindamos acceso facil y seguro con soporte y garantia.',
                'resumen' => 'Empresa de entretenimiento digital con soporte y garantia.',
                'visibilidad' => 'cliente',
                'tags' => ['faq', 'marca', 'confianza'],
                'fuente' => 'negocio',
                'prioridad' => 8,
                'activo' => true,
            ],
            [
                'tipo' => 'marca',
                'clave' => 'marca_origen',
                'titulo' => 'Origen de la empresa',
                'contenido' => 'Somos una empresa con sede en Ibarra y atendemos a todo Ecuador.',
                'resumen' => 'Sede en Ibarra con cobertura nacional.',
                'visibilidad' => 'cliente',
                'tags' => ['marca', 'ubicacion'],
                'fuente' => 'negocio',
                'prioridad' => 10,
                'activo' => true,
            ],
            [
                'tipo' => 'confianza',
                'clave' => 'confianza_cuentas_seguras',
                'titulo' => 'Seguridad y garantia',
                'contenido' => 'Las cuentas son garantizadas. Si hay fallas, se brinda garantia y soporte tecnico.',
                'resumen' => 'Cuentas con garantia y soporte tecnico.',
                'visibilidad' => 'cliente',
                'tags' => ['confianza', 'garantia', 'soporte'],
                'fuente' => 'negocio',
                'prioridad' => 12,
                'activo' => true,
            ],
            [
                'tipo' => 'metodo_pago',
                'clave' => 'metodos_pago_disponibles',
                'titulo' => 'Metodos de pago disponibles',
                'contenido' => 'Aceptamos pagos por transferencia bancaria. Bancos disponibles: Pichincha, Guayaquil, Produbanco, Be Produbanco, Bolivariano, Internacional y Binance USDT.',
                'resumen' => 'Transferencias bancarias y Binance USDT disponibles.',
                'visibilidad' => 'cliente',
                'tags' => ['pago', 'bancos', 'transferencia'],
                'fuente' => 'negocio',
                'prioridad' => 15,
                'activo' => true,
            ],
            [
                'tipo' => 'faq',
                'clave' => 'faq_pago_tarjeta',
                'titulo' => 'Pago con tarjeta',
                'contenido' => 'Por el momento no se aceptan pagos con tarjeta de credito. Solo transferencias bancarias y Binance USDT.',
                'resumen' => 'No hay pago con tarjeta; solo transferencia y USDT.',
                'visibilidad' => 'cliente',
                'tags' => ['faq', 'pago', 'tarjeta'],
                'fuente' => 'negocio',
                'prioridad' => 20,
                'activo' => true,
            ],
            [
                'tipo' => 'faq',
                'clave' => 'faq_entrega_inmediata',
                'titulo' => 'Entrega inmediata',
                'contenido' => 'Si, cuando hay disponibilidad la entrega es inmediata.',
                'resumen' => 'Entrega inmediata sujeta a disponibilidad.',
                'visibilidad' => 'cliente',
                'tags' => ['faq', 'entrega'],
                'fuente' => 'negocio',
                'prioridad' => 22,
                'activo' => true,
            ],
            [
                'tipo' => 'faq',
                'clave' => 'faq_servicios_disponibles',
                'titulo' => 'Servicios disponibles',
                'contenido' => 'Servicios frecuentes: Netflix, Disney, Max, Paramount, Crunchyroll, Prime Video, Spotify y Flujo TV.',
                'resumen' => 'Catalogo principal de servicios digitales.',
                'visibilidad' => 'cliente',
                'tags' => ['faq', 'servicios', 'catalogo'],
                'fuente' => 'negocio',
                'prioridad' => 25,
                'activo' => true,
            ],
            [
                'tipo' => 'objecion',
                'clave' => 'objecion_confiabilidad',
                'titulo' => 'Objecion de confiabilidad',
                'contenido' => 'Puedes responder que la empresa tiene clientes satisfechos, opera desde hace anos y cuenta con sitio web y procesos automatizados para atencion y soporte.',
                'resumen' => 'Responder objecion de confianza con evidencia de trayectoria y operacion.',
                'visibilidad' => 'cliente',
                'tags' => ['objecion', 'confianza', 'ventas'],
                'fuente' => 'negocio',
                'prioridad' => 30,
                'activo' => true,
            ],
            [
                'tipo' => 'guion',
                'clave' => 'guion_identificacion_lead',
                'titulo' => 'Flujo de identificacion lead',
                'contenido' => 'Paso 1: buscar cliente por telefono. Paso 2: si no existe, pedir nombre y apellido. Paso 3: crear cliente. Paso 4: continuar con atencion comercial.',
                'resumen' => 'Identificar por telefono y crear cliente si no existe.',
                'visibilidad' => 'ambas',
                'tags' => ['flujo', 'lead', 'identificacion'],
                'fuente' => 'subagente_asistente',
                'prioridad' => 2,
                'activo' => true,
            ],
        ];

        foreach ($items as $item) {
            ChatMemoriaNegocio::updateOrCreate(
                [
                    'tipo' => $item['tipo'],
                    'clave' => $item['clave'],
                ],
                $item
            );
        }
    }
}
