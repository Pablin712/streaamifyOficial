# Donna Personal — Documentación de Implementación Completa

> Fecha: 2026-05-28  
> Estado: **Producción**  
> Base para: Donna Business

---

## 1. Visión General

Donna Personal es una secretaria privada con IA, accesible por Telegram, que gestiona el calendario y tareas del propio cliente (el dueño de la cuenta). El cliente habla directamente con Donna; nadie más tiene acceso.

**Stack de ejecución:**
- **Laravel** — Backend, autenticación, APIs internas, integración Google OAuth
- **n8n** — Orquestador del bot (recibe Telegram, llama al contexto, ejecuta AI Agent, llama tools)
- **DeepSeek / OpenAI** — Modelo de lenguaje dentro del AI Agent de n8n
- **Google Calendar API** — Gestión de agenda
- **Google Sheets API** — Gestión de tareas (lista "Lista de Tareas")
- **Telegram Bot API** — Canal de comunicación con el cliente

---

## 2. Tablas de Base de Datos

### 2.1 `donna_plans`
Catálogo de planes disponibles. Un plan define precio, tipo y ciclo de facturación.

| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| code | string | Ej: `donna_personal_monthly` |
| name | string | Nombre visible |
| service_type | enum | `personal` \| `business` |
| description | text | |
| price | decimal(10,2) | |
| currency | string | `USD` |
| billing_cycle | enum | `monthly` \| `yearly` \| `one_time` |
| features_json | json | Array de strings para mostrar en la tarjeta |
| is_active | boolean | Solo los activos son visibles |
| sort_order | integer | Orden en la lista |

### 2.2 `donna_subscriptions`
Una suscripción por cliente por tipo de servicio. Es el "contrato" entre el cliente y Donna.

| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| client_id | FK → clientes.idcli | |
| plan_id | FK → donna_plans.id | |
| service_type | enum | `personal` \| `business` |
| status | enum | `pending` \| `active` \| `suspended` \| `expired` \| `cancelled` |
| billing_cycle | enum | Copiado del plan al momento de activar |
| price_paid | decimal | Precio que se cobró |
| currency | string | |
| starts_at | datetime | |
| expires_at | datetime | null = sin límite |
| last_payment_at | datetime | |
| next_payment_due | datetime | |
| is_enabled | boolean | false = suspendida manualmente |
| suspended_reason | string | Razón de suspensión |
| activated_by | FK → empleados | null si fue autoservicio |

### 2.3 `donna_requests`
Solicitudes cuando el cliente no tiene saldo suficiente para activar directamente.

| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| client_id | FK → clientes.idcli | |
| plan_id | FK → donna_plans.id | |
| status | enum | `pending` \| `approved` \| `rejected` |
| message | text | Mensaje opcional del cliente |
| employee_notes | text | Notas internas del admin |
| reviewed_by | FK → empleados | |
| reviewed_at | datetime | |

### 2.4 `donna_integrations`
Credenciales OAuth de Google del cliente. Una integración por tipo por cliente.

| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| client_id | FK → clientes.idcli | |
| subscription_id | FK nullable | |
| integration_type | enum | `google` (en futuro: `openai`, etc.) |
| name | string | Nombre descriptivo |
| access_token_encrypted | text | Encriptado con `Crypt::encryptString()` |
| refresh_token_encrypted | text | Encriptado |
| token_expires_at | datetime | |
| scopes_json | json | Array de scopes autorizados |
| status | enum | `active` \| `expired` \| `revoked` \| `error` |
| last_sync_at | datetime | |
| last_error | text | Mensaje del último error |
| metadata_json | json | `{email, google_id, avatar}` |

### 2.5 `donna_channels`
Canal de comunicación del cliente con Donna. Para Personal = Telegram; para Business = WhatsApp.

| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| client_id | FK → clientes.idcli | |
| subscription_id | FK → donna_subscriptions | |
| service_type | enum | `personal` \| `business` |
| channel_type | enum | `telegram` \| `whatsapp` |
| provider | string | `telegram_bot` \| `evolution_api` |
| owner_identifier | string | `telegram_chat_id` una vez vinculado |
| telegram_username | string | @usuario de Telegram |
| telegram_name | string | Nombre del perfil Telegram |
| activated_at | datetime | Momento en que se vinculó |
| activation_code | string(6) | Código temporal uppercase, expira en 48h |
| code_expires_at | datetime | |
| status | enum | `pending` \| `active` \| `inactive` \| `error` \| `suspended` |
| is_default | boolean | Canal principal de este tipo |
| last_connected_at | datetime | |
| last_error | text | |
| metadata_json | json | |

### 2.6 `donna_agent_configs`
Configuración personalizable del agente por cliente. Se crea automáticamente al activar.

| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| client_id | FK → clientes.idcli | |
| subscription_id | FK → donna_subscriptions | |
| service_type | enum | `personal` \| `business` |
| agent_name | string | Default: `Donna` |
| personal_context | text | Info personal que el cliente escribe sobre sí mismo |
| main_prompt | text | Prompt completo personalizado (reemplaza el default si se llena) |
| tone | string | Default: `profesional, amable y directa` |
| language | string | Default: `es` |
| timezone | string | Default: `America/Guayaquil` |
| spreadsheet_id | string | ID del Google Sheet de tareas |
| spreadsheet_name | string | Default: `Tareas` |
| calendar_id | string | Default: `primary` |
| is_active | boolean | |

### 2.7 `donna_tool_logs`
Log de cada herramienta ejecutada (Calendar, Sheets).

| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| client_id | FK → clientes.idcli | |
| subscription_id | FK nullable | |
| channel_id | FK nullable | |
| tool_name | string | Ej: `donna_calendar_create_event` |
| request_json | json | Parámetros de entrada (sanitizados) |
| response_json | json | Respuesta (sanitizada, sin tokens) |
| success | boolean | |
| reason | string | Código de resultado |
| duration_ms | integer | Tiempo de ejecución en ms |

---

## 3. Modelos Laravel

```
app/Models/
├── DonnaPlan.php           — Scopes: active(), personal(), business() | Accessor: billing_cycle_label
├── DonnaSubscription.php   — Métodos: isActive(), daysRemaining() | Accessors: status_label, status_color
├── DonnaRequest.php        — Accessors: status_label, status_color
├── DonnaIntegration.php    — Métodos: getAccessToken(), getRefreshToken(), isTokenExpired(), isActive()
│                             Static: googleConnected($clientId)
├── DonnaChannel.php        — Métodos: isCodeExpired(), isActive()
├── DonnaAgentConfig.php    — Config del agente (prompts, spreadsheet_id, calendar_id)
└── DonnaToolLog.php        — Log de herramientas
```

**Patrón de encriptación** (tokens sensibles):
```php
// Guardar
'access_token_encrypted' => Crypt::encryptString($token)

// Leer
Crypt::decryptString($this->access_token_encrypted)
```

---

## 4. Flujos Completos

### 4.1 Flujo de Contratación (autoservicio)

```
1. Cliente entra a /donna
   └── Ve tarjeta "Donna Personal" con precio y características

2. Click "Conectar Google"
   └── GET /cliente/donna/google/connect
   └── Redirect OAuth Google (scopes: calendar + spreadsheets)
   └── Google callback → POST /cliente/donna/google/callback
   └── Se crea/actualiza DonnaIntegration {status: active, metadata: {email, avatar}}

3. Click "Activar ahora — $X.XX"
   └── Abre modal de confirmación con resumen de pago

4. Confirmar → POST /cliente/donna/activar {plan_id}
   └── Valida: Google conectado, plan activo, suscripción no duplicada
   └── Si saldo ≥ precio:
       a. Crea DonnaSubscription {status: active}
       b. Crea DonnaChannel {status: pending, activation_code: "ABC123"}
       c. Descuenta saldo del cliente
       d. Registra en Historial
       e. DB::commit()
       f. Llama setupSpreadsheet() → crea "Lista de Tareas" en Google Drive del cliente
       g. Guarda spreadsheet_id en DonnaAgentConfig
       h. Flash: donna_activation_code, donna_plan_type='personal'
       
   └── Si saldo < precio:
       a. Crea DonnaRequest {status: pending}
       b. Admin aprueba manualmente desde /admin/donna/solicitudes

5. Modal automático muestra el código de activación (6 chars)
   └── Instrucciones para ir a Telegram y enviar el código al bot
```

