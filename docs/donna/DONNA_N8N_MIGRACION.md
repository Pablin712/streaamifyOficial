# Donna Personal — Migración del flujo n8n a APIs Streamify

> Guía práctica para actualizar el flujo actual "Donna personal Pablo" a la arquitectura multi-cliente con APIs de Laravel.

**Base URL producción:** `https://streamify.aaronsoft.es/api`  
**Header obligatorio en todos los HTTP Request:** `X-Donna-Key: {DONNA_API_KEY}`

---

## Resumen de cambios

| Nodo actual | Acción |
|---|---|
| `Telegram Trigger` | ✅ Mantener sin cambios |
| `Switch` (audio/texto) | 🔁 Mover — va DESPUÉS de validar el contexto |
| `Get a file` | ✅ Mantener sin cambios |
| `Transcribe a recording` | ✅ Mantener sin cambios |
| `Edit Fields` (set text) | ✅ Mantener sin cambios |
| `Simple Memory` | ✏️ Modificar — sessionKey dinámico |
| `DeepSeek Chat Model` | ✅ Mantener sin cambios |
| `AI Agent` | ✏️ Modificar — system_message dinámico |
| `Mensaje de espera` | ✏️ Modificar — chatId dinámico |
| `Code in JavaScript` | ✅ Mantener sin cambios |
| `Enviar mensaje` | ✏️ Modificar — chatId dinámico |
| `Edit Fields1` (error fallback) | ✅ Mantener sin cambios |
| `Date & Time` | ✅ Mantener sin cambios |
| `Get many events` | ❌ Eliminar — reemplazar con HTTP Tool |
| `Create an event` | ❌ Eliminar — reemplazar con HTTP Tool |
| `Update an event` | ❌ Eliminar — reemplazar con HTTP Tool |
| `Delete an event` | ❌ Eliminar — reemplazar con HTTP Tool |
| `Get Tareas` | ❌ Eliminar — reemplazar con HTTP Tool |
| `Crear o editar una tarea` | ❌ Eliminar — reemplazar con HTTP Tool |
| `Update row` | ❌ Eliminar — reemplazar con HTTP Tool |
| `Schedule Trigger` (disabled) | ❌ Eliminar — ya no aplica |
| `Edit Fields2` (auto agenda) | ❌ Eliminar — ya no aplica |

**Nodos nuevos a agregar: 15**

---

## Paso 1 — Variables de entorno en n8n

En n8n → Settings → Environment Variables, agrega:

| Variable | Valor local | Valor producción |
|---|---|---|
| `STREAMIFY_BASE_URL` | `http://localhost` | `https://streamify.aaronsoft.es` |
| `DONNA_API_KEY` | `donna-secret-key-cambiar-en-produccion` | (clave real del servidor) |

Usarlas en nodos como `{{ $env.STREAMIFY_BASE_URL }}` y `{{ $env.DONNA_API_KEY }}`.

---

## Paso 2 — Flujo nuevo completo (diagrama)

```
[Telegram Trigger]
       │
       ▼
[Code: Normalizar mensaje]           ← NUEVO
       │
       ▼
[IF: ¿Es código de activación?]      ← NUEVO
       │
  SÍ  │  NO
  ▼   │   ▼
[HTTP POST register-telegram]  [HTTP GET context]   ← NUEVOS
  │                                  │
[IF: registered]              [IF: allowed]          ← NUEVOS
  │                                  │
[Telegram OK/Error]          NO ─► [Telegram Bloqueado]  ← NUEVO
                              │
                             SÍ
                              │
                              ▼
                      [Set: Guardar contexto]        ← NUEVO
                              │
                   ┌──────────┴──────────┐
                   │                     │
              [Switch audio/texto]   (ya existía, mover aquí)
                   │
         ┌─────────┴─────────┐
       audio               texto
         │                   │
  [Get a file]         [Edit Fields]    ← sin cambios
  [Transcribe]
         └─────────┬─────────┘
                   │
                   ▼
          [Pensando... → Telegram]      ← modificar chatId
                   │
                   ▼
             [AI Agent]                 ← modificar system_message + tools
                   │
                   ▼
          [Code JS format HTML]
                   │
                   ▼
          [Enviar mensaje]              ← modificar chatId
                   │
                   ▼
          [HTTP POST respond]           ← NUEVO
```

