# Donna Personal — Flujo n8n con APIs de Streamify

**Versión:** 1.0  
**Fecha:** 2026-05-28  
**Base URL local:** `http://localhost/api`  
**Base URL producción:** `https://streamify.aaronsoft.es/api`

---

## Resumen del flujo completo

```
Telegram Trigger
      │
      ▼
¿Es un código de activación? (regex /^[A-Z0-9]{6}$/)
      │
      ├── SÍ ──► HTTP POST /api/donna/register-telegram
      │                │
      │                ├── success: true  → Telegram: "¡Listo! Ya puedes hablar conmigo."
      │                └── success: false → Telegram: mensaje de error
      │
      └── NO ──► HTTP GET /api/donna/context?telegram_chat_id={chat_id}
                      │
                      ├── allowed: false → Telegram: mensaje según reason
                      │
                      └── allowed: true
                              │
                              ▼
                        [Switch audio / texto]
                              │
                        ┌─────┴─────┐
                       audio      texto
                        │           │
                     Transcribir   Set text
                        └─────┬─────┘
                              │
                              ▼
                        "Pensando..." → Telegram
                              │
                              ▼
                         AI Agent
                     (prompt dinámico,
                      memoria por client_id,
                      tools via HTTP con Bearer token)
                              │
                              ▼
                     Format HTML → Telegram
```

---

## Autenticación

Todos los HTTP nodes que llamen a Streamify deben incluir este header:

| Header | Valor |
|--------|-------|
| `X-Donna-Key` | `donna-secret-key-cambiar-en-produccion` |
| `Content-Type` | `application/json` |
| `Accept` | `application/json` |

---

## NODO 1 — Telegram Trigger

**Tipo:** `Telegram Trigger`  
**Credencial:** `Google calendar agent` (tu bot actual, sin cambios)  
**Evento:** `message`

**Salida relevante que usarás luego:**
```
$json.message.chat.id          → chat_id del remitente
$json.message.text             → texto del mensaje
$json.message.voice.file_id    → si es audio
```

---

## NODO 2 — ¿Es código de activación?

**Tipo:** `Switch`  
**Modo:** Reglas

### Regla 1 — "es_codigo"
```
Campo:     {{ $json.message.text }}
Operación: Matches regex
Valor:     ^[A-Z0-9]{6}$
```

### Regla 2 — "es_audio"
```
Campo:     {{ $json.message.voice.file_id }}
Operación: exists
```

### Regla 3 (fallback) — "es_texto"
```
Sin condición (captura el resto)
```

---

## NODO 3A — Registrar Telegram (si es código)

**Tipo:** `HTTP Request`  
**Método:** `POST`  
**URL:** `{{ $env.STREAMIFY_BASE_URL }}/api/donna/register-telegram`

**Headers:**
```json
{
  "X-Donna-Key": "{{ $env.DONNA_API_KEY }}",
  "Content-Type": "application/json",
  "Accept": "application/json"
}
```

**Body (JSON):**
```json
{
  "code": "{{ $json.message.text.toUpperCase() }}",
  "telegram_chat_id": "{{ $json.message.chat.id }}"
}
```

### Respuesta exitosa (`success: true`):
```json
{
  "success": true,
  "message": "¡Listo! Tu cuenta de Donna está activada. Ya puedes hablar conmigo.",
  "client_id": 42,
  "service_type": "personal"
}
```

### Respuesta fallida (`success: false`):
```json
{
  "success": false,
  "message": "Código inválido, ya usado o expirado..."
}
```

**Nodo siguiente:** `Set respuesta_registro`

```
text → {{ $json.message }}
```

Luego enviar ese `text` por Telegram al `chat_id` original.

---

## NODO 3B — Consultar contexto (si es audio o texto)

**Tipo:** `HTTP Request`  
**Método:** `GET`  
**URL:** `{{ $env.STREAMIFY_BASE_URL }}/api/donna/context`

