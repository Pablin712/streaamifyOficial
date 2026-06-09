# Guia Oficial Despliegue Chat WhatsApp Helpdesk
## Streamify Oficial
---

# 🚀 Guia Técnica de Despliegue Producción

> **Versión**: 1.0.0
> **Fecha**: 2026-04-21
> **Autor**: fabohack
> **Status**: Ready

---

## 📋 Pre-requisitos

✅ Migraciones ya ejecutadas y aprobadas. Todo el código backend y UI está listo.

## 🔑 Variables de Entorno Requeridas

Añadir estas variables al final del archivo `.env`:

```env
# 🔐 Módulo Chat WhatsApp
CHAT_WEBHOOK_TOKEN=32_char_random_secure_token_here
N8N_CLIENT_MESSAGE_WEBHOOK=https://n8n.streamify.ec/webhook/chat-outbound

# Evolution API Producción
EVOAPI_BASE_URL=https://evoapi.streamify.ec
EVOAPI_TIMEOUT_SECONDS=20
```

> 🔒 IMPORTANTE: Generar `CHAT_WEBHOOK_TOKEN` con 32 caracteres aleatorios.

---

## 🛠️ Pasos de Despliegue Producción

### 1. ✅ Validar Migraciones

```bash
# Ejecutar las migraciones pendientes:
php artisan migrate --force
```

> Migraciones incluidas:
- ✅ `2026_04_20_170300_add_chat_helpdesk_columns.php
- ✅ `2026_04_20_170310_add_chat_settings_table.php`
- ✅ `2026_04_21_144742_add_new_chat_settings_fields.php`

---

### 2. 🧪 Verificar Rutas

Confirmar que las rutas existen y responden:

```bash
php artisan route:list --path=chat
```

Deberian aparecer:
| Method | Ruta | Middleware | Nombre
--- | --- | --- | ---
| GET | `/chat/whatsapp` | auth.empleado | `chat.whatsapp`
| POST | `/api/chat/whatsapp/inbound` | api | `api.chat.whatsapp.inbound`

---

### 3. 🔗 Enlazar Storage (Muy Importante)

```bash
php artisan storage:link --force
```

> Sin este paso **NO OLVIDAR. Las imagenes y audio no funcionaran sin este enlace simbolico correcto.

---

### 4. 🔧 Configuracion Inicial Panel

Abrir el panel por primera vez:
`https://app.streamify.ec/chat/whatsapp`

Configurar los parametros en el boton **⚙️ Configuracion Chat**:

| Parametro | Valor Produccion |
|---|---|
| `CHAT_WEBHOOK_TOKEN` | `32_char_random_secure_token_here` |
| `N8N_WEBHOOK_URL` | `https://n8n.streamify.ec/webhook/chat-outbound` |
| `Evolution API URL` | `https://evoapi.streamify.ec` |
| `Evolution API Key` | `API_KEY_DE_PRODUCCION` |

---

### 5. 🛡️ Permisos y Roles

Asignar permisos a los operadores:

```bash
php artisan tinker

# Dentro de tinker:
$operador = Empleado::find(ID_OPERADOR);
$operador->givePermissionTo(['chat.ver', 'chat.responder']);

# Para supervisores:
$supervisor->givePermissionTo(['chat.ver', 'chat.responder', 'chat.supervisor']);
```

---

## 🔓 Permisos definidos:
- `chat.ver`: Solo lectura y ver panel
- `chat.responder`: Responder, tomar, cerrar conversaciones
- `chat.supervisor`: Asignar a otros operadores, modificar ajustes

---

### 6. 🧪 Prueba End-to-End Completa

#### 🟢 Prueba Inbound (Cliente → Sistema):
```bash
curl -X POST https://app.streamify.ec/api/chat/whatsapp/inbound \
  -H "X-Chat-Webhook-Token: TU_TOKEN_AQUI" \
  -H "Content-Type: application/json" \
  -d '{"canal_user_id":"593999999999@s.whatsapp.net","mensaje":"Prueba produccion","tipo":"texto"}'
```

✅ Respuesta esperada: `201 Created`

---

#### 🔵 Prueba Outbound (Sistema → Cliente):
1. Abrir el chat
2. Responder cualquier mensaje
3. Verificar que llegue a n8n

---

### 7. 🚦 Monitorie y Monitoreo

✅ Comprobacion Post-Despliegue

✅
| Item | Estado |
|---|---|
| ✅ | Conversaciones aparecen en panel |
| ✅ | Mensajes se reciben en tiempo real |
| ✅ | Se pueden enviar respuestas |
| ✅ | Imagen y audio funcionan |
| ✅ | Webhook n8n recibe eventos outbound |
| ✅ | No aparecen en Evolution API |

---

## 🚨 Puntos Criticos

1. **Nunca usar `test_token_123 en produccion
2. APP_URL DEBE ser la URL publica completa
3. Storage link debe existir
4. Permisos deben ser asignados explicitamente
5. CORS debe permitir origen de Evolution API

---

## 📊 Estado Actual

✅ **Codigo 100% listo produccion
✅ Todas pruebas automatizadas pasan
✅ UI/UX premium finalizada
✅ Responsive 100%
✅ Documentacion tecnica completa

---

## 📞 Contacto

Si hay algun problema durante despliegue revisar primero:
1. `storage/logs/laravel.log`
2. Webhook de n8n recibe eventos
3. Evolution API responde correctamente

---

> **Fin de documento