---

## Paso 3 — Nodo NUEVO: Code — Normalizar mensaje

**Tipo:** `Code`  
**Nombre:** `Normalizar mensaje`  
**Conectar:** Salida de `Telegram Trigger` → entrada de este nodo

**Código JavaScript:**

```javascript
const message = $input.item.json.message;

const chatId = String(message?.chat?.id || '');
const text = message?.text || '';
const voiceFileId = message?.voice?.file_id || null;
const username = message?.from?.username || null;
const name = [message?.from?.first_name, message?.from?.last_name]
  .filter(Boolean).join(' ') || 'Usuario';

const isActivationCode = /^[A-Z0-9]{6}$/.test(text.trim().toUpperCase());

return [{
  json: {
    telegram_chat_id: chatId,
    text: text,
    voice_file_id: voiceFileId,
    telegram_username: username,
    telegram_name: name,
    activation_code: isActivationCode ? text.trim().toUpperCase() : null,
    is_activation_code: isActivationCode,
    is_audio: !!voiceFileId,
    is_text: !!text && !isActivationCode,
  }
}];
```

---

## Paso 4 — Nodo NUEVO: IF — ¿Es código de activación?

**Tipo:** `IF`  
**Nombre:** `¿Es código?`  
**Conectar:** Salida de `Normalizar mensaje`

**Condición:**

```
Campo:      {{ $json.is_activation_code }}
Operación:  is true
```

**Salidas:**
- `true` → rama de registro (paso 5)
- `false` → rama normal (paso 7)

---

## Paso 5 — Nodo NUEVO: HTTP Request — Registrar Telegram

**Tipo:** `HTTP Request`  
**Nombre:** `POST Register Telegram`  
**Conectar:** Salida `true` de `¿Es código?`

| Campo | Valor |
|---|---|
| Método | `POST` |
| URL | `{{ $env.STREAMIFY_BASE_URL }}/api/donna/register-telegram` |

**Headers:**
```
X-Donna-Key    →  {{ $env.DONNA_API_KEY }}
Content-Type   →  application/json
Accept         →  application/json
```

**Body (JSON):**
```json
{
  "code": "{{ $('Normalizar mensaje').item.json.activation_code }}",
  "telegram_chat_id": "{{ $('Normalizar mensaje').item.json.telegram_chat_id }}",
  "telegram_username": "{{ $('Normalizar mensaje').item.json.telegram_username }}",
  "telegram_name": "{{ $('Normalizar mensaje').item.json.telegram_name }}"
}
```

**En caso de error HTTP:** activar "Continue on Fail" = true

---

## Paso 6 — Nodo NUEVO: IF — ¿Registro exitoso?

**Tipo:** `IF`  
**Nombre:** `¿Registro exitoso?`

**Condición:**
```
Campo:      {{ $json.success }}
Operación:  is true
```

**Salida true → Telegram: mensaje de bienvenida**

```
Chat ID:  {{ $('Normalizar mensaje').item.json.telegram_chat_id }}
Texto:    {{ $json.message }}
```

**Salida false → Telegram: mensaje de error**

```
Chat ID:  {{ $('Normalizar mensaje').item.json.telegram_chat_id }}
Texto:    {{ $json.message }}
```

> Ambas salidas terminan el flujo (no conectan a nada más).

---

## Paso 7 — Nodo NUEVO: HTTP Request — GET Donna Context

**Tipo:** `HTTP Request`  
**Nombre:** `GET Donna Context`  
**Conectar:** Salida `false` de `¿Es código?`

| Campo | Valor |
|---|---|
| Método | `GET` |
| URL | `{{ $env.STREAMIFY_BASE_URL }}/api/donna/context` |