**Query parameters:**
```
telegram_chat_id  →  {{ $json.message.chat.id }}
```

**Headers:**
```json
{
  "X-Donna-Key": "{{ $env.DONNA_API_KEY }}",
  "Accept": "application/json"
}
```

### Respuesta cuando `allowed: true`:
```json
{
  "allowed": true,
  "client_id": 42,
  "subscription_id": 7,
  "service_type": "personal",
  "channel_id": 3,
  "expires_at": "2026-06-28T00:00:00Z",
  "google": {
    "access_token": "ya29.a0AfH6...",
    "refresh_token": "1//0g...",
    "token_expires_at": "2026-05-28T16:00:00Z",
    "is_expired": false,
    "scopes": ["calendar", "spreadsheets"],
    "email": "cliente@gmail.com"
  },
  "agent": {
    "agent_name": "Donna",
    "timezone": "America/Guayaquil",
    "language": "es",
    "prompt": null
  }
}
```

### Respuesta cuando `allowed: false`:

| `reason` | Significado | Mensaje sugerido |
|----------|-------------|------------------|
| `channel_not_found` | No registró su Telegram | "No encontré tu cuenta. Envía tu código de activación desde el panel." |
| `service_inactive` | Suscripción suspendida | "Tu suscripción Donna no está activa. Contacta a soporte." |
| `service_expired` | Venció la suscripción | "Tu Donna venció el {fecha}. Renueva desde streamify.aaronsoft.es/donna" |
| `unauthorized` | API key incorrecta | (error interno, no mostrar al usuario) |

**Nodo siguiente:** `Switch allowed`

---

## NODO 4 — Switch: ¿allowed?

**Tipo:** `Switch`

### Regla 1 — "bloqueado"
```
Campo:     {{ $json.allowed }}
Operación: equal
Valor:     false
```

### Regla 2 (fallback) — "continuar"
```
Sin condición
```

Si `bloqueado` → `Set mensaje_bloqueado` → Telegram:
```
{{ $json.message || "Tu servicio Donna no está disponible en este momento." }}
```

---

## NODO 5 — Guardar contexto en variables

**Tipo:** `Set`  
**Nombre:** `Guardar contexto`

Guarda los datos del contexto para usarlos en nodos siguientes:

| Variable | Expresión |
|----------|-----------|
| `chat_id` | `{{ $('Telegram Trigger').item.json.message.chat.id }}` |
| `client_id` | `{{ $json.client_id }}` |
| `google_token` | `{{ $json.google.access_token }}` |
| `google_email` | `{{ $json.google.email }}` |
| `google_is_expired` | `{{ $json.google.is_expired }}` |
| `agent_name` | `{{ $json.agent.agent_name }}` |
| `timezone` | `{{ $json.agent.timezone }}` |
| `agent_prompt` | `{{ $json.agent.prompt }}` |

---

## NODO 6 — Switch: ¿audio o texto?

**Tipo:** `Switch` (igual que en tu flujo actual)

Detecta si `$('Telegram Trigger').item.json.message.voice.file_id` existe.

- Si audio → `Get a file` → `Transcribe a recording` → `Set text`
- Si texto → `Set text` directamente

**Set text** (unifica los dos caminos):
```
text  →  {{ $json.text || $json.transcription }}
```

---

## NODO 7 — Mensaje de espera

**Tipo:** `Telegram` — Send Message  

```
Chat ID:  {{ $('Guardar contexto').item.json.chat_id }}
Text:     Pensando... 🤔
```

---

## NODO 8 — AI Agent (dinámico por cliente)

**Tipo:** `AI Agent`

### System message dinámico:
```
={{
  $('Guardar contexto').item.json.agent_prompt ||
  `Eres ${$('Guardar contexto').item.json.agent_name}, una secretaria inteligente que gestiona calendario y tareas.