### 4.2 Flujo de Vinculación Telegram

```
1. Cliente abre Telegram, busca @{donna_bot_username}
2. Escribe el código: "ABC123"

3. n8n recibe mensaje en Telegram Trigger
4. Nodo "Normalizar mensaje" detecta is_activation_code = true
5. Ruta código → POST https://streamify.aaronsoft.es/public/api/donna/register-telegram
   Headers: X-Donna-Key: {donna-secret-key}
   Body: {code, telegram_chat_id, telegram_username, telegram_name}

6. DonnaTelegramController@register:
   a. Busca DonnaChannel por activation_code (no expirado)
   b. Actualiza: owner_identifier = chat_id, status = active, telegram_name, activated_at
   c. Retorna: {success: true, registered: true, message: "¡Listo! Donna vinculada."}

7. n8n envía mensaje de confirmación al cliente por Telegram
```

### 4.3 Flujo de Conversación (mensaje normal)

```
1. Cliente envía mensaje por Telegram

2. n8n: Nodo "Normalizar mensaje"
   └── Extrae: chat_id, text, is_audio, is_text, is_activation_code

3. Ruta "mensaje" → GET /api/donna/context?telegram_chat_id=X
   └── DonnaServiceValidator: valida canal activo, suscripción activa
   └── DonnaPersonalContextService::build()
   └── Retorna JSON con:
       - client: {id, name, email}
       - service: {id, type, status, expires_at}
       - channel: {id, type, telegram_chat_id}
       - agent: {name, timezone, system_message}
       - memory: {session_key}
       - google: {connected, calendar_enabled, sheets_enabled, spreadsheet_id}
       - tools: {calendar: {...}, sheets: {...}}

4. Nodo "Guardar contexto" (Code JS)
   └── Extrae y organiza: client_id, service_id, channel_id, session_key, system_message

5. Si is_audio: Get Telegram File → Transcribe (OpenAI Whisper) → AI Agent
   Si is_text: directo → AI Agent

6. AI Agent (DeepSeek + Simple Memory + Date&Time + herramientas)
   └── Usa session_key para memoria de conversación (buffer window 20 msgs)
   └── Puede llamar:
       - donna_calendar_list_events
       - donna_calendar_freebusy
       - donna_calendar_create_event
       - donna_calendar_update_event
       - donna_calendar_delete_event
       - donna_sheets_get_tasks
       - donna_sheets_create_task
       - donna_sheets_update_task
       - donna_sheets_complete_task

7. Respuesta → Formatear HTML (bold, italic, limpiar markdown)
8. Enviar mensaje por Telegram
9. POST /api/donna/respond → Guarda en donna_tool_logs
```

---

## 5. API Interna (Laravel → n8n)

### Autenticación
Todas las APIs de Donna usan el header:
```
X-Donna-Key: {valor de DONNA_API_KEY en .env}
```

El middleware `donna.api` valida este header antes de ejecutar cualquier endpoint.

### Endpoints

#### `POST /api/donna/register-telegram`
Vincula un chat_id de Telegram con un canal pendiente.
```json
// Request
{
  "code": "ABC123",
  "telegram_chat_id": "123456789",
  "telegram_username": "usuario",
  "telegram_name": "Nombre Apellido"
}

// Response OK
{ "success": true, "registered": true, "message": "..." }

// Response Error
{ "success": false, "message": "Código inválido o expirado." }
```

#### `GET /api/donna/context?telegram_chat_id=X&channel_type=telegram&service_type=personal`
Retorna el contexto completo para el AI Agent.
```json
{
  "success": true,
  "allowed": true,
  "client": { "id": 5, "name": "Pablo Jiménez", "email": "..." },
  "service": { "id": 3, "type": "personal", "status": "active", "expires_at": "2026-06-28" },
  "channel": { "id": 2, "type": "telegram", "telegram_chat_id": "123456789", "status": "active" },
  "agent": {
    "name": "Donna",
    "timezone": "America/Guayaquil",
    "system_message": "Eres Donna, una secretaria personal..."
  },
  "memory": { "session_key": "donna_personal:5:123456789", "window_size": 20 },
  "google": {
    "connected": true,
    "calendar_enabled": true,
    "sheets_enabled": true,
    "spreadsheet_id": "1abc...",
    "spreadsheet_name": "Tareas"
  },
  "tools": {
    "calendar": { "list_events": true, "freebusy": true, "create_event": true, "update_event": true, "delete_event": true },
    "sheets": { "get_tasks": true, "create_task": true, "update_task": true, "complete_task": true }
  }
}
```