**Query Parameters:**
```
telegram_chat_id  →  {{ $('Normalizar mensaje').item.json.telegram_chat_id }}
channel_type      →  telegram
service_type      →  personal
```

**Headers:**
```
X-Donna-Key  →  {{ $env.DONNA_API_KEY }}
Accept       →  application/json
```

**Continue on Fail:** true

---

## Paso 8 — Nodo NUEVO: IF — ¿Permitido?

**Tipo:** `IF`  
**Nombre:** `¿Donna permitida?`

**Condición:**
```
Campo:      {{ $json.allowed }}
Operación:  is true
```

**Salida false → Nodo NUEVO: Telegram Bloqueado**

```
Chat ID:  {{ $('Normalizar mensaje').item.json.telegram_chat_id }}
Texto:    {{ $json.message }}
```

> Si `$json.plan.payment_url` existe, agregar al mensaje: `\nRenueva aquí: {{ $json.plan.payment_url }}`

---

## Paso 9 — Nodo NUEVO: Set — Guardar contexto

**Tipo:** `Set`  
**Nombre:** `Guardar contexto`  
**Conectar:** Salida `true` de `¿Donna permitida?`

| Variable | Expresión |
|---|---|
| `chat_id` | `{{ $('Normalizar mensaje').item.json.telegram_chat_id }}` |
| `client_id` | `{{ $('GET Donna Context').item.json.client.id }}` |
| `subscription_id` | `{{ $('GET Donna Context').item.json.service.id }}` |
| `channel_id` | `{{ $('GET Donna Context').item.json.channel.id }}` |
| `session_key` | `{{ $('GET Donna Context').item.json.memory.session_key }}` |
| `system_message` | `{{ $('GET Donna Context').item.json.agent.system_message }}` |
| `calendar_enabled` | `{{ $('GET Donna Context').item.json.tools.calendar.create_event }}` |
| `sheets_enabled` | `{{ $('GET Donna Context').item.json.tools.sheets.get_tasks }}` |
| `is_audio` | `{{ $('Normalizar mensaje').item.json.is_audio }}` |
| `voice_file_id` | `{{ $('Normalizar mensaje').item.json.voice_file_id }}` |
| `text` | `{{ $('Normalizar mensaje').item.json.text }}` |

---

## Paso 10 — Mover el Switch audio/texto

El `Switch` original sigue igual internamente pero ahora se conecta después de `Guardar contexto`.

**Condición audio** (sin cambios):
```
Campo:      {{ $('Telegram Trigger').item.json.message.voice.file_id }}
Operación:  exists
```

**Condición texto** (sin cambios):
```
Campo:      {{ $('Telegram Trigger').item.json.message.text }}
Operación:  exists
```

---

## Paso 11 — Modificar: Mensaje de espera

**Nodo:** `Mensaje de espera` (ya existe)

Cambiar solo el `chatId`:

```
Antes:  6199654595           (hardcodeado)
Ahora:  {{ $('Guardar contexto').item.json.chat_id }}
```

Texto: `Pensando... 🤔` (sin cambios)

---

## Paso 12 — Modificar: Simple Memory

**Nodo:** `Simple Memory` (ya existe)

Cambiar solo el `Session Key`:

```
Session ID type: customKey
Session key:

Antes:  6199654595
Ahora:  {{ $('Guardar contexto').item.json.session_key }}
```

---

## Paso 13 — Modificar: AI Agent

**Nodo:** `AI Agent` (ya existe)

### System message — cambiar a dinámico

```
Antes:  Prompt hardcodeado de Pablo
Ahora:  {{ $('Guardar contexto').item.json.system_message }}
```

### Eliminar estas tools conectadas al agente

- ❌ `Get many events` (Google Calendar nativo)
- ❌ `Create an event` (Google Calendar nativo)
- ❌ `Update an event` (Google Calendar nativo)
- ❌ `Delete an event` (Google Calendar nativo)
- ❌ `Get Tareas` (Google Sheets nativo)
- ❌ `Crear o editar una tarea` (Google Sheets nativo)
- ❌ `Update row` (Google Sheets nativo)