Fecha actual: ${$now}
Zona horaria: ${$('Guardar contexto').item.json.timezone}
Cuenta Google: ${$('Guardar contexto').item.json.google_email}

FUNCIONES:
- Agendar, editar, eliminar y consultar eventos en Google Calendar
- Gestionar tareas desde Google Sheets

REGLAS GENERALES:
- Sé breve, claro y directo
- Usa herramientas solo cuando sea necesario
- Máximo 6 acciones por ejecución

REGLAS DE AGENDA:
- Horario permitido: 09:00 AM a 08:00 PM
- Nunca usar 01:00 PM (almuerzo)
- No solapar eventos

FORMATO DE RESPUESTA:
- Resumen claro de acciones realizadas
- Indicar horarios y tareas programadas`
}}
```

### Memoria (keyed por client_id, no hardcodeado):
**Tipo:** `Simple Memory`
```
Session ID type: customKey
Session key:     donna_{{ $('Guardar contexto').item.json.client_id }}
```

### Modelo de lenguaje:
Sin cambios — usa DeepSeek o el que tengas configurado.

---

## NODO 9 — Tool: Google Calendar — Listar eventos

**Tipo:** `HTTP Request` (Tool)  
**Descripción para el agente:**
```
Consulta eventos en Google Calendar dentro de un rango de fechas.
Úsala para verificar disponibilidad, ver agenda del día o de una fecha.
Parámetros: timeMin (ISO8601), timeMax (ISO8601)
```

**Método:** `GET`  
**URL:** 
```
https://www.googleapis.com/calendar/v3/calendars/{{ $('Guardar contexto').item.json.google_email }}/events
```

**Query params:**
```
timeMin   →  {{ /*fromAI*/ $fromAI('timeMin', 'Inicio del rango en formato ISO8601 con timezone America/Guayaquil', 'string') }}
timeMax   →  {{ /*fromAI*/ $fromAI('timeMax', 'Fin del rango en formato ISO8601 con timezone America/Guayaquil', 'string') }}
singleEvents → true
orderBy   →  startTime
```

**Headers:**
```
Authorization  →  Bearer {{ $('Guardar contexto').item.json.google_token }}
```

---

## NODO 10 — Tool: Google Calendar — Crear evento

**Tipo:** `HTTP Request` (Tool)  
**Descripción para el agente:**
```
Crea un evento en Google Calendar.
Proporciona siempre: summary, start (ISO8601), end (ISO8601).
Opcional: description, location.
Reglas: nunca sin título, duración mínima 1h si no se indica hora_fin.
```

**Método:** `POST`  
**URL:**
```
https://www.googleapis.com/calendar/v3/calendars/{{ $('Guardar contexto').item.json.google_email }}/events
```

**Headers:**
```
Authorization  →  Bearer {{ $('Guardar contexto').item.json.google_token }}
Content-Type   →  application/json
```

**Body (JSON generado por el agente):**
```json
{
  "summary":     "={{ $fromAI('summary', 'Título del evento, incluye emoji 🤖', 'string') }}",
  "description": "={{ $fromAI('description', 'Descripción opcional', 'string') }}",
  "location":    "={{ $fromAI('location', 'Ubicación opcional', 'string') }}",
  "start": {
    "dateTime": "={{ $fromAI('start', 'Fecha/hora inicio ISO8601 con timezone America/Guayaquil', 'string') }}",
    "timeZone": "America/Guayaquil"
  },
  "end": {
    "dateTime": "={{ $fromAI('end', 'Fecha/hora fin ISO8601 con timezone America/Guayaquil', 'string') }}",
    "timeZone": "America/Guayaquil"
  },
  "colorId": "2"
}
```

---

## NODO 11 — Tool: Google Calendar — Actualizar evento

**Tipo:** `HTTP Request` (Tool)  
**Descripción para el agente:**
```
Actualiza un evento existente. Requiere el eventId del evento a modificar.
Obtén el eventId primero con la tool de listar eventos.
```

