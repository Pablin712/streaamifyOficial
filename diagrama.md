# 📋 Resumen Técnico Chat WhatsApp Streaamify

Este documento es para desarrolladores de producción que necesitan entender y mantener el sistema.

---

## 🧱 Arquitectura General

El sistema sigue un **patrón de capas completamente desacoplado**:

| Capa | Responsabilidad | Archivo Principal |
|---|---|---|
| 🔌 **Ingesta** | Recibe mensajes de WhatsApp, valida seguridad | `app/Http/Controllers/Chat/WhatsAppWebhookController.php:15` |
| 🧠 **Negocio** | Lógica core, deduplicación, asignación | `app/Services/Chat/WhatsAppHelpdeskService.php:19` |
| 📤 **Salida** | Envío de mensajes hacia WhatsApp | `app/Services/Chat/WhatsAppOutboundService.php:8` |
| 🗄️ **Persistencia** | Modelos normalizados independientes del canal | `app/Models/Conversacion.php`, `Mensaje.php`, `ChatContactoCanal.php` |
| 🖥️ **UI** | Panel operativo en tiempo real | `app/Livewire/Chat/WhatsAppHelpdesk.php` |

---

## ⚡ Flujo Mensaje INBOUND (Usuario -> Sistema)

```
WhatsApp → Evolution API → n8n Transformador → Webhook Laravel
```

### Paso a paso técnico:

1.  **Validación Seguridad** (`WhatsAppWebhookController.php:17-25`)
    - Token en header `X-Chat-Webhook-Token`
    - Comparación segura con `hash_equals()` (anti timing attack)
    - Si falla: 401 Unauthorized

2.  **Validación Schema** (`WhatsAppWebhookController.php:27-56`)
    - Laravel Validator con 22 campos permitidos
    - Campos opcionales para compatibilidad hacia atrás
    - Si falla: 422 con errores específicos

3.  **Ingesta Atómica** (`WhatsAppHelpdeskService.php:28-194`)
    - TODO el flujo corre dentro de una `DB::transaction()`
    - Si algo falla, ROLLBACK COMPLETO, nada se guarda a medias

4.  **Normalización**
    - Tipos de mensaje: `normalizeType()` → `texto`, `imagen`, `audio`, `documento`, `ubicacion`, `plantilla`, `sistema`
    - Teléfonos: `normalizePhone()` → remueve @s.whatsapp.net
    - Texto: `sanitizeText()` → trim + strip_tags

5.  **Resoluciones Automáticas**
    - `resolveCliente()`: Busca cliente existente por teléfono (usa LIKE con replace de símbolos)
    - `resolveChannel()`: Vincula automáticamente la instancia WhatsApp configurada

6.  **Contacto** (`ChatContactoCanal.php`)
    - `firstOrCreate()` por `canal + canal_user_id` (teléfono completo)
    - Actualiza automáticamente nombre, estado y metadata cada vez que escribe

7.  **Conversación** (`Conversacion.php`)
    - Busca primero conversación ABIERTA existente
    - Si no encuentra, busca última conversación CERRADA
    - Si ninguna, crea NUEVA con estado `nueva`

8.  **✅ Control de Duplicados** (`WhatsAppHelpdeskService.php:121-134`)
    - Busca por `external_message_id` de WhatsApp
    - Si existe, retorna 200 OK ignorando silenciosamente
    - Este es el punto MAS IMPORTANTE para evitar mensajes duplicados

9.  **Persistencia**
    - Crea `Mensaje` (genérico)
    - Crea `ChatMensajeCanal` (específico WhatsApp con IDs externos)
    - Incremento atómico de `mensajes_no_leidos`
    - Actualiza timestamps

10. **Evento**
    - Dispara `ChatMessageReceived` event
    - Usado por websockets, notificaciones y integraciones externas

---

## 🚀 Flujo Mensaje OUTBOUND (Operador -> Usuario)

```
Panel Livewire → WhatsAppHelpdeskService → n8n / Evolution API → WhatsApp
```

### Paso a paso:

1.  `sendOperatorMessage()` (`WhatsAppHelpdeskService.php:196-288`)
    - También corre dentro de transacción DB
    - Si es archivo: almacena en disco `public/chat/`
    - Crea registros `Mensaje` + `ChatMensajeCanal`

2.  `dispatchOutbound()` (`WhatsAppHelpdeskService.php:363-420`)
    - Fallback automático: primero intenta n8n, luego Evolution API directa
    - Timeout configurable: 20 segundos
    - Guarda respuesta completa y error en metadata del mensaje
    - Actualiza estado: `accepted` → `sent` / `failed`