### Agregar las nuevas HTTP Tool nodes (ver pasos 14-22)

- ✅ `Date & Time` — mantener conectado al agente

---

## Paso 14 — Tool NUEVA: donna_calendar_list_events

**Tipo:** `HTTP Request Tool`  
**Nombre:** `donna_calendar_list_events`

**Description (para el AI Agent):**
```
Consulta eventos del Google Calendar del cliente dentro de un rango de fechas.
Úsala para revisar agenda, disponibilidad o eventos antes de crear, modificar o eliminar citas.
Envía time_min y time_max en formato ISO 8601 con zona horaria América/Guayaquil.
```

| Campo | Valor |
|---|---|
| Método | `POST` |
| URL | `{{ $env.STREAMIFY_BASE_URL }}/api/donna/tools/calendar/list-events` |

**Headers:**
```
X-Donna-Key   →  {{ $env.DONNA_API_KEY }}
Content-Type  →  application/json
```

**Body (JSON):**
```json
{
  "client_id":      "{{ $('Guardar contexto').item.json.client_id }}",
  "service_id":     "{{ $('Guardar contexto').item.json.subscription_id }}",
  "channel_id":     "{{ $('Guardar contexto').item.json.channel_id }}",
  "time_min":       "={{ $fromAI('time_min', 'Inicio del rango en ISO 8601 con timezone America/Guayaquil', 'string') }}",
  "time_max":       "={{ $fromAI('time_max', 'Fin del rango en ISO 8601 con timezone America/Guayaquil', 'string') }}",
  "max_results":    20
}
```

---

## Paso 15 — Tool NUEVA: donna_calendar_freebusy

**Tipo:** `HTTP Request Tool`  
**Nombre:** `donna_calendar_freebusy`

**Description:**
```
Consulta bloques libres y ocupados del calendario del cliente.
Úsala antes de confirmar una cita o cuando el usuario pregunte si está disponible en cierto rango.
```

| Campo | Valor |
|---|---|
| Método | `POST` |
| URL | `{{ $env.STREAMIFY_BASE_URL }}/api/donna/tools/calendar/freebusy` |

**Headers:** (igual que arriba)

**Body:**
```json
{
  "client_id":   "{{ $('Guardar contexto').item.json.client_id }}",
  "service_id":  "{{ $('Guardar contexto').item.json.subscription_id }}",
  "channel_id":  "{{ $('Guardar contexto').item.json.channel_id }}",
  "time_min":    "={{ $fromAI('time_min', 'Inicio del rango ISO 8601', 'string') }}",
  "time_max":    "={{ $fromAI('time_max', 'Fin del rango ISO 8601', 'string') }}",
  "timezone":    "America/Guayaquil"
}
```

---

## Paso 16 — Tool NUEVA: donna_calendar_create_event

**Tipo:** `HTTP Request Tool`  
**Nombre:** `donna_calendar_create_event`

**Description:**
```
Crea un evento en Google Calendar del cliente.
Úsala cuando el usuario quiera agendar una reunión, cita, recordatorio o actividad.
Antes de agendar, verifica disponibilidad con donna_calendar_freebusy.
Nunca crees eventos sin summary, start y end.
Si no hay hora final, usa 1 hora de duración por defecto.
```

| Campo | Valor |
|---|---|
| Método | `POST` |
| URL | `{{ $env.STREAMIFY_BASE_URL }}/api/donna/tools/calendar/create-event` |

**Body:**
```json
{
  "client_id":   "{{ $('Guardar contexto').item.json.client_id }}",
  "service_id":  "{{ $('Guardar contexto').item.json.subscription_id }}",
  "channel_id":  "{{ $('Guardar contexto').item.json.channel_id }}",
  "summary":     "={{ $fromAI('summary', 'Título del evento, incluye emoji 🤖', 'string') }}",
  "description": "={{ $fromAI('description', 'Descripción opcional del evento', 'string') }}",
  "location":    "={{ $fromAI('location', 'Ubicación opcional', 'string') }}",
  "start":       "={{ $fromAI('start', 'Fecha/hora inicio ISO 8601 con timezone America/Guayaquil', 'string') }}",
  "end":         "={{ $fromAI('end', 'Fecha/hora fin ISO 8601 con timezone America/Guayaquil', 'string') }}",
  "timezone":    "America/Guayaquil"
}
```

