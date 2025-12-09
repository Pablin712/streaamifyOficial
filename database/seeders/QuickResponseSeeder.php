<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QuickResponse;

class QuickResponseSeeder extends Seeder
{
    public function run(): void
    {
        $responses = [
            // ===== RESPUESTAS PARA EMPLEADOS =====
            [
                'comando' => 'bancos',
                'titulo' => 'Métodos de Pago Disponibles',
                'contenido' => "💳 **MÉTODOS DE PAGO ACEPTADOS**\n\n📱 **Transferencias**\n• Banco Pichincha: 2100123456 (Juan Pérez)\n• Banco Guayaquil: 0012345678 (Streamify CIA)\n\n💵 **Efectivo**\n• Oficina: Av. Principal #123, Quito\n• Horario: Lun-Vie 9AM-6PM\n\n🌐 **PayPal**\n• pagos@streamify.com\n\n⚡ **Criptomonedas**\n• USDT (TRC20): TXxxxxxxxxx\n• BTC: 1BvBMxxxxxx",
                'tipo' => 'empleado',
                'activo' => true,
                'orden' => 1,
                'tags' => json_encode(['pago', 'transferencia', 'banco', 'efectivo', 'paypal']),
            ],
            [
                'comando' => 'precios',
                'titulo' => 'Lista de Precios',
                'contenido' => "💰 **PRECIOS ACTUALIZADOS 2025**\n\n📺 **STREAMING**\n• Netflix Premium: $25/mes\n• Spotify Premium: $15/mes\n• Disney+: $20/mes\n• HBO Max: $18/mes\n• Amazon Prime: $22/mes\n\n📦 **COMBOS**\n• Combo Básico (Netflix + Spotify): $35/mes\n• Combo Premium (3 servicios): $50/mes\n• Combo Familiar (5 servicios): $75/mes\n\n⏰ **PLANES ANUALES**\n• 10% descuento pagando 6 meses\n• 20% descuento pagando 12 meses",
                'tipo' => 'empleado',
                'activo' => true,
                'orden' => 2,
                'tags' => json_encode(['precios', 'costo', 'precio', 'planes', 'combo']),
            ],
            [
                'comando' => 'horario',
                'titulo' => 'Horario de Atención',
                'contenido' => "🕐 **HORARIOS DE ATENCIÓN**\n\n📞 **Soporte Telefónico**\n• Lun-Vie: 8:00 AM - 10:00 PM\n• Sáb-Dom: 9:00 AM - 6:00 PM\n\n💬 **Chat en Línea**\n• Disponible 24/7\n• Respuesta promedio: 5 minutos\n\n🏢 **Oficina Física**\n• Lun-Vie: 9:00 AM - 6:00 PM\n• Dirección: Av. Principal #123, Quito",
                'tipo' => 'ambos',
                'activo' => true,
                'orden' => 3,
                'tags' => json_encode(['horario', 'atención', 'soporte', 'oficina']),
            ],
            [
                'comando' => 'garantia',
                'titulo' => 'Política de Garantía',
                'contenido' => "✅ **GARANTÍA STREAMIFY**\n\n🔄 **Reemplazo Inmediato**\n• Si el servicio no funciona, reemplazo en 24h\n• Sin costo adicional\n\n💯 **Garantía de Funcionamiento**\n• 99% uptime garantizado\n• Reembolso si no funciona en 48h\n\n🛡️ **Soporte Técnico**\n• Ayuda para configuración\n• Solución de problemas incluida\n\n⏰ **Vigencia**\n• Durante todo el período pagado",
                'tipo' => 'ambos',
                'activo' => true,
                'orden' => 4,
                'tags' => json_encode(['garantía', 'reembolso', 'devolución', 'soporte']),
            ],
            [
                'comando' => 'configurar',
                'titulo' => 'Guía de Configuración',
                'contenido' => "⚙️ **CONFIGURAR TU SERVICIO**\n\n📺 **Smart TV**\n1. Abre la app del servicio\n2. Ingresa email y contraseña proporcionados\n3. Selecciona tu perfil\n\n📱 **Móvil/Tablet**\n1. Descarga la app oficial\n2. Inicia sesión con las credenciales\n3. Listo para usar\n\n💻 **PC/Mac**\n1. Ve al sitio web oficial\n2. Inicia sesión\n3. Disfruta desde el navegador\n\n⚠️ **IMPORTANTE**\n• No cambies la contraseña\n• Usa solo tu perfil asignado",
                'tipo' => 'ambos',
                'activo' => true,
                'orden' => 5,
                'tags' => json_encode(['configurar', 'ayuda', 'setup', 'instalar']),
            ],

            // ===== RESPUESTAS PARA CLIENTES =====
            [
                'comando' => 'pagos',
                'titulo' => 'Cómo Pagar',
                'contenido' => "💳 **REALIZA TU PAGO**\n\n1️⃣ **Transferencia Bancaria**\n• Envía a: 2100123456 (Banco Pichincha)\n• Beneficiario: Juan Pérez\n\n2️⃣ **PayPal**\n• Envía a: pagos@streamify.com\n\n3️⃣ **Efectivo**\n• Visítanos: Av. Principal #123, Quito\n\n📸 **IMPORTANTE**\n• Envía comprobante por WhatsApp\n• Incluye tu nombre completo\n• Activación en 1-2 horas",
                'tipo' => 'cliente',
                'activo' => true,
                'orden' => 10,
                'tags' => json_encode(['pago', 'transferencia', 'comprobante']),
            ],
            [
                'comando' => 'servicios',
                'titulo' => 'Servicios Disponibles',
                'contenido' => "🎬 **SERVICIOS DISPONIBLES**\n\n📺 **Streaming Video**\n• Netflix\n• Disney+\n• HBO Max\n• Amazon Prime\n• Star+\n\n🎵 **Streaming Música**\n• Spotify\n• Apple Music\n• YouTube Premium\n\n🎮 **Gaming**\n• Xbox Game Pass\n• PlayStation Plus\n\n📦 **Combos Disponibles**\n• Escribe /precios para ver costos",
                'tipo' => 'cliente',
                'activo' => true,
                'orden' => 11,
                'tags' => json_encode(['servicios', 'plataformas', 'disponibles']),
            ],
            [
                'comando' => 'renovar',
                'titulo' => 'Cómo Renovar tu Servicio',
                'contenido' => "🔄 **RENOVAR TU SUSCRIPCIÓN**\n\n1️⃣ **Contacta con nosotros**\n• WhatsApp: +593 99 123 4567\n• Chat web: www.streamify.com\n\n2️⃣ **Indica**\n• Tu nombre completo\n• Servicio a renovar\n• Duración deseada (1, 3, 6, 12 meses)\n\n3️⃣ **Realiza el pago**\n• Usa /pagos para ver métodos\n\n4️⃣ **Envía comprobante**\n• Renovación automática en 1-2h\n\n💡 **DESCUENTOS**\n• 6 meses: 10% OFF\n• 12 meses: 20% OFF",
                'tipo' => 'cliente',
                'activo' => true,
                'orden' => 12,
                'tags' => json_encode(['renovar', 'renovación', 'extender']),
            ],
            [
                'comando' => 'problema',
                'titulo' => 'Reportar un Problema',
                'contenido' => "🔧 **SOPORTE TÉCNICO**\n\n⚠️ **Reporta tu problema**\n\n1️⃣ **Envía por WhatsApp**\n+593 99 123 4567\n\n2️⃣ **Incluye**\n• Nombre completo\n• Servicio afectado\n• Descripción del problema\n• Capturas de pantalla (opcional)\n\n⏰ **Tiempo de respuesta**\n• Problemas urgentes: 30 min\n• Consultas generales: 1-2 horas\n\n💬 **Chat 24/7**\nwww.streamify.com/chat",
                'tipo' => 'cliente',
                'activo' => true,
                'orden' => 13,
                'tags' => json_encode(['problema', 'error', 'ayuda', 'soporte']),
            ],
            [
                'comando' => 'contacto',
                'titulo' => 'Información de Contacto',
                'contenido' => "📞 **CONTÁCTANOS**\n\n💬 **WhatsApp**\n+593 99 123 4567\n\n📧 **Email**\nsoporte@streamify.com\n\n🌐 **Web**\nwww.streamify.com\n\n📱 **Redes Sociales**\n• Facebook: @StreamifyEC\n• Instagram: @streamify_ec\n• TikTok: @streamifyecuador\n\n🏢 **Oficina**\nAv. Principal #123, Quito\nLun-Vie: 9AM-6PM",
                'tipo' => 'ambos',
                'activo' => true,
                'orden' => 14,
                'tags' => json_encode(['contacto', 'whatsapp', 'teléfono', 'email']),
            ],

            // ===== RESPUESTAS INTERNAS EMPLEADOS =====
            [
                'comando' => 'politicas',
                'titulo' => 'Políticas Internas',
                'contenido' => "📋 **POLÍTICAS STREAMIFY**\n\n✅ **Atención al Cliente**\n• Responder en máx 5 minutos\n• Siempre ser cortés y profesional\n• Confirmar recepción de pagos\n\n⚠️ **Manejo de Cuentas**\n• NUNCA compartir contraseñas maestras\n• Solo asignar perfiles específicos\n• Verificar pagos antes de activar\n\n💰 **Cobros**\n• Confirmar pago recibido\n• Registrar en sistema antes de activar\n• No dar servicio sin pago confirmado\n\n🔒 **Seguridad**\n• Cambiar contraseñas cada 30 días\n• No dar acceso a cuentas sin autorización",
                'tipo' => 'empleado',
                'activo' => true,
                'orden' => 20,
                'tags' => json_encode(['políticas', 'reglas', 'normas', 'interno']),
            ],
            [
                'comando' => 'comisiones',
                'titulo' => 'Sistema de Comisiones',
                'contenido' => "💵 **COMISIONES EMPLEADOS**\n\n🎯 **Por Venta**\n• Venta individual: 10%\n• Combo 3 servicios: 15%\n• Plan anual: 20%\n\n📊 **Metas Mensuales**\n• 0-20 ventas: 10%\n• 21-50 ventas: 12%\n• 51+ ventas: 15%\n\n🏆 **Bonos Adicionales**\n• Top vendedor del mes: $100\n• Mayor renovaciones: $50\n• Mejor calificación cliente: $30\n\n💰 **Pago de Comisiones**\n• Cada 15 del mes\n• Transferencia bancaria",
                'tipo' => 'empleado',
                'activo' => true,
                'orden' => 21,
                'tags' => json_encode(['comisiones', 'ventas', 'bonos', 'sueldo']),
            ],
        ];

        foreach ($responses as $response) {
            QuickResponse::create($response);
        }
    }
}