3.  **Estado Conversación**
    - Cambia automáticamente a `atendiendo`
    - Asigna operador si no estaba asignado
    - Actualiza `ultima_actividad`

4.  Dispara evento `ChatMessageSent`

---

## 📦 Estados de Conversación

Valores posibles en campo `estado`:
```php
['nueva', 'abierto', 'asignado', 'atendiendo', 'pausado', 'en_espera', 'bot_activo', 'cerrado']
```

> ⚠️ **Importante**: Existen alias históricos que se mantienen por compatibilidad: `nuevo`, `abierta`, `cerrada`

---

## 🗄️ Estructura Base de Datos

| Tabla | Propósito Clave |
|---|---|
| `conversaciones` | Estado, asignación, contadores, timestamps |
| `mensajes` | Contenido normalizado de TODOS los mensajes |
| `chat_contacto_canal` | Contactos únicos por teléfono WhatsApp |
| `chat_mensaje_canal` | Trackeo 1:1 con IDs de WhatsApp |
| `chat_whatsapp_channels` | Instancias conectadas al sistema |

> ✅ **Diseño excelente**: Todo lo genérico esta en tablas comunes, todo lo específico de canal esta separado. El sistema esta listo para agregar Instagram, Telegram, etc. sin tocar el core.

---

## ⚙️ Puntos Clave para Producción

### 🔐 Seguridad
- Webhook NO requiere autenticación Laravel
- Token es el unico mecanismo de seguridad → **nunca exponerlo**
- Valores en `config/services.php` o en tabla `chat_settings`

### 🚨 Manejo de Errores
- Todos los errores son reportados a Laravel Log
- Webhook siempre retorna JSON estructurado
- Nunca retorna stack traces al cliente externo

### 📊 Monitorización
- Para debuggear: revisar `payload` crudo guardado en `chat_mensaje_canal`
- Mensajes fallidos tienen `external_status = 'failed'` y `error_message`

### 🔄 Retries y Idempotencia
- El webhook es **100% idempotente**: se puede llamar N veces con el mismo `external_message_id` y solo se procesa una vez
- n8n ya maneja retries automáticos

---

## 🔧 Puntos de Configuración

| Clave | Ubicación | Descripción |
|---|---|---|
| `chat_webhook_token` | `chat_settings` o `config/services.n8n` | Token para validar webhook entrada |
| `n8n_webhook_url` | `chat_settings` | Webhook salida para mensajes |
| `evoapi.base_url` | `config/services.evoapi` | URL Evolution API fallback |
| `evoapi.timeout_seconds` | `config/services.evoapi` | Timeout para envíos |

---

## 🐛 Tips para Debuggear

1.  **Mensaje no llega**:
    - Revisar logs Laravel
    - Verificar token webhook
    - Probar webhook con `test_real_webhook.php`

2.  **Mensaje duplicado**:
    - Asegurarse que n8n envía `external_message_id` correcto
    - Revisar columna `external_message_id` en `chat_mensaje_canal`

3.  **Mensaje no se envía**:
    - Revisar `error_message` en tabla `mensajes`
    - Verificar estado de instancia WhatsApp
    - Probar Evolution API directamente

4.  **Conversación no se abre**:
    - Buscar conversaciones cerradas para ese número
    - Revisar `OPEN_STATES` constante en `WhatsAppHelpdeskService.php:21`

---

## 📌 Diagrama Simplificado

```
┌─────────────────┐     ┌─────────────────────┐     ┌────────────────────────┐
│  WhatsApp User  │────▶│ Evolution API / n8n │────▶│ WhatsAppWebhookController │
└─────────────────┘     └─────────────────────┘     └────────────────────────┘
                                                               │
                                                               ▼
                                                    ┌─────────────────────┐
                                                    │ WhatsAppHelpdeskService │
                                                    └─────────────────────┘
                                                               │
                    ┌──────────────────┬──────────────────┬──────────────────┐
                    ▼                  ▼                  ▼                  ▼
            ┌──────────────┐   ┌────────────────┐   ┌──────────────┐   ┌──────────┐
            │ Conversacion │   │ ChatContactoCanal │   │ ChatMensajeCanal │   │ Mensaje  │
            └──────────────┘   └────────────────┘   └──────────────┘   └──────────┘
                                                               │
                                                               ▼
                                                    ┌─────────────────────┐
                                                    │ WhatsAppOutboundService │
                                                    └─────────────────────┘
                                                               │
                                                               ▼
                                                    ┌─────────────────────┐
                                                    │ n8n / Evolution API │
                                                    └─────────────────────┘
                                                               │
                                                               ▼
                                                    ┌─────────────────┐
                                                    │  WhatsApp User  │
                                                    └─────────────────┘
```