---

## Paso 17 — Tool NUEVA: donna_calendar_update_event

**Tipo:** `HTTP Request Tool`  
**Nombre:** `donna_calendar_update_event`

**Description:**
```
Actualiza un evento existente en Google Calendar.
Úsala cuando el usuario pida cambiar hora, fecha, título, descripción o ubicación.
Si no tienes event_id, primero usa donna_calendar_list_events para encontrarlo.
```

**Body:**
```json
{
  "client_id":   "{{ $('Guardar contexto').item.json.client_id }}",
  "service_id":  "{{ $('Guardar contexto').item.json.subscription_id }}",
  "channel_id":  "{{ $('Guardar contexto').item.json.channel_id }}",
  "event_id":    "={{ $fromAI('event_id', 'ID del evento a actualizar', 'string') }}",
  "summary":     "={{ $fromAI('summary', 'Nuevo título del evento', 'string') }}",
  "start":       "={{ $fromAI('start', 'Nueva fecha/hora inicio ISO 8601', 'string') }}",
  "end":         "={{ $fromAI('end', 'Nueva fecha/hora fin ISO 8601', 'string') }}",
  "location":    "={{ $fromAI('location', 'Nueva ubicación opcional', 'string') }}",
  "timezone":    "America/Guayaquil"
}
```

**URL:** `{{ $env.STREAMIFY_BASE_URL }}/api/donna/tools/calendar/update-event`

---

## Paso 18 — Tool NUEVA: donna_calendar_delete_event

**Tipo:** `HTTP Request Tool`  
**Nombre:** `donna_calendar_delete_event`

**Description:**
```
Elimina o cancela un evento del Google Calendar del cliente.
Úsala SOLO cuando el usuario pida cancelar o borrar explícitamente.
Si no tienes event_id, primero usa donna_calendar_list_events.
```

**Body:**
```json
{
  "client_id":  "{{ $('Guardar contexto').item.json.client_id }}",
  "service_id": "{{ $('Guardar contexto').item.json.subscription_id }}",
  "channel_id": "{{ $('Guardar contexto').item.json.channel_id }}",
  "event_id":   "={{ $fromAI('event_id', 'ID del evento a eliminar', 'string') }}"
}
```

**URL:** `{{ $env.STREAMIFY_BASE_URL }}/api/donna/tools/calendar/delete-event`

---

## Paso 19 — Tool NUEVA: donna_sheets_get_tasks

**Tipo:** `HTTP Request Tool`  
**Nombre:** `donna_sheets_get_tasks`

**Description:**
```
Consulta las tareas del cliente desde Google Sheets.
Úsala para revisar pendientes, organizar el día o verificar si una tarea existe.
Las tareas completadas (completed: true) ya no deben usarse para planificar el día.
Prioriza por fecha límite más cercana.
Usa hours_per_day para saber cuánto tiempo agendar por tarea.
```

**Body:**
```json
{
  "client_id":        "{{ $('Guardar contexto').item.json.client_id }}",
  "service_id":       "{{ $('Guardar contexto').item.json.subscription_id }}",
  "channel_id":       "{{ $('Guardar contexto').item.json.channel_id }}",
  "include_completed": false
}
```

**URL:** `{{ $env.STREAMIFY_BASE_URL }}/api/donna/tools/sheets/get-tasks`

---

## Paso 20 — Tool NUEVA: donna_sheets_create_task

**Tipo:** `HTTP Request Tool`  
**Nombre:** `donna_sheets_create_task`

**Description:**
```
Crea una nueva tarea en Google Sheets del cliente.
Úsala cuando el usuario pida registrar o agregar una tarea pendiente.
Antes de crear, verifica con donna_sheets_get_tasks que no exista una igual.
```