**Método:** `PATCH`  
**URL:**
```
https://www.googleapis.com/calendar/v3/calendars/{{ $('Guardar contexto').item.json.google_email }}/events/{{ $fromAI('eventId', 'ID del evento a actualizar', 'string') }}
```

**Headers:**
```
Authorization  →  Bearer {{ $('Guardar contexto').item.json.google_token }}
Content-Type   →  application/json
```

**Body:** Solo los campos que cambian (el agente decide cuáles incluir).

---

## NODO 12 — Tool: Google Calendar — Eliminar evento

**Tipo:** `HTTP Request` (Tool)  
**Descripción para el agente:**
```
Elimina un evento del calendario. Requiere el eventId.
Úsala solo cuando el usuario pida cancelar o borrar explícitamente.
```

**Método:** `DELETE`  
**URL:**
```
https://www.googleapis.com/calendar/v3/calendars/{{ $('Guardar contexto').item.json.google_email }}/events/{{ $fromAI('eventId', 'ID del evento a eliminar', 'string') }}
```

**Headers:**
```
Authorization  →  Bearer {{ $('Guardar contexto').item.json.google_token }}
```

---

## NODO 13 — Tool: Google Sheets — Leer tareas

**Tipo:** `HTTP Request` (Tool)  
**Descripción para el agente:**
```
Lee las tareas del Google Sheets del cliente.
Ignora tareas donde la columna ✓ sea TRUE o "verdadero".
Prioriza por fecha límite más cercana.
Usa "horas al día" para planificar el tiempo disponible.
El spreadsheet_id viene del cliente, no lo inventes.
```

**Método:** `GET`  
**URL:**
```
https://sheets.googleapis.com/v4/spreadsheets/{{ $fromAI('spreadsheet_id', 'ID del spreadsheet de tareas del cliente', 'string') }}/values/Tareas
```

**Headers:**
```
Authorization  →  Bearer {{ $('Guardar contexto').item.json.google_token }}
```

> **Nota:** El `spreadsheet_id` del cliente se configurará en `donna_agent_configs` en el Sprint 3. Por ahora el agente lo pedirá si no lo sabe.

---

## NODO 14 — Tool: Google Sheets — Agregar tarea

**Tipo:** `HTTP Request` (Tool)  
**Descripción para el agente:**
```
Agrega una nueva tarea al Google Sheets. 
Campos: Tarea, Fecha (YYYY-MM-DD), Horas al día, ✓ (siempre FALSE).
No dupliques tareas existentes.
```

**Método:** `POST`  
**URL:**
```
https://sheets.googleapis.com/v4/spreadsheets/{{ $fromAI('spreadsheet_id', 'ID del spreadsheet', 'string') }}/values/Tareas:append?valueInputOption=USER_ENTERED
```

**Headers:**
```
Authorization  →  Bearer {{ $('Guardar contexto').item.json.google_token }}
Content-Type   →  application/json
```

**Body:**
```json
{
  "values": [[
    "={{ $fromAI('tarea', 'Descripción de la tarea', 'string') }}",
    "FALSE",
    "={{ $fromAI('fecha', 'Fecha límite YYYY-MM-DD', 'string') }}",
    "={{ $fromAI('horas_dia', 'Horas estimadas por día', 'number') }}"
  ]]
}
```

---

## NODO 15 — Tool: Date & Time

Sin cambios. Ya está en tu flujo actual.

```
Timezone: America/Guayaquil
```

---

## NODO 16 — Formatear respuesta (Code JS)

Sin cambios respecto a tu flujo actual. Convierte markdown a HTML de Telegram.

---

## NODO 17 — Enviar respuesta por Telegram

**Tipo:** `Telegram` — Send Message

```
Chat ID:     {{ $('Guardar contexto').item.json.chat_id }}
Text:        {{ $json.text }}
Parse mode:  HTML
```

