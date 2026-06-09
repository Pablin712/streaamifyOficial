<?php

namespace Database\Seeders;

use App\Models\ChatMemoriaNegocio;
use Illuminate\Database\Seeder;

class ChatMemoriaNegocioSeeder extends Seeder
{
    public function run(): void
    {
        $items = [

            // ─── GUIONES / ESTILO ────────────────────────────────────────────────
            [
                'tipo' => 'guion',
                'clave' => 'asistente_estilo_general',
                'titulo' => 'Estilo de respuesta del asistente',
                'contenido' => 'Responde rápido, firme, seguro y amable. Tono formal y elegante. Mensajes cortos y accionables. Máx. 2 emojis por respuesta. No uses: "Entiendo tu frustración", "Lamento el inconveniente", "Gracias por tu paciencia". Sí usa: "Un momento, verifico.", "Listo, ya quedó.", "Verifica e intenta."',
                'resumen' => 'Tono formal, breve y seguro con máximo 2 emojis. Sin frases de relleno.',
                'visibilidad' => 'ambas',
                'tags' => ['estilo', 'tono', 'asistente'],
                'fuente' => 'subagente_asistente',
                'prioridad' => 1,
                'activo' => true,
            ],
            [
                'tipo' => 'guion',
                'clave' => 'registro_cliente_automatico',
                'titulo' => 'Registro automático de clientes',
                'contenido' => 'El sistema registra al cliente automáticamente cuando envía su comprobante de pago. NUNCA pidas nombre ni apellido. El cliente se identifica por su número de teléfono. Si el agente necesita referirse al cliente, usa el nombre disponible en el contacto.',
                'resumen' => 'Registro automático por comprobante — nunca pedir nombre ni apellido.',
                'visibilidad' => 'ambas',
                'tags' => ['flujo', 'registro', 'cliente', 'automatico'],
                'fuente' => 'subagente_asistente',
                'prioridad' => 2,
                'activo' => true,
            ],

            // ─── MARCA ───────────────────────────────────────────────────────────
            [
                'tipo' => 'marca',
                'clave' => 'marca_origen',
                'titulo' => 'Origen y cobertura de Streamify',
                'contenido' => 'Streamify es una empresa con sede en Ibarra, Ecuador. Atendemos a todo el país. Ofrecemos cuentas de entretenimiento digital con soporte, garantía y atención personalizada por WhatsApp.',
                'resumen' => 'Empresa en Ibarra con cobertura nacional en Ecuador.',
                'visibilidad' => 'cliente',
                'tags' => ['marca', 'ubicacion', 'quienes-somos'],
                'fuente' => 'negocio',
                'prioridad' => 10,
                'activo' => true,
            ],

            // ─── FAQ ─────────────────────────────────────────────────────────────
            [
                'tipo' => 'faq',
                'clave' => 'faq_quienes_somos',
                'titulo' => '¿Quiénes somos?',
                'contenido' => 'Somos Streamify, una empresa dedicada a la venta de cuentas de entretenimiento digital. Brindamos acceso fácil y seguro a plataformas de streaming con soporte y garantía. Operamos desde Ibarra y atendemos a todo Ecuador.',
                'resumen' => 'Empresa de entretenimiento digital con soporte y garantía.',
                'visibilidad' => 'cliente',
                'tags' => ['faq', 'marca', 'confianza'],
                'fuente' => 'negocio',
                'prioridad' => 8,
                'activo' => true,
            ],
            [
                'tipo' => 'faq',
                'clave' => 'faq_horario_atencion',
                'titulo' => 'Horario de atención',
                'contenido' => 'Nuestro horario de atención es lunes a viernes de 9am a 6pm. Los mensajes recibidos fuera del horario se atienden el siguiente día hábil. Casos urgentes de soporte pueden tener respuesta el mismo día aunque sea fuera de horario.',
                'resumen' => 'Atención L-V 9am-6pm. Mensajes fuera de horario al siguiente día hábil.',
                'visibilidad' => 'cliente',
                'tags' => ['faq', 'horario'],
                'fuente' => 'negocio',
                'prioridad' => 5,
                'activo' => true,
            ],
            [
                'tipo' => 'faq',
                'clave' => 'faq_servicios_disponibles',
                'titulo' => 'Servicios / plataformas disponibles',
                'contenido' => 'Ofrecemos cuentas de las principales plataformas de streaming: Netflix, Disney+, Max (HBO), Paramount+, Crunchyroll, Prime Video (Amazon), Spotify y Flujo TV. Para precios y disponibilidad actualizada, consulta el catálogo o pregunta al vendedor.',
                'resumen' => 'Netflix, Disney+, Max, Paramount+, Crunchyroll, Prime Video, Spotify, Flujo TV.',
                'visibilidad' => 'cliente',
                'tags' => ['faq', 'servicios', 'catalogo', 'plataformas'],
                'fuente' => 'negocio',
                'prioridad' => 25,
                'activo' => true,
            ],
            [
                'tipo' => 'faq',
                'clave' => 'faq_entrega_inmediata',
                'titulo' => '¿La entrega es inmediata?',
                'contenido' => 'Sí, cuando hay disponibilidad la entrega es inmediata una vez confirmado el pago. Si no hay stock en el momento, notificamos al cliente en cuanto se libere una cuenta, normalmente en el mismo día.',
                'resumen' => 'Entrega inmediata sujeta a disponibilidad; si no hay stock, aviso el mismo día.',
                'visibilidad' => 'cliente',
                'tags' => ['faq', 'entrega', 'tiempo'],
                'fuente' => 'negocio',
                'prioridad' => 22,
                'activo' => true,
            ],
            [
                'tipo' => 'faq',
                'clave' => 'faq_pago_tarjeta',
                'titulo' => '¿Aceptan pago con tarjeta?',
                'contenido' => 'Por el momento no aceptamos pagos con tarjeta de crédito o débito. Solo aceptamos transferencias bancarias y Binance USDT. Consulta los bancos disponibles si necesitas más detalle.',
                'resumen' => 'No hay pago con tarjeta; solo transferencia bancaria y Binance USDT.',
                'visibilidad' => 'cliente',
                'tags' => ['faq', 'pago', 'tarjeta'],
                'fuente' => 'negocio',
                'prioridad' => 20,
                'activo' => true,
            ],
            [
                'tipo' => 'faq',
                'clave' => 'faq_tiempo_soporte',
                'titulo' => '¿Cuánto demoran en resolver mi soporte?',
                'contenido' => 'Los casos de soporte se revisan en menos de 1 hora durante horario de atención (L-V 9am-6pm). Los problemas de acceso urgentes (cuenta caída, contraseña incorrecta) suelen resolverse en 30 minutos. Recibirás un mensaje de WhatsApp cuando tu ticket sea atendido.',
                'resumen' => 'Resolución en <1 hora en horario. Urgentes en ~30 min. Notificación por WhatsApp.',
                'visibilidad' => 'cliente',
                'tags' => ['faq', 'soporte', 'tiempo', 'ticket'],
                'fuente' => 'negocio',
                'prioridad' => 15,
                'activo' => true,
            ],
            [
                'tipo' => 'faq',
                'clave' => 'faq_garantia_cuenta',
                'titulo' => '¿Las cuentas tienen garantía?',
                'contenido' => 'Sí. Todas las cuentas tienen garantía. Si hay problemas de acceso relacionados con la cuenta (contraseña incorrecta sin haberla cambiado tú, cuenta caída, error del sistema), lo resolvemos sin costo adicional. La garantía NO cubre: servicio vencido, dispositivos bloqueados por el proveedor por exceso de pantallas, ni cambios de contraseña realizados por el cliente.',
                'resumen' => 'Garantía cubre problemas de acceso del sistema. No cubre vencimiento ni bloqueos por mal uso.',
                'visibilidad' => 'cliente',
                'tags' => ['faq', 'garantia', 'soporte', 'cobertura'],
                'fuente' => 'negocio',
                'prioridad' => 12,
                'activo' => true,
            ],
            [
                'tipo' => 'faq',
                'clave' => 'faq_renovacion',
                'titulo' => '¿Cómo renuevo mi servicio?',
                'contenido' => 'Para renovar tu servicio, escríbenos por WhatsApp indicando qué plataforma quieres renovar y por cuántos meses. Te confirmamos el precio, realizas la transferencia, envías el comprobante y lo activamos de inmediato.',
                'resumen' => 'Renovar: escribe, confirma precio, transfiere, envía comprobante, listo.',
                'visibilidad' => 'cliente',
                'tags' => ['faq', 'renovacion', 'flujo'],
                'fuente' => 'negocio',
                'prioridad' => 18,
                'activo' => true,
            ],

            // ─── MÉTODO DE PAGO ───────────────────────────────────────────────────
            [
                'tipo' => 'metodo_pago',
                'clave' => 'metodos_pago_disponibles',
                'titulo' => 'Métodos de pago disponibles',
                'contenido' => 'Aceptamos pagos por transferencia bancaria y Binance USDT. Bancos disponibles: Banco Pichincha, Banco Guayaquil / Banco del Barrio, Produbanco / Be Produbanco, Banco Bolivariano, Banco Internacional. Para pago con Binance USDT solicita el ID de billetera al vendedor. No aceptamos efectivo ni tarjetas.',
                'resumen' => 'Transferencias: Pichincha, Guayaquil, Produbanco, Bolivariano, Internacional. También Binance USDT.',
                'visibilidad' => 'cliente',
                'tags' => ['pago', 'bancos', 'transferencia', 'binance'],
                'fuente' => 'negocio',
                'prioridad' => 15,
                'activo' => true,
            ],
            [
                'tipo' => 'metodo_pago',
                'clave' => 'flujo_envio_comprobante',
                'titulo' => 'Cómo enviar el comprobante de pago',
                'contenido' => 'Después de hacer la transferencia, envía la foto del comprobante por este WhatsApp. El sistema la verifica automáticamente y acredita tu saldo en pocos minutos. Si no recibes confirmación en 10 minutos, escríbenos.',
                'resumen' => 'Foto del comprobante por WhatsApp → verificación automática → saldo acreditado.',
                'visibilidad' => 'cliente',
                'tags' => ['pago', 'comprobante', 'flujo', 'transferencia'],
                'fuente' => 'negocio',
                'prioridad' => 16,
                'activo' => true,
            ],

            // ─── CONFIANZA ───────────────────────────────────────────────────────
            [
                'tipo' => 'confianza',
                'clave' => 'confianza_cuentas_seguras',
                'titulo' => 'Seguridad y garantía de las cuentas',
                'contenido' => 'Las cuentas son garantizadas. Si hay fallas de acceso, se brinda garantía y soporte técnico sin costo. Operamos con transparencia: cada venta queda registrada y el cliente puede consultar su historial en cualquier momento.',
                'resumen' => 'Cuentas con garantía, soporte técnico y registro de cada venta.',
                'visibilidad' => 'cliente',
                'tags' => ['confianza', 'garantia', 'seguridad'],
                'fuente' => 'negocio',
                'prioridad' => 12,
                'activo' => true,
            ],

            // ─── OBJECIONES ──────────────────────────────────────────────────────
            [
                'tipo' => 'objecion',
                'clave' => 'objecion_confiabilidad',
                'titulo' => 'Objeción: ¿cómo sé que son confiables?',
                'contenido' => 'Responder: "Somos Streamify, operamos desde Ibarra y tenemos clientes satisfechos en todo Ecuador. Cada venta tiene garantía y soporte. Puedes ver nuestro sitio web y nuestro historial de atención. Si algo falla, lo resolvemos — es nuestra política."',
                'resumen' => 'Responder con trayectoria, cobertura nacional, garantía y sitio web.',
                'visibilidad' => 'cliente',
                'tags' => ['objecion', 'confianza', 'ventas'],
                'fuente' => 'negocio',
                'prioridad' => 30,
                'activo' => true,
            ],
            [
                'tipo' => 'objecion',
                'clave' => 'objecion_precio_caro',
                'titulo' => 'Objeción: está muy caro',
                'contenido' => 'Responder: "Entendemos. A diferencia de comprar directo a la plataforma (que cuesta $X/mes), aquí pagas fracción del precio y además tienes soporte incluido. Si algo falla, te lo resolvemos — no estás solo después de la compra."',
                'resumen' => 'Precio justificado por ahorro vs. precio oficial + soporte incluido.',
                'visibilidad' => 'cliente',
                'tags' => ['objecion', 'precio', 'ventas'],
                'fuente' => 'negocio',
                'prioridad' => 32,
                'activo' => true,
            ],

            // ─── POLÍTICAS DE VENTA ──────────────────────────────────────────────
            [
                'tipo' => 'politica_venta',
                'clave' => 'politica_no_reembolso',
                'titulo' => 'Política de no reembolso',
                'contenido' => 'No se realizan reembolsos una vez entregada la cuenta, salvo que la cuenta nunca haya funcionado. Si la cuenta falla después de la entrega, se aplica garantía de reemplazo o solución técnica, no devolución de dinero.',
                'resumen' => 'No hay reembolsos. Fallas post-entrega se cubren con garantía (reemplazo/soporte).',
                'visibilidad' => 'ambas',
                'tags' => ['politica', 'reembolso', 'venta'],
                'fuente' => 'negocio',
                'prioridad' => 20,
                'activo' => true,
            ],
            [
                'tipo' => 'politica_venta',
                'clave' => 'politica_no_compartir',
                'titulo' => 'Política: no compartir credenciales',
                'contenido' => 'Las cuentas son para uso del cliente. No se deben compartir las credenciales con terceros ni publicarlas. Hacerlo puede provocar bloqueo de la cuenta y pérdida de la garantía.',
                'resumen' => 'No compartir credenciales — bloqueo por mal uso pierde la garantía.',
                'visibilidad' => 'cliente',
                'tags' => ['politica', 'credenciales', 'seguridad'],
                'fuente' => 'negocio',
                'prioridad' => 25,
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

        // Eliminar entrada obsoleta que contradice el comportamiento actual del agente
        ChatMemoriaNegocio::where('clave', 'guion_identificacion_lead')->delete();
    }
}
