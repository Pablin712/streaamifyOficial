<?php

namespace Database\Seeders;

use App\Models\ChatMemoriaNegocio;
use Illuminate\Database\Seeder;

class ChatMemoriaNegocioSeeder extends Seeder
{
    public function run(): void
    {
        $items = [

            // ─── GENERAL: GUIONES / ESTILO ───────────────────────────────────────
            [
                'tipo' => 'guion', 'categoria' => 'general',
                'clave' => 'asistente_estilo_general',
                'titulo' => 'Estilo de respuesta del asistente',
                'contenido' => 'Responde rápido, firme, seguro y amable. Tono formal y elegante. Mensajes cortos y accionables. Máx. 2 emojis por respuesta. No uses: "Entiendo tu frustración", "Lamento el inconveniente", "Gracias por tu paciencia". Sí usa: "Un momento, verifico.", "Listo, ya quedó.", "Verifica e intenta."',
                'resumen' => 'Tono formal, breve y seguro con máximo 2 emojis. Sin frases de relleno.',
                'visibilidad' => 'ambas', 'tags' => ['estilo', 'tono', 'asistente'],
                'fuente' => 'subagente_asistente', 'prioridad' => 1, 'activo' => true,
            ],
            [
                'tipo' => 'guion', 'categoria' => 'general',
                'clave' => 'registro_cliente_automatico',
                'titulo' => 'Registro automático de clientes',
                'contenido' => 'El sistema registra al cliente automáticamente cuando envía su comprobante de pago. NUNCA pidas nombre ni apellido. El cliente se identifica por su número de teléfono. Si el agente necesita referirse al cliente, usa el nombre disponible en el contacto.',
                'resumen' => 'Registro automático por comprobante — nunca pedir nombre ni apellido.',
                'visibilidad' => 'ambas', 'tags' => ['flujo', 'registro', 'cliente', 'automatico'],
                'fuente' => 'subagente_asistente', 'prioridad' => 2, 'activo' => true,
            ],

            // ─── GENERAL: MARCA ──────────────────────────────────────────────────
            [
                'tipo' => 'marca', 'categoria' => 'general',
                'clave' => 'marca_origen',
                'titulo' => 'Origen y cobertura de Streamify',
                'contenido' => 'Streamify es una empresa con sede en Ibarra, Ecuador. Atendemos a todo el país. Ofrecemos cuentas de entretenimiento digital con soporte, garantía y atención personalizada por WhatsApp.',
                'resumen' => 'Empresa en Ibarra con cobertura nacional en Ecuador.',
                'visibilidad' => 'cliente', 'tags' => ['marca', 'ubicacion', 'quienes-somos'],
                'fuente' => 'negocio', 'prioridad' => 10, 'activo' => true,
            ],

            // ─── GENERAL: FAQ ─────────────────────────────────────────────────────
            [
                'tipo' => 'faq', 'categoria' => 'general',
                'clave' => 'faq_quienes_somos',
                'titulo' => '¿Quiénes somos?',
                'contenido' => 'Somos Streamify, una empresa dedicada a la venta de cuentas de entretenimiento digital. Brindamos acceso fácil y seguro a plataformas de streaming con soporte y garantía. Operamos desde Ibarra y atendemos a todo Ecuador.',
                'resumen' => 'Empresa de entretenimiento digital con soporte y garantía.',
                'visibilidad' => 'cliente', 'tags' => ['faq', 'marca', 'confianza'],
                'fuente' => 'negocio', 'prioridad' => 8, 'activo' => true,
            ],
            [
                'tipo' => 'faq', 'categoria' => 'general',
                'clave' => 'faq_horario_atencion',
                'titulo' => 'Horario de atención',
                'contenido' => 'Nuestro horario de atención es lunes a viernes de 9am a 6pm. Los mensajes recibidos fuera del horario se atienden el siguiente día hábil. Casos urgentes de soporte pueden tener respuesta el mismo día aunque sea fuera de horario.',
                'resumen' => 'Atención L-V 9am-6pm. Mensajes fuera de horario al siguiente día hábil.',
                'visibilidad' => 'cliente', 'tags' => ['faq', 'horario'],
                'fuente' => 'negocio', 'prioridad' => 5, 'activo' => true,
            ],
            [
                'tipo' => 'faq', 'categoria' => 'general',
                'clave' => 'faq_servicios_disponibles',
                'titulo' => 'Servicios / plataformas disponibles',
                'contenido' => 'Ofrecemos cuentas de las principales plataformas de streaming: Netflix, Disney+, Max (HBO), Paramount+, Crunchyroll, Prime Video (Amazon), Spotify y Flujo TV. Para precios y disponibilidad actualizada, consulta el catálogo o pregunta al vendedor.',
                'resumen' => 'Netflix, Disney+, Max, Paramount+, Crunchyroll, Prime Video, Spotify, Flujo TV.',
                'visibilidad' => 'cliente', 'tags' => ['faq', 'servicios', 'catalogo', 'plataformas'],
                'fuente' => 'negocio', 'prioridad' => 25, 'activo' => true,
            ],
            [
                'tipo' => 'faq', 'categoria' => 'general',
                'clave' => 'faq_entrega_inmediata',
                'titulo' => '¿La entrega es inmediata?',
                'contenido' => 'Sí, cuando hay disponibilidad la entrega es inmediata una vez confirmado el pago. Si no hay stock en el momento, notificamos al cliente en cuanto se libere una cuenta, normalmente en el mismo día.',
                'resumen' => 'Entrega inmediata sujeta a disponibilidad; si no hay stock, aviso el mismo día.',
                'visibilidad' => 'cliente', 'tags' => ['faq', 'entrega', 'tiempo'],
                'fuente' => 'negocio', 'prioridad' => 22, 'activo' => true,
            ],
            [
                'tipo' => 'faq', 'categoria' => 'general',
                'clave' => 'faq_pago_tarjeta',
                'titulo' => '¿Aceptan pago con tarjeta?',
                'contenido' => 'Por el momento no aceptamos pagos con tarjeta de crédito o débito. Solo aceptamos transferencias bancarias y Binance USDT. Consulta los bancos disponibles si necesitas más detalle.',
                'resumen' => 'No hay pago con tarjeta; solo transferencia bancaria y Binance USDT.',
                'visibilidad' => 'cliente', 'tags' => ['faq', 'pago', 'tarjeta'],
                'fuente' => 'negocio', 'prioridad' => 20, 'activo' => true,
            ],
            [
                'tipo' => 'faq', 'categoria' => 'general',
                'clave' => 'faq_tiempo_soporte',
                'titulo' => '¿Cuánto demoran en resolver mi soporte?',
                'contenido' => 'Los casos de soporte se revisan en menos de 1 hora durante horario de atención (L-V 9am-6pm). Los problemas de acceso urgentes (cuenta caída, contraseña incorrecta) suelen resolverse en 30 minutos. Recibirás un mensaje de WhatsApp cuando tu ticket sea atendido.',
                'resumen' => 'Resolución en <1 hora en horario. Urgentes en ~30 min. Notificación por WhatsApp.',
                'visibilidad' => 'cliente', 'tags' => ['faq', 'soporte', 'tiempo', 'ticket'],
                'fuente' => 'negocio', 'prioridad' => 15, 'activo' => true,
            ],
            [
                'tipo' => 'faq', 'categoria' => 'general',
                'clave' => 'faq_garantia_cuenta',
                'titulo' => '¿Las cuentas tienen garantía?',
                'contenido' => 'Sí. Todas las cuentas tienen garantía. Si hay problemas de acceso relacionados con la cuenta (contraseña incorrecta sin haberla cambiado tú, cuenta caída, error del sistema), lo resolvemos sin costo adicional. La garantía NO cubre: servicio vencido, dispositivos bloqueados por el proveedor por exceso de pantallas, ni cambios de contraseña realizados por el cliente.',
                'resumen' => 'Garantía cubre problemas de acceso del sistema. No cubre vencimiento ni bloqueos por mal uso.',
                'visibilidad' => 'cliente', 'tags' => ['faq', 'garantia', 'soporte', 'cobertura'],
                'fuente' => 'negocio', 'prioridad' => 12, 'activo' => true,
            ],
            [
                'tipo' => 'faq', 'categoria' => 'general',
                'clave' => 'faq_renovacion',
                'titulo' => '¿Cómo renuevo mi servicio?',
                'contenido' => 'Para renovar tu servicio, escríbenos por WhatsApp indicando qué plataforma quieres renovar y por cuántos meses. Te confirmamos el precio, realizas la transferencia, envías el comprobante y lo activamos de inmediato.',
                'resumen' => 'Renovar: escribe, confirma precio, transfiere, envía comprobante, listo.',
                'visibilidad' => 'cliente', 'tags' => ['faq', 'renovacion', 'flujo'],
                'fuente' => 'negocio', 'prioridad' => 18, 'activo' => true,
            ],

            // ─── GENERAL: MÉTODO DE PAGO ──────────────────────────────────────────
            [
                'tipo' => 'metodo_pago', 'categoria' => 'general',
                'clave' => 'metodos_pago_disponibles',
                'titulo' => 'Métodos de pago disponibles',
                'contenido' => 'Aceptamos pagos por transferencia bancaria y Binance USDT. Bancos disponibles: Banco Pichincha, Banco Guayaquil / Banco del Barrio, Produbanco / Be Produbanco, Banco Bolivariano, Banco Internacional. Para pago con Binance USDT solicita el ID de billetera al vendedor. No aceptamos efectivo ni tarjetas.',
                'resumen' => 'Transferencias: Pichincha, Guayaquil, Produbanco, Bolivariano, Internacional. También Binance USDT.',
                'visibilidad' => 'cliente', 'tags' => ['pago', 'bancos', 'transferencia', 'binance'],
                'fuente' => 'negocio', 'prioridad' => 15, 'activo' => true,
            ],
            [
                'tipo' => 'metodo_pago', 'categoria' => 'general',
                'clave' => 'flujo_envio_comprobante',
                'titulo' => 'Cómo enviar el comprobante de pago',
                'contenido' => 'Después de hacer la transferencia, envía la foto del comprobante por este WhatsApp. El sistema la verifica automáticamente y acredita tu saldo en pocos minutos. Si no recibes confirmación en 10 minutos, escríbenos.',
                'resumen' => 'Foto del comprobante por WhatsApp → verificación automática → saldo acreditado.',
                'visibilidad' => 'cliente', 'tags' => ['pago', 'comprobante', 'flujo', 'transferencia'],
                'fuente' => 'negocio', 'prioridad' => 16, 'activo' => true,
            ],

            // ─── GENERAL: CONFIANZA ───────────────────────────────────────────────
            [
                'tipo' => 'confianza', 'categoria' => 'general',
                'clave' => 'confianza_cuentas_seguras',
                'titulo' => 'Seguridad y garantía de las cuentas',
                'contenido' => 'Las cuentas son garantizadas. Si hay fallas de acceso, se brinda garantía y soporte técnico sin costo. Operamos con transparencia: cada venta queda registrada y el cliente puede consultar su historial en cualquier momento.',
                'resumen' => 'Cuentas con garantía, soporte técnico y registro de cada venta.',
                'visibilidad' => 'cliente', 'tags' => ['confianza', 'garantia', 'seguridad'],
                'fuente' => 'negocio', 'prioridad' => 12, 'activo' => true,
            ],

            // ─── GENERAL: OBJECIONES ─────────────────────────────────────────────
            [
                'tipo' => 'objecion', 'categoria' => 'general',
                'clave' => 'objecion_confiabilidad',
                'titulo' => 'Objeción: ¿cómo sé que son confiables?',
                'contenido' => 'Responder: "Somos Streamify, operamos desde Ibarra y tenemos clientes satisfechos en todo Ecuador. Cada venta tiene garantía y soporte. Puedes ver nuestro sitio web y nuestro historial de atención. Si algo falla, lo resolvemos — es nuestra política."',
                'resumen' => 'Responder con trayectoria, cobertura nacional, garantía y sitio web.',
                'visibilidad' => 'cliente', 'tags' => ['objecion', 'confianza', 'ventas'],
                'fuente' => 'negocio', 'prioridad' => 30, 'activo' => true,
            ],
            [
                'tipo' => 'objecion', 'categoria' => 'general',
                'clave' => 'objecion_precio_caro',
                'titulo' => 'Objeción: está muy caro',
                'contenido' => 'Responder: "Entendemos. A diferencia de comprar directo a la plataforma (que cuesta $X/mes), aquí pagas fracción del precio y además tienes soporte incluido. Si algo falla, te lo resolvemos — no estás solo después de la compra."',
                'resumen' => 'Precio justificado por ahorro vs. precio oficial + soporte incluido.',
                'visibilidad' => 'cliente', 'tags' => ['objecion', 'precio', 'ventas'],
                'fuente' => 'negocio', 'prioridad' => 32, 'activo' => true,
            ],

            // ─── GENERAL: POLÍTICAS DE VENTA ─────────────────────────────────────
            [
                'tipo' => 'politica_venta', 'categoria' => 'general',
                'clave' => 'politica_no_reembolso',
                'titulo' => 'Política de no reembolso',
                'contenido' => 'No se realizan reembolsos una vez entregada la cuenta, salvo que la cuenta nunca haya funcionado. Si la cuenta falla después de la entrega, se aplica garantía de reemplazo o solución técnica, no devolución de dinero.',
                'resumen' => 'No hay reembolsos. Fallas post-entrega se cubren con garantía (reemplazo/soporte).',
                'visibilidad' => 'ambas', 'tags' => ['politica', 'reembolso', 'venta'],
                'fuente' => 'negocio', 'prioridad' => 20, 'activo' => true,
            ],
            [
                'tipo' => 'politica_venta', 'categoria' => 'general',
                'clave' => 'politica_no_compartir',
                'titulo' => 'Política: no compartir credenciales',
                'contenido' => 'Las cuentas son para uso del cliente. No se deben compartir las credenciales con terceros ni publicarlas. Hacerlo puede provocar bloqueo de la cuenta y pérdida de la garantía.',
                'resumen' => 'No compartir credenciales — bloqueo por mal uso pierde la garantía.',
                'visibilidad' => 'cliente', 'tags' => ['politica', 'credenciales', 'seguridad'],
                'fuente' => 'negocio', 'prioridad' => 25, 'activo' => true,
            ],

            // ─── SOPORTE GENERAL ─────────────────────────────────────────────────
            [
                'tipo' => 'soporte_escalado', 'categoria' => 'soporte',
                'clave' => 'soporte_criterio_escalado_universal',
                'titulo' => 'Criterio universal: cuándo crear ticket vs. resolver directo',
                'contenido' => "RESOLVER DIRECTAMENTE (sin ticket) cuando:\n- El cliente olvidó su contraseña y PUEDE recuperarla por email → guiar al cliente con el enlace de recuperación\n- La sesión se cerró sola → pedir iniciar sesión de nuevo\n- Error temporal de red → pedir reintentar en 10-15 minutos\n- Problema de caché o app desactualizada → guiar: limpiar caché, reinstalar, actualizar\n\nCREAR TICKET automáticamente cuando:\n- Los pasos básicos (un ciclo de diagnóstico) NO resuelven el problema\n- El cliente reporta que la contraseña fue cambiada sin que él la tocara (posible acceso no autorizado)\n- El cliente NO puede recuperar la cuenta por email (email no reconocido o no llega)\n- La cuenta aparece bloqueada o suspendida\n- El problema persiste en TODOS los dispositivos del cliente\n- El cliente lleva más de 24h con el mismo problema\n\nESCALAR A HUMANO (acción pasar_a_humano) cuando:\n- El cliente está muy molesto o amenaza con cancelar\n- Se detecta posible fraude o doble cobro\n- El cliente pide explícitamente hablar con un agente humano\n- El ticket lleva más de 24h sin resolverse y el cliente vuelve a preguntar",
                'resumen' => 'Resolver directo: contraseña olvidada, caché, sesión. Ticket: pasos fallidos, cuenta bloqueada. Humano: cancelación, fraude.',
                'visibilidad' => 'ambas', 'tags' => ['soporte', 'escalado', 'ticket', 'criterio'],
                'fuente' => 'soporte_playbook', 'prioridad' => 5, 'activo' => true,
            ],

            // ─── NETFLIX ─────────────────────────────────────────────────────────
            [
                'tipo' => 'soporte_pasos', 'categoria' => 'netflix',
                'clave' => 'soporte_pasos_netflix',
                'titulo' => 'Diagnóstico paso a paso: Netflix',
                'contenido' => "PROBLEMA 1 — Contraseña incorrecta / no puedo entrar:\nPASO 1: Preguntar si el error dice 'contraseña incorrecta' o 'cuenta bloqueada/suspendida'.\n→ Contraseña incorrecta: enviar al cliente a netflix.com/loginhelp para reseteo con su email.\n→ Si no recibe el email de recuperación o el email no es el registrado: CREAR TICKET tipo 'acceso'.\nPASO 2: Si resetea y sigue sin entrar (error 'cuenta no válida' o similar): CREAR TICKET tipo 'acceso'.\n\nPROBLEMA 2 — Demasiadas pantallas / error de concurrencia:\nPASO 1: Pedir al cliente que vaya a netflix.com → Cuenta → Administrar acceso y dispositivos → Cerrar sesión de todos los dispositivos.\nPASO 2: Esperar 30 minutos e intentar de nuevo.\n→ Si persiste después de 30 min: CREAR TICKET tipo 'acceso'.\nNOTA: Si el cliente tiene acceso por perfil compartido, recordarle que el plan limita pantallas simultáneas.\n\nPROBLEMA 3 — No carga / pantalla negra / se congela:\nPASO 1: Limpiar caché de la app y reiniciar el dispositivo.\nPASO 2: Desinstalar y reinstalar la app de Netflix.\nPASO 3: Probar en navegador web: netflix.com\n→ Si falla en TODOS los dispositivos/navegadores: CREAR TICKET tipo 'técnico'.\n\nPROBLEMA 4 — Perfil eliminado o modificado sin autorización:\n→ CREAR TICKET tipo 'acceso' inmediatamente.",
                'resumen' => 'Contraseña: loginhelp → ticket si falla. Pantallas: cerrar sesiones remotas. No carga: caché/reinstalar/web. Perfil modificado: ticket directo.',
                'visibilidad' => 'ambas', 'tags' => ['netflix', 'soporte', 'acceso', 'diagnostico'],
                'fuente' => 'soporte_playbook', 'prioridad' => 10, 'activo' => true,
            ],
            [
                'tipo' => 'soporte_escalado', 'categoria' => 'netflix',
                'clave' => 'soporte_escalado_netflix',
                'titulo' => 'Netflix: cuándo crear ticket vs. escalar',
                'contenido' => "RESOLVER DIRECTO (sin ticket):\n- Cliente olvidó contraseña y PUEDE recuperarla en netflix.com/loginhelp\n- Demasiadas pantallas que se resuelve cerrando sesiones remotas (netflix.com/account)\n\nCREAR TICKET (tipo según el caso):\n- La contraseña fue cambiada y el cliente NO la tocó → tipo: 'acceso' (posible cuenta comprometida)\n- No puede recuperar la cuenta por el email registrado → tipo: 'acceso'\n- Cuenta bloqueada o suspendida por Netflix → tipo: 'acceso'\n- Demasiadas pantallas persiste después de cerrar todas las sesiones → tipo: 'acceso'\n- Pantalla negra / no carga en todos los dispositivos → tipo: 'técnico'\n- Perfil del cliente eliminado sin autorización → tipo: 'acceso'\n\nESCALAR A HUMANO:\n- Cliente amenaza con cancelar el servicio\n- Sospecha de doble cobro o fraude\n- Cliente pide hablar directamente con un agente",
                'resumen' => 'Ticket para cuenta comprometida, bloqueada, sin recuperación. Directo solo para contraseña olvidada y pantallas resolubles.',
                'visibilidad' => 'ambas', 'tags' => ['netflix', 'escalado', 'ticket'],
                'fuente' => 'soporte_playbook', 'prioridad' => 11, 'activo' => true,
            ],
            [
                'tipo' => 'faq', 'categoria' => 'netflix',
                'clave' => 'faq_netflix_demasiadas_pantallas',
                'titulo' => 'Netflix: error de demasiadas pantallas',
                'contenido' => 'El error de "demasiadas pantallas" aparece cuando se supera el límite de dispositivos simultáneos del plan. Solución: ir a netflix.com → Cuenta → Administrar acceso y dispositivos → Cerrar sesión en todos los dispositivos. Si el problema persiste tras 30 minutos, abrir ticket de soporte.',
                'resumen' => 'Cerrar sesiones en netflix.com/account → esperar 30 min → ticket si persiste.',
                'visibilidad' => 'cliente', 'tags' => ['netflix', 'pantallas', 'faq', 'acceso'],
                'fuente' => 'soporte_playbook', 'prioridad' => 15, 'activo' => true,
            ],

            // ─── DISNEY+ ─────────────────────────────────────────────────────────
            [
                'tipo' => 'soporte_pasos', 'categoria' => 'disney_plus',
                'clave' => 'soporte_pasos_disney_plus',
                'titulo' => 'Diagnóstico paso a paso: Disney+',
                'contenido' => "PROBLEMA 1 — Contraseña incorrecta / no puedo entrar:\nPASO 1: Ir a disneyplus.com → Iniciar sesión → ¿Olvidaste la contraseña? con el email registrado.\n→ Si no recibe el email o el email no es reconocido: CREAR TICKET tipo 'acceso'.\nPASO 2: Si resetea y sigue sin acceso o aparece error de suscripción: CREAR TICKET tipo 'acceso'.\n\nPROBLEMA 2 — Error 'suscripción inactiva' o 'contenido no disponible':\nPASO 1: Pedir intentar cerrar sesión y volver a entrar.\nPASO 2: Si el error persiste en todos los dispositivos: puede indicar problema con la cuenta titular. CREAR TICKET tipo 'acceso'.\nNOTA: Algunos contenidos no están disponibles por restricción regional — esto es normal y no es un error de cuenta.\n\nPROBLEMA 3 — App no carga / error con código numérico:\nPASO 1: Limpiar caché, reiniciar app.\nPASO 2: Probar en navegador: disneyplus.com\nPASO 3: Si hay un código de error específico: pedir captura + CREAR TICKET tipo 'técnico'.\nPASO 4: Verificar si hay caída del servicio en disneyplus.com/status.\n\nPROBLEMA 4 — Perfil del cliente eliminado o no aparece:\n→ CREAR TICKET tipo 'acceso' inmediatamente.",
                'resumen' => 'Contraseña: loginhelp → ticket si falla. Suscripción inactiva: ticket. No carga: caché/web/código error. Perfil eliminado: ticket directo.',
                'visibilidad' => 'ambas', 'tags' => ['disney', 'soporte', 'acceso', 'diagnostico'],
                'fuente' => 'soporte_playbook', 'prioridad' => 10, 'activo' => true,
            ],
            [
                'tipo' => 'soporte_escalado', 'categoria' => 'disney_plus',
                'clave' => 'soporte_escalado_disney_plus',
                'titulo' => 'Disney+: cuándo crear ticket vs. escalar',
                'contenido' => "RESOLVER DIRECTO:\n- Cliente olvidó contraseña y PUEDE recuperarla en disneyplus.com\n- Contenido específico no disponible (restricción regional — normal, no es falla de cuenta)\n\nCREAR TICKET:\n- No puede recuperar la cuenta por email → tipo: 'acceso'\n- Error de suscripción inactiva persistente → tipo: 'acceso'\n- Código de error numérico en la app → tipo: 'técnico' (adjuntar captura)\n- Perfil eliminado sin autorización → tipo: 'acceso'\n\nESCALAR A HUMANO:\n- Cliente amenaza con cancelar o muy molesto\n- Sospecha de acceso no autorizado a la cuenta",
                'resumen' => 'Directo: contraseña olvidada, restricción regional. Ticket: email no reconocido, suscripción inactiva, perfil eliminado.',
                'visibilidad' => 'ambas', 'tags' => ['disney', 'escalado', 'ticket'],
                'fuente' => 'soporte_playbook', 'prioridad' => 11, 'activo' => true,
            ],

            // ─── MAX (HBO) ────────────────────────────────────────────────────────
            [
                'tipo' => 'soporte_pasos', 'categoria' => 'max',
                'clave' => 'soporte_pasos_max',
                'titulo' => 'Diagnóstico paso a paso: Max (HBO)',
                'contenido' => "PROBLEMA 1 — Contraseña incorrecta / no puedo entrar:\nPASO 1: Ir a max.com → Iniciar sesión → ¿Olvidaste tu contraseña? con el email registrado.\n→ Si no recibe el email o aparece error 'cuenta no encontrada': CREAR TICKET tipo 'acceso'.\nPASO 2: Si resetea y aparece 'suscripción vencida' o 'no autorizado': CREAR TICKET tipo 'acceso'.\n\nPROBLEMA 2 — Error 'no tienes acceso a este contenido':\nCaso A: En contenido específico → puede ser restricción del catálogo (normal). Probar con otro título.\nCaso B: En TODO el contenido → CREAR TICKET tipo 'acceso'.\n\nPROBLEMA 3 — App se cierra / no carga:\nPASO 1: Actualizar la app desde la tienda correspondiente.\nPASO 2: Limpiar caché y reiniciar el dispositivo.\nPASO 3: Probar en navegador web: max.com\n→ Si falla en todos los dispositivos: CREAR TICKET tipo 'técnico'.",
                'resumen' => 'Contraseña: max.com/forgot → ticket si falla. Acceso restringido en todo: ticket. No carga: actualizar/caché/web.',
                'visibilidad' => 'ambas', 'tags' => ['max', 'hbo', 'soporte', 'acceso', 'diagnostico'],
                'fuente' => 'soporte_playbook', 'prioridad' => 10, 'activo' => true,
            ],
            [
                'tipo' => 'soporte_escalado', 'categoria' => 'max',
                'clave' => 'soporte_escalado_max',
                'titulo' => 'Max (HBO): cuándo crear ticket vs. escalar',
                'contenido' => "RESOLVER DIRECTO:\n- Cliente olvidó contraseña y PUEDE recuperarla en max.com\n- Error en contenido específico (restricción del catálogo — normal)\n\nCREAR TICKET:\n- Email no reconocido o no llega recuperación → tipo: 'acceso'\n- Toda la cuenta aparece como no autorizada o suscripción vencida → tipo: 'acceso'\n- App falla en todos los dispositivos → tipo: 'técnico'\n\nESCALAR A HUMANO:\n- Cliente amenaza con cancelar\n- Sospecha de cuenta comprometida",
                'resumen' => 'Directo: contraseña olvidada, restricción de catálogo. Ticket: email no reconocido, sin autorización, falla total.',
                'visibilidad' => 'ambas', 'tags' => ['max', 'hbo', 'escalado', 'ticket'],
                'fuente' => 'soporte_playbook', 'prioridad' => 11, 'activo' => true,
            ],

            // ─── PARAMOUNT+ ───────────────────────────────────────────────────────
            [
                'tipo' => 'soporte_pasos', 'categoria' => 'paramount_plus',
                'clave' => 'soporte_pasos_paramount_plus',
                'titulo' => 'Diagnóstico paso a paso: Paramount+',
                'contenido' => "PROBLEMA 1 — Contraseña incorrecta / no puedo entrar:\nPASO 1: Ir a paramountplus.com → Iniciar sesión → ¿Olvidaste tu contraseña?\n→ Si no recibe email o email no reconocido: CREAR TICKET tipo 'acceso'.\nPASO 2: Si resetea y aparece 'cuenta no válida' o 'plan expirado': CREAR TICKET tipo 'acceso'.\n\nPROBLEMA 2 — App no carga / error al reproducir:\nPASO 1: Limpiar caché y reinstalar la app.\nPASO 2: Probar en navegador: paramountplus.com\n→ Si falla en todos lados con un código de error: captura + CREAR TICKET tipo 'técnico'.\n\nPROBLEMA 3 — Error 'cuenta no válida' o 'plan expirado':\n→ CREAR TICKET tipo 'acceso' inmediatamente (posible problema con la cuenta titular).",
                'resumen' => 'Contraseña: paramountplus.com/forgot → ticket si falla. No carga: caché/web. Cuenta no válida: ticket directo.',
                'visibilidad' => 'ambas', 'tags' => ['paramount', 'soporte', 'acceso', 'diagnostico'],
                'fuente' => 'soporte_playbook', 'prioridad' => 10, 'activo' => true,
            ],

            // ─── CRUNCHYROLL ──────────────────────────────────────────────────────
            [
                'tipo' => 'soporte_pasos', 'categoria' => 'crunchyroll',
                'clave' => 'soporte_pasos_crunchyroll',
                'titulo' => 'Diagnóstico paso a paso: Crunchyroll',
                'contenido' => "PROBLEMA 1 — Contraseña incorrecta / no puedo entrar:\nPASO 1: Ir a crunchyroll.com → Iniciar sesión → ¿Olvidaste la contraseña?\n→ Si no recibe email o email no reconocido: CREAR TICKET tipo 'acceso'.\n\nPROBLEMA 2 — Videos no cargan / calidad muy baja:\nPASO 1: Verificar que el cliente tiene buena conexión a internet (mínimo 5 Mbps para HD).\n→ Si la conexión es lenta: es problema del lado del cliente, no de la cuenta.\nPASO 2: Cambiar la calidad del video en el reproductor (icono de configuración).\nPASO 3: Limpiar caché de la app.\n→ Si NO carga nada con buena conexión: CREAR TICKET tipo 'técnico'.\n\nPROBLEMA 3 — Error 'Premium requerido' teniendo premium:\nPASO 1: Cerrar sesión completamente y volver a iniciar.\n→ Si persiste: CREAR TICKET tipo 'acceso' (problema de sincronización de membresía).\n\nPROBLEMA 4 — Múltiples dispositivos usando la cuenta:\n→ Recordar política: no compartir credenciales. Si hay acceso no autorizado: CREAR TICKET.",
                'resumen' => 'Contraseña: crunchyroll.com/forgot. Videos lentos: conexión del cliente. Premium no activo: cerrar sesión → ticket. Acceso no autorizado: ticket.',
                'visibilidad' => 'ambas', 'tags' => ['crunchyroll', 'soporte', 'acceso', 'diagnostico'],
                'fuente' => 'soporte_playbook', 'prioridad' => 10, 'activo' => true,
            ],

            // ─── FLUJO TV ────────────────────────────────────────────────────────
            [
                'tipo' => 'soporte_pasos', 'categoria' => 'flujo_tv',
                'clave' => 'soporte_pasos_flujo_tv',
                'titulo' => 'Diagnóstico paso a paso: Flujo TV',
                'contenido' => "PROBLEMA 1 — Contraseña incorrecta / no puedo entrar:\nPASO 1: Ir a flujo.com.ec → Cuenta → ¿Olvidaste tu contraseña?\n→ Si no recibe email o email no reconocido: CREAR TICKET tipo 'acceso'.\n\nPROBLEMA 2 — Canales no cargan / señal interrumpida:\nPASO 1: Verificar velocidad de internet del cliente (mínimo 10 Mbps recomendado para Flujo TV).\n→ Conexión lenta: problema del lado del cliente.\nPASO 2: Limpiar caché de la app, reiniciar.\nPASO 3: Si ciertos canales no están disponibles: puede ser mantenimiento o restricción regional temporal. Pedir intentar en 30 minutos.\n→ Si NINGÚN canal carga con buena conexión: CREAR TICKET tipo 'técnico'.\n\nPROBLEMA 3 — App no compatible / error de dispositivo:\nFlujo TV es compatible con: Smart TVs Android, Roku, Fire Stick, iOS, Android.\n→ Si el dispositivo no es compatible: informar al cliente (no es un error de cuenta).\n→ Si el dispositivo ES compatible pero falla: CREAR TICKET tipo 'técnico' indicando el modelo del dispositivo.",
                'resumen' => 'Contraseña: flujo.com.ec/forgot. Sin señal: verificar 10 Mbps → caché → ticket. Dispositivo incompatible: informar al cliente.',
                'visibilidad' => 'ambas', 'tags' => ['flujo', 'flujotv', 'soporte', 'acceso', 'diagnostico'],
                'fuente' => 'soporte_playbook', 'prioridad' => 10, 'activo' => true,
            ],

            // ─── SPOTIFY ─────────────────────────────────────────────────────────
            [
                'tipo' => 'soporte_pasos', 'categoria' => 'spotify',
                'clave' => 'soporte_pasos_spotify',
                'titulo' => 'Diagnóstico paso a paso: Spotify',
                'contenido' => "PROBLEMA 1 — Contraseña incorrecta / no puedo entrar:\nPASO 1: Ir a accounts.spotify.com → Olvidé mi contraseña.\nIMPORTANTE: Si el cliente usó Facebook, Apple o Google para registrarse, debe usar ESE método para entrar (no contraseña directa).\n→ Si no puede recuperar la cuenta por ningún método: CREAR TICKET tipo 'acceso'.\n\nPROBLEMA 2 — Plan Premium no activo (aparece versión gratuita / con anuncios):\nPASO 1: Cerrar sesión completamente y volver a iniciar en todos los dispositivos.\nPASO 2: Verificar en accounts.spotify.com → tu cuenta que el plan muestre 'Premium'.\n→ Si el plan sigue mostrando Gratuito: CREAR TICKET tipo 'acceso' (problema de activación de membresía).\n\nPROBLEMA 3 — Descargas no funcionan / música offline:\nPASO 1: Verificar que la descarga se completó (icono verde en la canción/playlist).\nPASO 2: Activar Modo sin conexión en Ajustes → Modo sin conexión.\nPASO 3: Si las descargas desaparecieron tras reinstalar la app: es normal, deben volver a descargarse.\n→ Si el problema persiste: CREAR TICKET tipo 'técnico'.\n\nPROBLEMA 4 — Cuenta usada desde otro dispositivo sin permiso:\nPASO 1: Ir a accounts.spotify.com → Cerrar sesión en todos los dispositivos.\n→ Si continúa el acceso no autorizado: CREAR TICKET tipo 'acceso'.",
                'resumen' => 'Contraseña: accounts.spotify.com (o Facebook/Apple/Google). Premium inactivo: cerrar sesión → ticket. Offline: verificar descarga. Acceso no autorizado: cerrar sesiones → ticket.',
                'visibilidad' => 'ambas', 'tags' => ['spotify', 'soporte', 'acceso', 'premium', 'diagnostico'],
                'fuente' => 'soporte_playbook', 'prioridad' => 10, 'activo' => true,
            ],

            // ─── PRIME VIDEO ─────────────────────────────────────────────────────
            [
                'tipo' => 'soporte_pasos', 'categoria' => 'prime_video',
                'clave' => 'soporte_pasos_prime_video',
                'titulo' => 'Diagnóstico paso a paso: Prime Video',
                'contenido' => "PROBLEMA 1 — Contraseña incorrecta / no puedo entrar:\nNOTA: Prime Video usa cuenta de Amazon. Recuperación en amazon.com → Mi cuenta → Contraseña olvidada.\n→ Si el email de Amazon no es reconocido o no llega el email: CREAR TICKET tipo 'acceso'.\nPASO 2: Si resetea y aparece 'no autorizado' o la suscripción no aparece activa: CREAR TICKET tipo 'acceso'.\n\nPROBLEMA 2 — Error 'no autorizado' o 'contenido no disponible en tu región':\nCaso A: En títulos específicos → restricción geográfica del título (normal, no es falla de cuenta).\nCaso B: En TODO el contenido o en la cuenta en general → CREAR TICKET tipo 'acceso'.\n\nPROBLEMA 3 — App no carga / video interrumpido:\nPASO 1: Limpiar caché, actualizar la app, reiniciar el dispositivo.\nPASO 2: Probar en navegador: primevideo.com\n→ Si falla en todos los dispositivos y hay un código de error: captura + CREAR TICKET tipo 'técnico'.\n\nPROBLEMA 4 — Perfil modificado o actividad no reconocida:\n→ CREAR TICKET tipo 'acceso' inmediatamente (posible acceso no autorizado a cuenta Amazon).",
                'resumen' => 'Contraseña: amazon.com/forgot (cuenta Amazon). Restricción regional en títulos: normal. Todo bloqueado: ticket. No carga: caché/web. Perfil modificado: ticket.',
                'visibilidad' => 'ambas', 'tags' => ['prime', 'amazon', 'soporte', 'acceso', 'diagnostico'],
                'fuente' => 'soporte_playbook', 'prioridad' => 10, 'activo' => true,
            ],

        ];

        foreach ($items as $item) {
            ChatMemoriaNegocio::updateOrCreate(
                [
                    'tipo'  => $item['tipo'],
                    'clave' => $item['clave'],
                ],
                $item
            );
        }

        ChatMemoriaNegocio::where('clave', 'guion_identificacion_lead')->delete();
    }
}