> **Cambio clave vs flujo actual:** El `chat_id` ya no es hardcodeado `6199654595` — viene del contexto, lo que hace el flujo multi-cliente.

---

## Variables de entorno n8n a configurar

En tu instancia de n8n, agrega estas variables:

| Variable | Valor local | Valor producción |
|----------|-------------|------------------|
| `STREAMIFY_BASE_URL` | `http://localhost` | `https://streamify.aaronsoft.es` |
| `DONNA_API_KEY` | `donna-secret-key-cambiar-en-produccion` | (clave real del .env de prod) |

Usarlas en los nodos: `{{ $env.DONNA_API_KEY }}`

---

## Manejo de token Google expirado

En el **Nodo 5 (Guardar contexto)**, agrega una rama:

```
Si google_is_expired == true
    → Telegram: "⚠️ Tu sesión de Google ha expirado. 
                  Reconéctala en: streamify.aaronsoft.es/donna"
    → Detener flujo
```

En el futuro, Streamify hará el refresh automático (HU-14) y `is_expired` siempre será `false`.

---

## Diferencias clave vs flujo actual

| Aspecto | Flujo actual (Pablo) | Flujo nuevo (multi-cliente) |
|---------|---------------------|----------------------------|
| `chat_id` | Hardcodeado `6199654595` | `$('Guardar contexto').item.json.chat_id` |
| Google OAuth | Credencial fija en n8n | Bearer token de `/api/donna/context` |
| `sessionKey` memoria | `"6199654595"` | `"donna_{{ client_id }}"` |
| System prompt | Hardcodeado en el nodo | `agent_prompt` del contexto (o fallback) |
| Validación activo/inactivo | No existe | Nodo context → `allowed` |
| Registro cliente | No existe | Código 6 chars → `/register-telegram` |
| Google Calendar tool | Nodo nativo con OAuth | HTTP Request con Bearer token |
| Google Sheets tool | Nodo nativo con OAuth | HTTP Request con Bearer token |

---

## Orden de construcción recomendado

```
1. Telegram Trigger (sin cambios)
2. Switch: ¿es código?
3. HTTP: register-telegram  →  Telegram respuesta
4. HTTP: context
5. Switch: ¿allowed?        →  Telegram mensaje bloqueado
6. Set: Guardar contexto
7. Switch: ¿is_expired?     →  Telegram aviso expirado
8. Switch: ¿audio o texto?
9. Get file + Transcribe (sin cambios para audio)
10. Set text
11. Telegram: "Pensando..."
12. AI Agent con:
    - System message dinámico
    - Simple Memory (sessionKey = donna_{client_id})
    - Tools HTTP: Calendar listar, crear, actualizar, eliminar
    - Tools HTTP: Sheets leer, agregar
    - Tool: Date & Time (sin cambios)
13. Code JS formatear HTML (sin cambios)
14. Telegram: enviar respuesta
```

---

## Endpoints Streamify disponibles hoy

| Método | URL | Descripción |
|--------|-----|-------------|
| `GET` | `/api/donna/context` | Valida acceso y devuelve contexto + tokens Google |
| `POST` | `/api/donna/register-telegram` | Registra chat_id del cliente con código de activación |

**Próximos endpoints (Sprint 5 — API n8n):**

| Método | URL | Descripción |
|--------|-----|-------------|
| `POST` | `/api/donna/tools/google-calendar/create-event` | Streamify crea el evento (token manejado server-side) |
| `POST` | `/api/donna/tools/google-calendar/freebusy` | Disponibilidad en Calendar |
| `POST` | `/api/donna/tools/google-sheets/append-row` | Agrega fila a Sheets |

Cuando esos endpoints existan, reemplazarás los HTTP nodes de Google por llamadas a Streamify, eliminando la necesidad de pasar el Bearer token en n8n.