Si la suscripción no está activa o el canal no está registrado:
```json
{ "success": false, "allowed": false, "reason": "subscription_not_active", "message": "..." }
```

#### `POST /api/donna/tools/calendar/create-event`
```json
// Request
{
  "client_id": 5,
  "service_id": 3,
  "channel_id": 2,
  "summary": "🤖 Reunión con cliente",
  "start": "2026-05-29T10:00:00-05:00",
  "end": "2026-05-29T11:00:00-05:00",
  "timezone": "America/Guayaquil"
}

// Response OK
{ "success": true, "event": { "id": "...", "htmlLink": "...", ... }, "message": "Evento creado." }
```

#### `POST /api/donna/tools/sheets/get-tasks`
```json
// Request
{
  "client_id": 5,
  "service_id": 3,
  "channel_id": 2,
  "include_completed": false
}

// Response OK
{
  "success": true,
  "tasks": [
    { "id": "T-ABC12", "completed": false, "date": "2026-06-01", "title": "Entregar proyecto", "hours_per_day": 2, "row_number": 3 }
  ]
}
```

---

## 6. Servicios PHP

### `DonnaServiceValidator`
Valida el acceso antes de ejecutar cualquier herramienta o devolver contexto.

```php
// Valida por telegram_chat_id (usado por DonnaContextController)
$result = $validator->resolveByTelegram($telegramChatId);
// Retorna: ['allowed' => bool, 'channel' => DonnaChannel, 'sub' => DonnaSubscription]

// Valida para tools (usado por DonnaCalendarToolController y DonnaSheetsToolController)
$result = $validator->validateForTool($clientId, $subscriptionId, $channelId);
// Retorna: ['allowed' => bool, 'reason' => string, 'message' => string]
```

### `DonnaPersonalContextService`
Construye el JSON de contexto completo que n8n usa para configurar el AI Agent.

**Lógica del system_message:**
1. Si `DonnaAgentConfig::main_prompt` tiene valor → usa ese prompt (con sustitución de `{{now}}`, `{{timezone}}`, `{{agent_name}}`)
2. Si no → genera prompt por defecto con:
   - Fecha/hora actual en la timezone del cliente
   - Cuenta Google
   - `personal_context` del cliente (si existe)
   - Reglas de comportamiento
   - Lista de herramientas disponibles (incluye sheets solo si `spreadsheet_id` está configurado)

### `DonnaGoogleTokenService`
Maneja la vida del access token de Google.

```php
$token = $tokenService->getValidAccessToken($integ);
// - Si no expiró: desencripta y retorna
// - Si expiró: llama a /oauth2.googleapis.com/token con refresh_token
//   - Si refresh falla (invalid_grant): marca integración como 'revoked'
//   - Si refresh OK: actualiza access_token_encrypted y token_expires_at
```

### `DonnaSpreadsheetSetupService`
Crea el Google Spreadsheet "Lista de Tareas" en la cuenta del cliente vía API.

**Qué crea:**
- Spreadsheet: "Lista de Tareas"
- Hoja (tab): "Tareas"
- Encabezado fila 1: `ID | ✓ | Fecha | Tarea | Horas al día | Creado por | Actualizado en`
- Formato: fondo verde oscuro + texto blanco + bold
- Fila congelada, filtros activos
- Validación BOOLEAN en columna B (checkboxes)
- Formato condicional: tachado + gris cuando `✓ = TRUE`
- Anchos de columna optimizados

**Cuándo se llama:**
1. Al activar Donna Personal (`ClienteDonnaController::activar()`) — después del DB::commit()
2. En `DonnaSheetsToolController::resolveContext()` — si `spreadsheet_id` es null al momento de usar una herramienta (clientes que contrataron antes de que existiera el setup automático)