**Body:**
```json
{
  "client_id":    "{{ $('Guardar contexto').item.json.client_id }}",
  "service_id":   "{{ $('Guardar contexto').item.json.subscription_id }}",
  "channel_id":   "{{ $('Guardar contexto').item.json.channel_id }}",
  "title":        "={{ $fromAI('title', 'Descripción clara de la tarea', 'string') }}",
  "due_date":     "={{ $fromAI('due_date', 'Fecha límite en formato YYYY-MM-DD', 'string') }}",
  "hours_per_day":"={{ $fromAI('hours_per_day', 'Horas estimadas por día', 'number') }}"
}
```

**URL:** `{{ $env.STREAMIFY_BASE_URL }}/api/donna/tools/sheets/create-task`

---

## Paso 21 — Tool NUEVA: donna_sheets_update_task

**Tipo:** `HTTP Request Tool`  
**Nombre:** `donna_sheets_update_task`

**Description:**
```
Actualiza una tarea existente en Google Sheets.
Úsala para cambiar título, fecha límite, horas por día o estado.
Si no tienes row_number, primero usa donna_sheets_get_tasks.
```

**Body:**
```json
{
  "client_id":     "{{ $('Guardar contexto').item.json.client_id }}",
  "service_id":    "{{ $('Guardar contexto').item.json.subscription_id }}",
  "channel_id":    "{{ $('Guardar contexto').item.json.channel_id }}",
  "row_number":    "={{ $fromAI('row_number', 'Número de fila de la tarea a actualizar', 'number') }}",
  "title":         "={{ $fromAI('title', 'Nuevo título de la tarea', 'string') }}",
  "due_date":      "={{ $fromAI('due_date', 'Nueva fecha límite YYYY-MM-DD', 'string') }}",
  "hours_per_day": "={{ $fromAI('hours_per_day', 'Nuevas horas estimadas por día', 'number') }}"
}
```

**URL:** `{{ $env.STREAMIFY_BASE_URL }}/api/donna/tools/sheets/update-task`

---

## Paso 22 — Tool NUEVA: donna_sheets_complete_task

**Tipo:** `HTTP Request Tool`  
**Nombre:** `donna_sheets_complete_task`

**Description:**
```
Marca una tarea como completada en Google Sheets.
Úsala cuando el usuario diga que terminó, completó o cerró una tarea.
Si no tienes row_number, primero usa donna_sheets_get_tasks.
```

**Body:**
```json
{
  "client_id":  "{{ $('Guardar contexto').item.json.client_id }}",
  "service_id": "{{ $('Guardar contexto').item.json.subscription_id }}",
  "channel_id": "{{ $('Guardar contexto').item.json.channel_id }}",
  "row_number": "={{ $fromAI('row_number', 'Número de fila de la tarea completada', 'number') }}"
}
```

**URL:** `{{ $env.STREAMIFY_BASE_URL }}/api/donna/tools/sheets/complete-task`

---

## Paso 23 — Modificar: Enviar mensaje

**Nodo:** `Enviar mensaje` (ya existe)

Solo cambiar `chatId`:

```
Antes:  6199654595
Ahora:  {{ $('Guardar contexto').item.json.chat_id }}
```

Parse mode: `HTML` (sin cambios)

---

## Paso 24 — Nodo NUEVO: HTTP Request — Guardar conversación

**Tipo:** `HTTP Request`  
**Nombre:** `POST Donna Respond`  
**Conectar:** Después de `Enviar mensaje`

| Campo | Valor |
|---|---|
| Método | `POST` |
| URL | `{{ $env.STREAMIFY_BASE_URL }}/api/donna/respond` |

**Headers:**
```
X-Donna-Key   →  {{ $env.DONNA_API_KEY }}
Content-Type  →  application/json
```

**Body:**
```json
{
  "client_id":          "{{ $('Guardar contexto').item.json.client_id }}",
  "service_id":         "{{ $('Guardar contexto').item.json.subscription_id }}",
  "channel_id":         "{{ $('Guardar contexto').item.json.channel_id }}",
  "telegram_chat_id":   "{{ $('Guardar contexto').item.json.chat_id }}",
  "user_message":       "{{ $('Normalizar mensaje').item.json.text }}",
  "assistant_response": "{{ $('Code in JavaScript').item.json.text }}",
  "message_type":       "text",
  "ai_metadata": {
    "model": "deepseek-chat"
  }
}
```

