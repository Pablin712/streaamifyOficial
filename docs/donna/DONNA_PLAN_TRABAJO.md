# Plan de Trabajo — Donna SaaS en Streamify

**Versión:** 1.0  
**Fecha inicio:** 2026-05-26  
**Responsable:** Pablo Jiménez / Claude Code  
**Stack:** Laravel 11, Bootstrap 5, Blade, Spatie Permission, Eloquent, n8n, Evo API

---

## Índice

1. [Visión general](#1-visión-general)
2. [Arquitectura de flujos](#2-arquitectura-de-flujos)
3. [Fases y épicas](#3-fases-y-épicas)
4. [Historias de usuario detalladas](#4-historias-de-usuario-detalladas)
5. [Modelo de datos](#5-modelo-de-datos)
6. [Endpoints API](#6-endpoints-api)
7. [Convenciones del proyecto](#7-convenciones-del-proyecto)
8. [Criterios de aceptación globales](#8-criterios-de-aceptación-globales)
9. [Orden de implementación con Claude Code](#9-orden-de-implementación-con-claude-code)

---

## 1. Visión general

Donna es un módulo SaaS multi-cliente dentro de Streamify. Se vende como un servicio independiente del streaming.

Existen dos productos Donna:

| Plan | Descripción | Destinatario |
|------|-------------|--------------|
| **Donna Personal** | Secretaria privada del dueño del negocio | El dueño habla con Donna directamente |
| **Donna Business** | Asesora que atiende a los clientes finales del negocio | Los clientes finales hablan con Donna |

### Dos formas de contratar

```
Flujo A — Con intervención de empleado (pedido)
  Cliente pide Donna por WhatsApp / panel
  → Empleado crea la suscripción
  → Empleado configura Donna
  → Donna activa

Flujo B — Autoservicio del cliente
  Cliente ve precios en /donna
  → Cliente elige plan y paga (recarga de saldo)
  → Streamify activa suscripción automáticamente
  → Cliente configura lo básico desde su panel
  → Empleado revisa y completa la configuración
```

---

## 2. Arquitectura de flujos

### 2.1 Flujo de mensaje (runtime Donna)

```
WhatsApp / Telegram
      ↓
Evo API / Telegram Bot
      ↓
Webhook → n8n
      ↓
POST /api/donna/ingest ← Streamify valida todo aquí
      ↓
¿allowed?
  NO → guardar mensaje como blocked, no llamar IA
  SÍ → GET /api/donna/context
      ↓
n8n carga prompt + contexto + herramientas
      ↓
OpenAI / DeepSeek genera respuesta
      ↓
Herramientas: Calendar, Sheets, Knowledge Base
      ↓
Enviar respuesta por Evo API / Telegram
      ↓
POST /api/donna/respond ← Streamify guarda respuesta
```

### 2.2 Flujo de configuración (admin)

```
Empleado define precios de planes Donna (como productos)
      ↓
Cliente ve precios en /donna (dinámico desde BD)
      ↓
Cliente solicita o paga
      ↓
Empleado activa suscripción en panel Donna Hub
      ↓
Empleado (o cliente) configura:
  - Canal WhatsApp / Telegram
  - Prompt del agente
  - Contexto del negocio
  - Google OAuth
  - Base de conocimiento
      ↓
Donna operativa
```

---

## 3. Fases y épicas

| Fase | Épica | Prioridad | Estado |
|------|-------|-----------|--------|
| 1 | Planes Donna con precios (admin) | CRÍTICA | Pendiente |
| 2 | Vista pública /donna con precios dinámicos | CRÍTICA | Hecho parcial |
| 3 | Suscripciones de clientes | CRÍTICA | Pendiente |
| 4 | Panel Donna Hub (admin) | ALTA | Pendiente |
| 5 | Configuración del agente (admin) | ALTA | Pendiente |
| 6 | Canales WhatsApp / Telegram (admin) | ALTA | Pendiente |
| 7 | Integraciones Google OAuth | ALTA | Pendiente |
| 8 | API para n8n | ALTA | Pendiente |
| 9 | Panel cliente autoservicio | MEDIA | Pendiente |
| 10 | Base de conocimiento | MEDIA | Pendiente |
| 11 | Historial de conversaciones (admin) | MEDIA | Pendiente |
| 12 | Ingest de mensajes + middleware SaaS | ALTA | Pendiente |

---

## 4. Historias de usuario detalladas

---

### FASE 1 — Planes Donna con precios (admin)

#### HU-01 — Empleado crea plan Donna

**Como** empleado con permiso `donna.planes`,  
**quiero** crear un plan Donna (Personal o Business) con nombre, precio, descripción y características,  
**para** que el precio se refleje dinámicamente en la página pública `/donna`.

**Criterios de aceptación:**
- [ ] Existe una vista en `/admin/donna/planes` accesible con permiso `donna.planes`
- [ ] El formulario incluye: nombre, código, tipo (`personal` | `business`), precio, descripción, lista de características, estado (activo/inactivo)
- [ ] El precio acepta decimales (ej. `19.99`)
- [ ] Se puede subir una imagen/ícono del plan (opcional)
- [ ] El plan creado aparece inmediatamente en `/donna`
- [ ] Se registra en tabla `historial` con el empleado que creó el plan

**Campos del formulario:**
```
nombre          → texto libre (ej: "Donna Personal")
codigo          → texto único (ej: donna_personal)
tipo            → select: personal | business
precio          → decimal (ej: 19.99)
moneda          → USD (fijo)
billing_cycle   → select: mensual | anual | único
descripcion     → textarea
caracteristicas → lista dinámica (igual que detalles en productos)
activo          → toggle
```

---

#### HU-02 — Empleado edita y desactiva plan Donna

**Como** empleado,  
**quiero** editar el precio y características de un plan Donna,  
**para** mantener los precios actualizados sin tocar código.

**Criterios de aceptación:**
- [ ] Botón "Editar" en la tabla de planes abre modal con datos precargados
- [ ] Al guardar, el cambio de precio se refleja en `/donna` en menos de 1 segundo
- [ ] Si el plan se desactiva (`activo = false`), desaparece de la vista pública
- [ ] No se puede eliminar un plan si tiene suscripciones activas (solo desactivar)

---

#### HU-03 — Vista pública /donna con precios dinámicos

**Como** visitante o cliente,  
**quiero** ver los precios reales de Donna Personal y Donna Business,  
**para** decidir cuál contratar.

**Criterios de aceptación:**
- [ ] Los precios se consultan desde la tabla `donna_plans` (no hardcodeados)
- [ ] Si solo hay un plan activo de cada tipo, se muestra solo ese
- [ ] Si no hay planes activos, se muestra "Consultar precio" (fallback)
- [ ] Botón "Contratar" lleva al login/registro si no está autenticado, o al proceso de pago si sí lo está
- [ ] Los planes se ordenan: Personal primero, Business segundo

---

### FASE 2 — Suscripciones de clientes

#### HU-04 — Cliente solicita Donna (flujo con empleado)

**Como** cliente autenticado,  
**quiero** solicitar el servicio Donna desde mi panel o desde `/donna`,  
**para** que el empleado lo active y configure.

**Criterios de aceptación:**
- [ ] Botón "Solicitar Donna" en `/donna` (visible si el cliente está logueado)
- [ ] Al hacer clic, se crea un registro de solicitud en la tabla `donna_requests` con estado `pending`
- [ ] El empleado recibe notificación (badge en sidebar o email)
- [ ] El empleado puede aprobar/rechazar la solicitud desde `/admin/donna/solicitudes`
- [ ] Al aprobar, se crea automáticamente la suscripción en `donna_subscriptions`

---

#### HU-05 — Cliente paga y activa Donna (autoservicio)

**Como** cliente autenticado con saldo suficiente,  
**quiero** contratar Donna directamente pagando con mi saldo,  
**para** que el servicio se active sin esperar al empleado.

**Criterios de aceptación:**
- [ ] Si el cliente tiene saldo >= precio del plan, aparece botón "Activar ahora"
- [ ] Si no tiene saldo, aparece "Recargar saldo" con enlace a `/cliente/recargar-saldo`
- [ ] Al confirmar, se descuenta el saldo y se crea la suscripción con estado `active`
- [ ] Se registra la transacción en la tabla `historial` o equivalente
- [ ] El cliente recibe un mensaje de confirmación en pantalla y opcionalmente por WhatsApp
- [ ] La suscripción dura el período del plan (ej. 30 días si es mensual)

---

#### HU-06 — Empleado activa suscripción manualmente

**Como** empleado,  
**quiero** crear una suscripción Donna para un cliente directamente desde el panel,  
**para** los casos en que el cliente paga por otro medio (transferencia, efectivo).

**Criterios de aceptación:**
- [ ] Formulario en `/admin/donna/suscripciones/create` con: cliente (selector), plan, fecha inicio, fecha vencimiento, notas
- [ ] La suscripción queda en estado `active` inmediatamente si se activa manualmente
- [ ] El empleado puede seleccionar período personalizado (no solo el del plan)
- [ ] Se registra en historial con nombre del empleado

---

#### HU-07 — Empleado ve y gestiona suscripciones

**Como** empleado,  
**quiero** ver todas las suscripciones Donna con su estado,  
**para** saber quién tiene el servicio activo, vencido o pendiente.

**Criterios de aceptación:**
- [ ] Tabla en `/admin/donna/suscripciones` con columnas: Cliente, Plan, Estado, Inicio, Vencimiento, Días restantes, Acciones
- [ ] Filtros por: estado (active/expired/suspended), tipo de plan, cliente
- [ ] Acción "Renovar" extiende el vencimiento
- [ ] Acción "Suspender" cambia estado a `suspended` y desactiva el canal
- [ ] Badge de colores por estado: verde=active, naranja=por vencer (≤7 días), rojo=expired

---

### FASE 3 — Panel Donna Hub (admin)

#### HU-08 — Vista principal Donna Hub

**Como** empleado,  
**quiero** ver un dashboard de Donna Hub con métricas clave,  
**para** tener una visión general del estado del servicio.

**Criterios de aceptación:**
- [ ] Ruta: `/admin/donna` (o `/admin/donna/hub`)
- [ ] Cards con: total clientes Donna, activos, vencidos, Donna Personal vs Business
- [ ] Lista de suscripciones próximas a vencer (≤7 días)
- [ ] Lista de últimos errores de canal
- [ ] Accesos rápidos: nueva suscripción, nueva solicitud, ver canales

---

#### HU-09 — Empleado agrega enlace Donna al sidebar

**Como** empleado con permiso `donna.hub`,  
**quiero** ver "Donna Hub" en el menú lateral del panel admin,  
**para** acceder rápidamente al módulo.

**Criterios de aceptación:**
- [ ] Sección "Donna" en el sidebar con sub-items:
  - Dashboard (Donna Hub)
  - Planes
  - Suscripciones
  - Solicitudes
  - Canales
  - Configuraciones
  - Chats
- [ ] Solo visible para empleados con permiso `donna.hub`
- [ ] Icono: `bi-robot`

---

### FASE 4 — Configuración del agente Donna (admin)

#### HU-10 — Empleado configura el agente Donna de un cliente

**Como** empleado,  
**quiero** configurar el prompt, contexto y reglas de Donna para cada cliente,  
**para** que Donna responda correctamente según el negocio del cliente.

**Criterios de aceptación:**
- [ ] Vista en `/admin/donna/suscripciones/{id}/config`
- [ ] Formulario con:
  - Nombre del agente (ej. "Donna", "Ana", "Sofía")
  - Nombre del negocio
  - Descripción del negocio (qué hace, qué vende)
  - Contexto personal (solo para Donna Personal)
  - Contexto del negocio (para Donna Business)
  - Lógica de negocio (reglas especiales)
  - Prompt principal (textarea grande)
  - Mensaje de bienvenida
  - Mensaje fallback (cuando no sabe responder)
  - Mensaje fuera de horario
  - Tono: select (formal, amable, cercano, profesional)
  - Idioma: select (es, en)
  - Zona horaria: select
  - Horario de atención: JSON o formulario por días
  - Mensaje de escalado a humano
  - Estado activo/inactivo
- [ ] Al guardar se actualiza la tabla `donna_agent_configs`
- [ ] Incluye botón "Probar prompt" (futuro)

---

### FASE 5 — Canales WhatsApp / Telegram (admin)

#### HU-11 — Empleado registra un canal WhatsApp para un cliente

**Como** empleado,  
**quiero** registrar la instancia de Evo API que usará Donna Business de un cliente,  
**para** que los mensajes lleguen correctamente al sistema.

**Criterios de aceptación:**
- [ ] Formulario en `/admin/donna/canales/create` con:
  - Cliente (select)
  - Suscripción asociada (filtra por cliente)
  - Tipo de canal: WhatsApp | Telegram
  - Proveedor: evolution_api | telegram_bot
  - Nombre de instancia Evo API (instance_name)
  - Número de teléfono asociado
  - URL base de Evo API
  - API Key (se guarda cifrada, se muestra enmascarada `sk-****abcd`)
  - Webhook URL (auto-generada o ingresada)
  - Estado: activo/inactivo
- [ ] La API key nunca se muestra completa en pantalla
- [ ] Validación: instance_name único en el sistema

---

#### HU-12 — Empleado registra canal Telegram personal

**Como** empleado,  
**quiero** configurar un canal Telegram para Donna Personal de un cliente,  
**para** que el dueño del negocio pueda hablar con Donna desde Telegram.

**Criterios de aceptación:**
- [ ] Mismo formulario que HU-11 con tipo=Telegram
- [ ] Campo extra: `owner_identifier` (chat_id o username de Telegram del dueño)
- [ ] Campo: Telegram Bot Token (cifrado)
- [ ] Solo el `owner_identifier` puede activar herramientas de Donna Personal

---

### FASE 6 — Integraciones Google OAuth (admin/cliente)

#### HU-13 — Cliente conecta Google desde su panel

**Como** cliente con Donna activa,  
**quiero** conectar mi cuenta de Google,  
**para** que Donna pueda usar mi Google Calendar y Google Sheets.

**Criterios de aceptación:**
- [ ] Botón "Conectar Google" en el panel del cliente (sección Donna)
- [ ] Redirige al flujo OAuth de Google (con scopes: Calendar, Sheets)
- [ ] Al completar OAuth, los tokens se guardan cifrados en `donna_integrations`
- [ ] Jamás se pide usuario/contraseña de Google
- [ ] En el panel, aparece estado: "Google Calendar: Conectado ✓" o "Desconectado"
- [ ] Botón "Desconectar" revoca el token y lo elimina

---

#### HU-14 — Refresh automático de tokens Google

**Como** sistema,  
**quiero** refrescar el access token de Google automáticamente antes de usarlo,  
**para** que Donna no falle por token expirado.

**Criterios de aceptación:**
- [ ] Antes de llamar Google Calendar o Sheets, verificar `token_expires_at`
- [ ] Si está vencido, usar `refresh_token` para obtener uno nuevo
- [ ] Si el refresh falla, marcar la integración como `error` y no ejecutar la herramienta
- [ ] Donna responde informando que Calendar no está disponible en vez de fallar silenciosamente

---

### FASE 7 — API para n8n

#### HU-15 — n8n obtiene contexto completo de Donna

**Como** flujo n8n,  
**quiero** consultar a Streamify el contexto completo para un mensaje entrante,  
**para** cargar dinámicamente el prompt, reglas y herramientas del cliente correcto.

**Endpoint:** `GET /api/donna/context`

**Criterios de aceptación:**
- [ ] Acepta parámetros: `provider`, `instance_name`, `sender_identifier`
- [ ] Devuelve `allowed: true/false`
- [ ] Si `allowed: true` devuelve: client, service, channel, agent_config, tools disponibles
- [ ] Si `allowed: false` devuelve: reason (service_expired, channel_inactive, unauthorized, etc.)
- [ ] Respuesta en < 200ms (sin llamadas externas, todo desde BD local)
- [ ] Protegido por API key interna (`X-Donna-Key` header)

---

#### HU-16 — n8n registra mensaje entrante

**Como** flujo n8n,  
**quiero** registrar cada mensaje que llega en Streamify antes de procesarlo,  
**para** tener historial completo y poder auditar.

**Endpoint:** `POST /api/donna/ingest`

**Criterios de aceptación:**
- [ ] Guarda el mensaje en `donna_messages` con estado `received`
- [ ] Crea o recupera la conversación en `donna_conversations`
- [ ] Devuelve: `stored`, `allowed`, `conversation_id`, `message_id`, `service_type`
- [ ] Si `allowed: false`, guarda mensaje con estado `blocked_service_inactive`
- [ ] Funciona aunque el servicio esté vencido (para auditoría)

---

#### HU-17 — n8n registra respuesta generada

**Como** flujo n8n,  
**quiero** registrar la respuesta que Donna envió al usuario,  
**para** tener el historial completo de conversación.

**Endpoint:** `POST /api/donna/respond`

**Criterios de aceptación:**
- [ ] Guarda mensaje saliente en `donna_messages` con `direction: outbound`
- [ ] Actualiza `last_message_at` y `last_message_preview` en la conversación
- [ ] Si se envía `provider_message_id`, lo guarda para trazabilidad

---

#### HU-18 — Middleware SaaS: validar servicio antes de procesar

**Como** sistema,  
**quiero** validar que el cliente tiene el servicio activo, habilitado y no vencido antes de procesar cualquier mensaje,  
**para** que Donna no consuma créditos de IA si el cliente no pagó.

**Criterios de aceptación:**
- [ ] La clase `DonnaServiceValidator` verifica en orden:
  1. Canal existe
  2. Cliente existe y está activo
  3. Suscripción existe
  4. Suscripción está en estado `active`
  5. Fecha actual <= `expires_at`
  6. Canal está en estado `active`
  7. Si Donna Personal: sender == owner_identifier
- [ ] Si alguna condición falla, devuelve `allowed: false` con `reason`
- [ ] Si todas pasan, devuelve `allowed: true` con contexto completo

---

### FASE 8 — Panel cliente autoservicio

#### HU-19 — Cliente ve su suscripción Donna desde su panel

**Como** cliente autenticado,  
**quiero** ver el estado de mi suscripción Donna desde mi panel,  
**para** saber cuánto tiempo me queda y qué servicios tengo activos.

**Criterios de aceptación:**
- [ ] Nueva sección "Donna" en el panel del cliente (sidebar o tabs)
- [ ] Muestra: tipo de plan, estado, fecha de vencimiento, días restantes
- [ ] Muestra estado de integraciones: Google Calendar, Google Sheets, Canal WhatsApp
- [ ] Botón "Renovar" lleva al proceso de pago (si tiene saldo, desencadena directamente)
- [ ] Si no tiene suscripción activa, muestra CTA para contratar

---

#### HU-20 — Cliente configura datos básicos de su negocio

**Como** cliente autenticado con Donna Business activa,  
**quiero** completar los datos básicos de mi negocio desde mi panel,  
**para** que Donna tenga contexto de qué hace mi empresa.

**Criterios de aceptación:**
- [ ] Formulario simplificado (sin prompts técnicos): nombre del negocio, descripción, horario, teléfono
- [ ] Los datos se guardan en `donna_agent_configs`
- [ ] El empleado puede ver y completar la configuración avanzada desde el panel admin
- [ ] Al guardar aparece confirmación: "Datos guardados. El equipo completará tu configuración pronto."

---

### FASE 9 — Base de conocimiento (Donna Business)

#### HU-21 — Empleado crea base de conocimiento para un cliente

**Como** empleado,  
**quiero** agregar preguntas frecuentes, precios, políticas y documentos a la base de conocimiento de Donna Business,  
**para** que Donna pueda responder correctamente a los clientes finales.

**Criterios de aceptación:**
- [ ] Vista en `/admin/donna/suscripciones/{id}/conocimiento`
- [ ] Tipos de item: `text` (pregunta/respuesta), `faq`, `url`, `pdf`, `table`
- [ ] Editor de texto simple para ítems de tipo `text` y `faq`
- [ ] Upload de PDF (se guarda en storage, se extrae texto para indexar)
- [ ] Cada item tiene: título, contenido, tipo, estado
- [ ] Los ítems se asocian siempre a `client_id` y `service_id` (nunca se mezclan)

---

#### HU-22 — Cliente agrega ítems de conocimiento desde su panel

**Como** cliente con Donna Business,  
**quiero** agregar información sobre mi negocio desde mi panel,  
**para** mantener a Donna actualizada sin pedirle al empleado.

**Criterios de aceptación:**
- [ ] Formulario simplificado: tipo FAQ (pregunta + respuesta) o texto libre
- [ ] Límite de ítems según el plan contratado
- [ ] Los ítems recién agregados quedan en estado `pending` hasta que el empleado los revise (o activos si el plan lo permite)

---

### FASE 10 — Historial de conversaciones (admin)

#### HU-23 — Empleado ve conversaciones de Donna por cliente

**Como** empleado,  
**quiero** ver el historial de conversaciones de Donna de cada cliente,  
**para** monitorear la calidad de las respuestas y detectar errores.

**Criterios de aceptación:**
- [ ] Vista en `/admin/donna/chats` con filtros: cliente, tipo de servicio, canal, estado, fecha
- [ ] Listado de conversaciones con: nombre/teléfono del contacto, último mensaje, estado, fecha
- [ ] Al hacer clic en una conversación, se ve el hilo completo (mensajes entrantes y salientes)
- [ ] Indicadores visuales por estado de mensaje: received, processing, responded, failed, blocked
- [ ] Botón "Tomar atención" cambia la conversación a `human_takeover`
- [ ] Botón "Pausar Donna" desactiva temporalmente la IA en esa conversación

---

### FASE 11 — Herramientas Google Calendar y Sheets (API)

#### HU-24 — n8n crea evento en Google Calendar del cliente

**Endpoint:** `POST /api/donna/tools/google-calendar/create-event`

**Criterios de aceptación:**
- [ ] Recibe: client_id, service_id, summary, start, end, attendees (opcional)
- [ ] Busca la integración Google del service_id
- [ ] Refresca el token si está vencido
- [ ] Crea el evento via Google Calendar API
- [ ] Devuelve: event_id, link del evento
- [ ] Si falla, devuelve error claro para que n8n lo maneje

---

#### HU-25 — n8n consulta disponibilidad en Google Calendar

**Endpoint:** `POST /api/donna/tools/google-calendar/freebusy`

**Criterios de aceptación:**
- [ ] Recibe: client_id, service_id, date_from, date_to
- [ ] Devuelve bloques libres y ocupados del calendario
- [ ] n8n usa esto para ofrecer horarios disponibles al usuario final

---

#### HU-26 — n8n agrega fila en Google Sheets del cliente

**Endpoint:** `POST /api/donna/tools/google-sheets/append-row`

**Criterios de aceptación:**
- [ ] Recibe: client_id, service_id, spreadsheet_id, sheet_name, values[]
- [ ] Agrega fila al final de la hoja indicada
- [ ] Devuelve confirmación con el rango insertado

---

## 5. Modelo de datos

### Tablas nuevas a crear

#### `donna_plans`
```sql
id                 PK
code               string(50) unique         -- donna_personal, donna_business
name               string(100)               -- Donna Personal, Donna Business
service_type       enum(personal, business)
description        text nullable
price              decimal(10,2)
currency           string(3) default 'USD'
billing_cycle      enum(monthly, yearly, one_time) default monthly
features_json      json nullable             -- lista de características
icon               string(255) nullable      -- ruta de imagen
is_active          boolean default 1
sort_order         integer default 0
created_at
updated_at
```

#### `donna_subscriptions`
```sql
id                 PK
client_id          FK → clientes.idcli
plan_id            FK → donna_plans.id
service_type       enum(personal, business)
status             enum(pending, active, suspended, expired, cancelled)
billing_cycle      string
price_paid         decimal(10,2)
currency           string(3)
starts_at          datetime nullable
expires_at         datetime nullable
last_payment_at    datetime nullable
next_payment_due   date nullable
is_enabled         boolean default 1
suspended_reason   text nullable
notes              text nullable             -- notas del empleado
activated_by       FK → empleados.idemp nullable
created_at
updated_at
```

#### `donna_requests`
```sql
id                 PK
client_id          FK → clientes.idcli
plan_id            FK → donna_plans.id
status             enum(pending, approved, rejected)
message            text nullable             -- mensaje del cliente
employee_notes     text nullable
reviewed_by        FK → empleados.idemp nullable
reviewed_at        datetime nullable
created_at
updated_at
```

#### `donna_channels`
```sql
id                 PK
client_id          FK → clientes.idcli
subscription_id    FK → donna_subscriptions.id
service_type       enum(personal, business)
channel_type       enum(whatsapp, telegram)
provider           string(50)               -- evolution_api, telegram_bot
instance_name      string(100) unique       -- nombre en Evo API
phone_number       string(50) nullable
owner_identifier   string(100) nullable     -- para Donna Personal
audience_type      enum(owner, final_customer)
api_base_url       string(255) nullable
api_key_encrypted  text nullable
webhook_url        string(255) nullable
status             enum(active, inactive, error, suspended)
is_default         boolean default 0
last_connected_at  datetime nullable
last_error         text nullable
metadata_json      json nullable
created_at
updated_at
```

#### `donna_agent_configs`
```sql
id                   PK
client_id            FK → clientes.idcli
subscription_id      FK → donna_subscriptions.id
service_type         enum(personal, business)
agent_name           string(50) default 'Donna'
business_name        string(150) nullable
business_description text nullable
personal_context     text nullable
business_context     text nullable
business_logic       text nullable
main_prompt          text nullable
fallback_prompt      text nullable
welcome_message      text nullable
out_of_hours_message text nullable
tone                 string(30) default 'amable'
language             string(5) default 'es'
timezone             string(50) default 'America/Guayaquil'
working_hours_json   json nullable
human_handoff_msg    text nullable
is_active            boolean default 1
created_at
updated_at
```

#### `donna_integrations`
```sql
id                       PK
client_id                FK → clientes.idcli
subscription_id          FK → donna_subscriptions.id
service_type             enum(personal, business)
integration_type         string(50)           -- google_calendar, google_sheets, openai, deepseek
name                     string(100)
credentials_encrypted    text nullable
access_token_encrypted   text nullable
refresh_token_encrypted  text nullable
token_expires_at         datetime nullable
scopes_json              json nullable
status                   enum(active, expired, revoked, error) default active
last_sync_at             datetime nullable
last_error               text nullable
metadata_json            json nullable
created_at
updated_at
```

#### `donna_knowledge_bases`
```sql
id              PK
client_id       FK → clientes.idcli
subscription_id FK → donna_subscriptions.id
name            string(100)
description     text nullable
status          enum(active, inactive) default active
created_at
updated_at
```

#### `donna_knowledge_items`
```sql
id                  PK
knowledge_base_id   FK → donna_knowledge_bases.id
client_id           FK → clientes.idcli
subscription_id     FK → donna_subscriptions.id
type                enum(text, faq, pdf, url, table)
source_title        string(200) nullable
content_text        longtext nullable
file_path           string(255) nullable
metadata_json       json nullable
status              enum(active, inactive, pending) default active
created_at
updated_at
```

#### `donna_conversations`
```sql
id                    PK
client_id             FK → clientes.idcli
subscription_id       FK → donna_subscriptions.id
channel_id            FK → donna_channels.id
service_type          enum(personal, business)
conversation_type     enum(owner_chat, final_customer_chat)
external_chat_id      string(200) nullable
sender_identifier     string(100) nullable
sender_name           string(100) nullable
status                enum(open, closed, pending, human_takeover, blocked)
last_message_at       datetime nullable
last_message_preview  string(300) nullable
assigned_to           FK → empleados.idemp nullable
metadata_json         json nullable
created_at
updated_at
```

#### `donna_messages`
```sql
id                    PK
conversation_id       FK → donna_conversations.id
client_id             FK → clientes.idcli
subscription_id       FK → donna_subscriptions.id
channel_id            FK → donna_channels.id nullable
direction             enum(inbound, outbound)
message_type          enum(text, audio, image, video, document, sticker, system)
provider_message_id   string(200) nullable
content_text          text nullable
media_url             string(500) nullable
transcription_text    text nullable
ai_response_text      text nullable
processing_status     enum(received, stored, processing, responded, failed, blocked_service_inactive)
blocked_reason        string(100) nullable
raw_payload_json      json nullable
created_at
updated_at
```

---

## 6. Endpoints API

| Método | URL | Descripción | HU |
|--------|-----|-------------|-----|
| GET | `/api/donna/context` | Contexto completo del agente | HU-15 |
| POST | `/api/donna/ingest` | Registrar mensaje entrante | HU-16 |
| POST | `/api/donna/respond` | Registrar respuesta saliente | HU-17 |
| GET | `/api/donna/service-status` | Estado del servicio | HU-15 |
| POST | `/api/donna/tools/google-calendar/create-event` | Crear evento | HU-24 |
| POST | `/api/donna/tools/google-calendar/freebusy` | Disponibilidad | HU-25 |
| POST | `/api/donna/tools/google-sheets/append-row` | Agregar fila | HU-26 |
| GET | `/api/donna/knowledge/search` | Buscar en base de conocimiento | Futuro |

**Autenticación API:** Header `X-Donna-Key: {api_key}` (key configurada en `.env`)

---

## 7. Convenciones del proyecto

| Aspecto | Patrón a seguir |
|---------|-----------------|
| **Rutas admin** | `/admin/donna/*` bajo middleware `auth` |
| **Permisos** | `donna.hub`, `donna.planes`, `donna.suscripciones`, `donna.canales`, `donna.chats` |
| **Vistas admin** | `resources/views/donna/` (como `inventory/productos/`) |
| **Layout** | Extender `layouts.navigation` (igual que todas las vistas admin) |
| **Sidebar** | Agregar sección "Donna" en `partials/sidebar.blade.php` |
| **Models** | `app/Models/Donna/DonnaPlan.php`, `DonnaSubscription.php`, etc. |
| **Controllers** | `app/Http/Controllers/Donna/DonnaPlanController.php`, etc. |
| **Historial** | Registrar en tabla `historial` igual que productos |
| **Cifrado** | `Crypt::encryptString()` para tokens y API keys |
| **Layout cliente** | Extender `layouts.cliente` para vistas del cliente |
| **Rutas cliente** | `/cliente/donna/*` bajo middleware `AuthCliente` |
| **Colores** | Amarillo `#E4B100`, Rojo `#D41216`, Azul `#274698`, Negro `#1D1D1B` |

---

## 8. Criterios de aceptación globales

La implementación se considera completa cuando:

```
[ ] Un empleado puede crear y editar planes Donna con precio
[ ] El precio de los planes aparece dinámico en /donna (sin hardcodear)
[ ] Un cliente puede solicitar Donna y el empleado puede aprobar
[ ] Un cliente puede autocontratar Donna si tiene saldo
[ ] Un empleado puede configurar el agente (prompt, contexto, reglas)
[ ] Un empleado puede registrar el canal WhatsApp o Telegram
[ ] Un cliente puede conectar su Google desde el panel
[ ] n8n puede consultar el contexto con GET /api/donna/context
[ ] n8n puede registrar mensajes con POST /api/donna/ingest
[ ] Donna no responde si el servicio está vencido
[ ] Donna no responde si el canal está inactivo
[ ] Donna Personal solo responde al dueño autorizado
[ ] Las credenciales siempre se guardan cifradas
[ ] Los datos de cada cliente están completamente aislados
[ ] El empleado puede ver el historial de conversaciones
```

---

## 9. Orden de implementación con Claude Code

> Seguir este orden exacto. No avanzar una fase si la anterior no cumple sus criterios de aceptación.

### Sprint 1 — Base visible (HU-01, HU-02, HU-03)
```
[ ] 1. Migración: tabla donna_plans
[ ] 2. Model: DonnaPlan
[ ] 3. Controller: DonnaPlanController (index, store, update, destroy)
[ ] 4. Vistas admin: donna/planes/index.blade.php (con modal create/edit como productos)
[ ] 5. Rutas admin: /admin/donna/planes
[ ] 6. Permiso: donna.planes en seeder/panel de roles
[ ] 7. Sidebar: agregar sección Donna
[ ] 8. Vista /donna: leer planes dinámicamente desde donna_plans
[ ] 9. Fallback en /donna si no hay planes activos
```

### Sprint 2 — Suscripciones (HU-04, HU-05, HU-06, HU-07)
```
[ ] 1. Migraciones: donna_subscriptions, donna_requests
[ ] 2. Models: DonnaSubscription, DonnaRequest
[ ] 3. Controller admin: DonnaSubscriptionController (CRUD)
[ ] 4. Controller admin: DonnaRequestController (index, approve, reject)
[ ] 5. Vistas admin: listado de suscripciones, crear suscripción manual
[ ] 6. Vista admin: listado de solicitudes con acciones
[ ] 7. Lógica de pago con saldo del cliente (similar a comprar producto)
[ ] 8. Ruta cliente: POST /cliente/donna/solicitar
[ ] 9. Botón "Solicitar Donna" en /donna (si autenticado)
[ ] 10. Botón "Activar con saldo" en /donna (si saldo >= precio)
```

### Sprint 3 — Donna Hub y configuración (HU-08, HU-09, HU-10)
```
[ ] 1. Vista admin: /admin/donna (dashboard con métricas)
[ ] 2. Cards de métricas (clientes activos, vencidos, Personal vs Business)
[ ] 3. Migración: donna_agent_configs
[ ] 4. Model: DonnaAgentConfig
[ ] 5. Controller: DonnaAgentConfigController
[ ] 6. Vista admin: formulario de configuración del agente por suscripción
[ ] 7. Sidebar: actualizar con todos los sub-items de Donna
```

### Sprint 4 — Canales (HU-11, HU-12)
```
[ ] 1. Migración: donna_channels
[ ] 2. Model: DonnaChannel
[ ] 3. Controller: DonnaChannelController (CRUD)
[ ] 4. Vistas admin: crear/editar canal
[ ] 5. Cifrado de api_key_encrypted con Crypt::encryptString()
[ ] 6. Enmascarar key en vista (mostrar solo últimos 4 caracteres)
[ ] 7. Validar instance_name único
```

### Sprint 5 — API para n8n (HU-15, HU-16, HU-17, HU-18)
```
[ ] 1. Migraciones: donna_conversations, donna_messages
[ ] 2. Models: DonnaConversation, DonnaMessage
[ ] 3. Servicio: DonnaServiceValidator (resolver + validar)
[ ] 4. Servicio: DonnaContextService (armar respuesta contexto)
[ ] 5. Controller API: DonnaApiController
[ ] 6. Rutas API: /api/donna/* (sin middleware web, con X-Donna-Key)
[ ] 7. Tests manuales con Postman/Insomnia
[ ] 8. Configurar n8n con los nuevos endpoints
```

### Sprint 6 — Google OAuth (HU-13, HU-14)
```
[ ] 1. Migración: donna_integrations
[ ] 2. Model: DonnaIntegration
[ ] 3. Configurar Google OAuth en Google Cloud Console (1 sola vez)
[ ] 4. Controller: DonnaGoogleController (redirect, callback)
[ ] 5. Servicio: DonnaGoogleTokenService (refresh automático)
[ ] 6. Vista cliente: botón "Conectar Google" en panel cliente
[ ] 7. Endpoints API herramientas: /api/donna/tools/google-calendar/*
[ ] 8. Endpoints API herramientas: /api/donna/tools/google-sheets/*
```

### Sprint 7 — Panel cliente y autoservicio (HU-19, HU-20)
```
[ ] 1. Sección "Donna" en panel del cliente
[ ] 2. Vista estado de suscripción (plan, estado, días restantes)
[ ] 3. Formulario básico de datos del negocio (solo campos simples)
[ ] 4. Botón "Renovar" con descuento de saldo
[ ] 5. Estado de integraciones (Google conectado/desconectado)
```

### Sprint 8 — Base de conocimiento (HU-21, HU-22)
```
[ ] 1. Migraciones: donna_knowledge_bases, donna_knowledge_items
[ ] 2. Models: DonnaKnowledgeBase, DonnaKnowledgeItem
[ ] 3. Controller admin: DonnaKnowledgeController
[ ] 4. Vistas admin: gestión de base de conocimiento
[ ] 5. Vista cliente: formulario simplificado para agregar FAQ
[ ] 6. Endpoint API: GET /api/donna/knowledge/search
```

### Sprint 9 — Historial de conversaciones (HU-23)
```
[ ] 1. Vista admin: /admin/donna/chats con filtros
[ ] 2. Vista detalle de conversación (hilo de mensajes)
[ ] 3. Acción "Tomar atención" (human_takeover)
[ ] 4. Acción "Pausar Donna" en conversación específica
[ ] 5. Indicadores de estado de mensajes con colores
```

### Sprint 10 — Pruebas piloto (HU de prueba del doc v2)
```
[ ] Demo A: cliente con Donna Personal (Telegram, Google Calendar)
[ ] Demo B: cliente con Donna Business (Evo API, base de conocimiento)
[ ] Probar todos los casos de bloqueo (vencido, canal inactivo, no autorizado)
[ ] Probar dos clientes simultáneos sin mezcla de datos
[ ] Documentar flujo n8n genérico final
```

---

## Notas técnicas importantes

- **No duplicar el flujo n8n por cliente.** Un solo flujo genérico que consulta Streamify.
- **Nunca guardar credenciales en texto plano.** Usar `Crypt::encryptString()` de Laravel.
- **Siempre filtrar por client_id + subscription_id.** Nunca queries sin estos filtros.
- **Los tokens de Google deben vivir en `donna_integrations`, no en la tabla de clientes.**
- **El campo `instance_name` de Evo API es la clave principal de resolución** de a qué cliente pertenece un mensaje.
- **Reutilizar el modelo `Cliente`** (tabla `clientes`, PK `idcli`) — no crear nueva tabla de usuarios.
- **Reutilizar el layout `navigation.blade.php`** para todas las vistas admin de Donna.
- **La lógica de pago con saldo** debe seguir el mismo patrón que `ShopController::comprar()`.

---

*Documento generado el 2026-05-26. Actualizar al finalizar cada sprint.*