### `DonnaSheetsTaskService`
Operaciones CRUD sobre el spreadsheet del cliente.

**Estructura de columnas (constante COLS):**
```
A: id           — "T-XXXXX" generado al crear
B: completed    — "TRUE" / "FALSE" (checkbox)
C: date         — Fecha de vencimiento
D: title        — Descripción de la tarea
E: hours_per_day — Horas estimadas diarias
F: created_by   — "Donna" cuando la crea el agente
G: updated_at   — Timestamp de última modificación
```

**Recuperación tras borrado:** Si Google devuelve 404, `DonnaSheetsToolController::sheetsError()` detecta el código, resetea `spreadsheet_id = null` en `DonnaAgentConfig`, y responde a Donna que debe reintentar. En el reintento, `resolveContext()` vuelve a crear una hoja nueva.

---

## 7. Personalización del Agente (por el cliente)

Los clientes pueden personalizar a Donna desde **Mi Actividad → tab "Donna AI"**:

### Contexto personal (`personal_context`, max 1000 chars)
Se añade al prompt por defecto. No lo reemplaza. Ideal para describir: profesión, proyectos, horarios, preferencias de comunicación.

Ejemplo:
> "Soy diseñador freelance. Trabajo 9am-6pm lunes a viernes. No agendar domingos. Proyectos activos: App X, Branding Y."

### Prompt personalizado completo (`main_prompt`, max 5000 chars)
Reemplaza **todo** el prompt por defecto si se llena. Variables disponibles:
- `{{now}}` → fecha y hora actual en la timezone del cliente
- `{{timezone}}` → zona horaria
- `{{agent_name}}` → nombre del agente (default: "Donna")

Si está vacío, Donna usa el prompt predeterminado (recomendado).

### Vista previa
La sección incluye un acordeón que muestra el texto exacto que Donna recibirá en el próximo mensaje, generado en tiempo real por `DonnaPersonalContextService::getSystemMessagePreview()`.

---

## 8. Panel Admin

### `/admin/donna/planes`
CRUD de planes. Campos editables: nombre, descripción, precio, ciclo, características (json array), estado activo.

### `/admin/donna/suscripciones`
Tabla con todas las suscripciones. Columnas: ID, **Cliente (con foto Google)**, Plan, Tipo, Estado Google, Estado, Vencimiento, Días restantes, Acciones.

Acciones disponibles:
- **Renovar** — extiende `expires_at`
- **Suspender** — cambia status y guarda razón
- **Revocar Google** — marca DonnaIntegration como revoked

La foto del cliente se obtiene de `DonnaIntegration.metadata_json['avatar']` (guardada en el callback OAuth).

### `/admin/donna/solicitudes`
Solicitudes de clientes sin saldo. El admin puede:
- **Aprobar** → crea DonnaSubscription, genera activation_code, notifica
- **Rechazar** → cambia status con nota

---

## 9. Seguridad

| Dato | Protección |
|---|---|
| Google access_token | `Crypt::encryptString()` en BD |
| Google refresh_token | `Crypt::encryptString()` en BD |
| API Key n8n→Laravel | Header `X-Donna-Key`, validado por middleware `donna.api` |
| Logs de herramientas | `DonnaToolLogger::sanitize()` elimina tokens antes de guardar |
| Multi-tenant | Todas las queries filtran por `client_id` + `subscription_id` |

---

## 10. Workflow n8n (`donna_personal_telegram_streamify_template.json`)

El workflow completo importable está en `docs/` y también disponible en la carpeta de Downloads del servidor.

### Nodos en orden:

| Nodo | Tipo | Función |
|---|---|---|
| Telegram Trigger | Webhook | Recibe mensajes del bot |
| Normalizar mensaje | Code JS | Extrae chat_id, text, is_audio, is_text, is_activation_code |
| Ruta código o contexto | Switch | Bifurca: código de activación vs mensaje normal |
| POST Register Telegram | HTTP | Vincula chat_id con activation_code |
| Registro exitoso | Switch | OK → mensaje bienvenida, Error → mensaje error |
| GET Donna Context | HTTP | Obtiene contexto del cliente |
| Donna permitida | Switch | allowed → procesar, blocked → mensaje bloqueado |
| Guardar contexto | Code JS | Reorganiza datos del contexto para el agente |
| Mensaje de espera | Telegram | Envía "Pensando... 🤔" |
| Switch audio/texto | Switch | Bifurca por tipo de entrada |
| Get a file | Telegram | Descarga archivo de voz |
| Transcribe a recording | OpenAI | Whisper transcripción |
| Texto directo | Set | Pasa text tal cual |
| AI Agent | LangChain | DeepSeek + Simple Memory + Date&Time + 9 tools |
| Formatear respuesta HTML | Code JS | Convierte markdown a HTML de Telegram |
| Error fallback | Set | Mensaje de error genérico |
| Preparar respuesta final | Set | Unifica salida de las dos ramas |
| Enviar mensaje | Telegram | Envía respuesta al cliente |
| POST Donna Respond | HTTP | Guarda conversación en Laravel |

### Variables de entorno en n8n:
```
STREAMIFY_BASE_URL = https://streamify.aaronsoft.es
DONNA_API_KEY      = {valor de DONNA_API_KEY en .env de Laravel}
```

### Credenciales requeridas en n8n:
- `telegramApi` — Bot de Telegram para Donna Personal
- `openAiApi` — Para transcripción de audio (Whisper)
- `deepSeekApi` — Modelo de lenguaje del agente

---

## 11. Variables de Entorno Laravel (`.env`)

```env
DONNA_API_KEY=donna-secret-key-xxx     # Header X-Donna-Key para n8n
DONNA_TELEGRAM_BOT_USERNAME=nombre_bot  # Para mostrar en instrucciones al cliente

GOOGLE_CLIENT_ID=xxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=xxx
GOOGLE_REDIRECT_URI=https://streamify.aaronsoft.es/cliente/donna/google/callback
```

En `config/services.php`:
```php
'donna' => [
    'telegram_bot_username'        => env('DONNA_TELEGRAM_BOT_USERNAME', 'DonnaStreamifyBot'),
    'google_default_timezone'      => env('DONNA_DEFAULT_TIMEZONE', 'America/Guayaquil'),
],
```

---

## 12. Archivos Clave — Mapa de Responsabilidades

```
# ACTIVACIÓN DEL SERVICIO (flujo del cliente)
app/Http/Controllers/ClienteDonnaController.php
  solicitar()      — Crea DonnaRequest si no tiene saldo
  activar()        — Crea Subscription + Channel + llama setupSpreadsheet()
  saveConfig()     — Guarda personal_context y main_prompt
  setupSpreadsheet() — Crea hoja Google y guarda spreadsheet_id

# OAUTH GOOGLE
app/Http/Controllers/DonnaGoogleController.php
  redirect()       — Inicia OAuth (scopes: calendar + spreadsheets)
  callback()       — Guarda tokens encriptados + metadata {email, avatar}
  disconnect()     — Revoca integración (cliente)
  adminRevoke()    — Revoca integración (admin)

# APIs INTERNAS PARA N8N
app/Http/Controllers/Api/Donna/DonnaTelegramController.php
  register()       — Vincula telegram_chat_id con activation_code

app/Http/Controllers/Api/Donna/DonnaContextController.php
  show()           — Devuelve contexto completo al AI Agent

app/Http/Controllers/Api/Donna/Tools/DonnaCalendarToolController.php
  listEvents / freebusy / createEvent / updateEvent / deleteEvent

app/Http/Controllers/Api/Donna/Tools/DonnaSheetsToolController.php
  getTasks / createTask / updateTask / completeTask
  resolveContext() — Auto-crea spreadsheet si no existe (clientes legados)
  sheetsError()    — Reset spreadsheet_id en 404 para recrear en próximo intento

# LÓGICA DE NEGOCIO
app/Services/Donna/DonnaServiceValidator.php    — Valida acceso multi-tenant
app/Services/Donna/DonnaPersonalContextService.php — Construye el JSON de contexto
app/Services/Donna/DonnaToolLogger.php          — Logs sanitizados de herramientas

app/Services/Donna/Google/DonnaGoogleTokenService.php   — Gestión de tokens OAuth
app/Services/Donna/Google/DonnaCalendarService.php      — Operaciones Calendar API
app/Services/Donna/Google/DonnaSheetsTaskService.php    — Operaciones Sheets API
app/Services/Donna/Google/DonnaSpreadsheetSetupService.php — Creación hoja inicial

# VISTAS CLIENTE
resources/views/donna.blade.php                 — Página pública + contratar + vincular Google
resources/views/shopping/historialCliente.blade.php (tab "Donna AI")
  — Estado de Google, canal Telegram, suscripción, personalización del agente

# VISTAS ADMIN
resources/views/donna/planes/          — CRUD planes
resources/views/donna/suscripciones/   — Listado + renovar/suspender (con foto Google)
resources/views/donna/solicitudes/     — Aprobar/rechazar solicitudes
```