**Continue on Fail:** true (no debe detener el flujo si falla)

---

## Orden de conexiones final

```
Telegram Trigger
    → Normalizar mensaje
        → ¿Es código?
            → [true]  POST Register Telegram → ¿Registro exitoso? → Telegram OK/Error
            → [false] GET Donna Context → ¿Donna permitida?
                → [false] Telegram Bloqueado
                → [true]  Guardar contexto
                              → Switch (audio/texto)
                                  → [audio] Get a file → Transcribe a recording → AI Agent
                                  → [texto] Edit Fields → AI Agent
                              AI Agent recibe también:
                                  → Simple Memory (sessionKey dinámico)
                                  → DeepSeek Chat Model
                                  → Date & Time
                                  → 9 HTTP Tool nodes (Donna APIs)
                              → Code in JavaScript
                              → Enviar mensaje (chatId dinámico)
                              → POST Donna Respond
```

---

## Checklist de verificación

Antes de activar el flujo:

- [ ] Variables de entorno `STREAMIFY_BASE_URL` y `DONNA_API_KEY` configuradas en n8n
- [ ] `Normalizar mensaje` extrae correctamente `telegram_chat_id` y `is_activation_code`
- [ ] `POST Register Telegram` recibe código de 6 chars y registra el chat_id
- [ ] `GET Donna Context` devuelve `allowed: true` con `memory.session_key` y `agent.system_message`
- [ ] `Simple Memory` usa `session_key` del contexto (no hardcodeado)
- [ ] `AI Agent` usa `system_message` del contexto (no hardcodeado)
- [ ] `Enviar mensaje` usa `chat_id` dinámico (no hardcodeado `6199654595`)
- [ ] Los 9 HTTP Tool nodes envían `client_id`, `service_id`, `channel_id` correctamente
- [ ] `Date & Time` sigue conectado al AI Agent
- [ ] Nodos eliminados: `Get many events`, `Create an event`, `Update an event`, `Delete an event`, `Get Tareas`, `Crear o editar una tarea`, `Update row`, `Schedule Trigger`, `Edit Fields2`
- [ ] `POST Donna Respond` está al final y tiene "Continue on Fail" = true

---

## Prueba rápida del flujo

**Prueba 1 — Registro:**
1. Activar Donna Personal desde Streamify → aparece código (ej. `ABC123`)
2. Enviar `ABC123` al bot de Telegram
3. Debe responder: `¡Listo! Donna Personal fue vinculada correctamente.`

**Prueba 2 — Mensaje normal:**
1. Enviar `¿Qué tengo mañana en el calendario?`
2. Debe responder con los eventos del día siguiente del cliente
3. En `donna_tool_logs` de Laravel debe aparecer registro de `donna_calendar_list_events`

**Prueba 3 — Plan inactivo:**
1. Suspender la suscripción del cliente desde el panel admin
2. Enviar cualquier mensaje al bot
3. Debe responder el mensaje de `service_inactive` con el link de pago

---

## Notas importantes

- **`$fromAI()` solo funciona en HTTP Request Tool nodes** conectados al AI Agent como herramientas, no en HTTP Request normales.
- **`$('Guardar contexto').item.json.*`** asume que el nodo se llama exactamente `Guardar contexto`. Si lo renombras, actualiza todas las referencias.
- **El `Schedule Trigger` y `Edit Fields2`** (organización automática) se pueden migrar en una segunda fase usando `POST /api/donna/tools/calendar/list-events` y `POST /api/donna/tools/sheets/get-tasks` desde un flujo separado disparado por cron.
- **Los tokens Google nunca pasan por n8n.** Streamify los maneja internamente. Si un cliente reconecta Google desde el panel, el flujo sigue funcionando sin cambios en n8n.