---

## 13. Diferencias Clave: Personal vs Business (referencia para implementación)

| Aspecto | Donna Personal (implementado) | Donna Business (pendiente) |
|---|---|---|
| **Canal** | Telegram Bot | WhatsApp (Evolution API) |
| **Quien habla** | Solo el dueño (owner) | Clientes externos del negocio |
| **Tenant** | 1 usuario = 1 suscripción | 1 negocio = N clientes finales |
| **Identificación** | telegram_chat_id fijo del dueño | Número de teléfono del cliente final |
| **Memoria** | session_key por chat_id | session_key por número de teléfono |
| **Contexto** | personal_context del dueño | business_context: precios, productos, horarios |
| **Prompt** | Secretaria personal con reglas de agenda | Agente de atención al cliente con base de conocimiento |
| **Google** | Calendar + Sheets del dueño | Calendar del negocio (puede ser compartido) |
| **Escalado** | No aplica | Escala a humano cuando no puede resolver |
| **Activación canal** | Código 6 chars → bot Telegram | instance_name en Evolution API |
| **Horarios** | Puede ser 24/7 | `working_hours_json` con horarios de atención |
| **Spreadsheet** | Lista de tareas personales | CRM de leads, registro de atenciones |
| **Setup automático** | `DonnaSpreadsheetSetupService` | Crear spreadsheet CRM con columnas de negocio |

**Campos de `donna_agent_configs` que Business usará y Personal no:**
- `business_name`, `business_description`, `business_context`, `business_logic`
- `welcome_message`, `out_of_hours_message`, `human_handoff_msg`
- `working_hours_json` (objeto con horarios por día de la semana)
- `fallback_prompt`

**Nuevo flujo de activación para Business:**
1. Cliente activa → se crea `DonnaChannel {channel_type: whatsapp, provider: evolution_api}`
2. Admin (o sistema) crea instancia en Evolution API
3. Se vincula `instance_name` al canal
4. Se genera QR para conectar WhatsApp del negocio
5. Una vez escaneado → canal activo

---

## 14. Checklist de Estado Actual

- [x] Tablas BD (9 migraciones)
- [x] Modelos con relaciones y accessors
- [x] OAuth Google (connect / callback / disconnect / adminRevoke)
- [x] Activación autoservicio con descuento de saldo
- [x] Solicitud cuando no hay saldo
- [x] Aprobación/rechazo admin
- [x] Generación automática de código de activación Telegram
- [x] Registro del chat_id de Telegram vía API
- [x] Endpoint GET /api/donna/context
- [x] Herramientas Calendar (5 operaciones)
- [x] Herramientas Sheets (4 operaciones)
- [x] Creación automática del spreadsheet "Lista de Tareas" al activar
- [x] Auto-reparación del spreadsheet si no existe (clientes legados o si fue borrado)
- [x] Sistema de prompts personalizable por el cliente
- [x] Vista previa del system message en tiempo real
- [x] Logs de herramientas (donna_tool_logs)
- [x] Panel admin: planes, suscripciones (con foto Google), solicitudes
- [x] Workflow n8n completo e importable
- [x] Refresco automático de token Google (OAuth refresh_token)
- [ ] Renovación automática de suscripciones vencidas (cron pendiente)
- [ ] Notificaciones por email cuando la suscripción está por vencer
- [ ] Donna Business
