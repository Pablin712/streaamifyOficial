# Auditoría Donna Business — Streamify (2026-06-30)

Auditoría de código real (PHP + workflows n8n) del módulo Donna Business, hecha para evaluar si está listo para venderse como SaaS y activarse en el WhatsApp de distintos clientes.

Cubre dos capas que hay que leer **juntas**, no por separado:

1. **Backend Laravel** (`app/Http/Controllers/Api/Donna/Business/*`, `app/Services/Donna/*`, modelos, migraciones, vistas admin/cliente).
2. **Workflows n8n** (`docs/donna/*.json`) — el "cerebro" que orquesta WhatsApp ↔ Laravel ↔ DeepSeek/OpenAI ↔ Google. Esta capa **no vive en este repo como código ejecutable**, vive como exportación JSON de n8n. Si el JSON y el backend se desincronizan, Donna se rompe en producción sin que ningún test de Laravel lo detecte.

---

## 0. Inventario de archivos auditados

### Backend (PHP)
- Rutas: `routes/web.php` (admin/cliente), `routes/api.php` (n8n)
- Controllers Business: 12 archivos en `app/Http/Controllers/Api/Donna/Business/` (+ `Tools/`)
- Controllers admin/cliente: `DonnaPlanController`, `DonnaSubscriptionController`, `DonnaRequestController`, `DonnaDashboardController`, `DonnaGoogleController`, `ClienteDonnaController`, `ClienteDonnaKnowledgeController`
- Services: `app/Services/Donna/*` (incluye `DonnaServiceValidator`, `DonnaBusinessContextService`, `DonnaBusinessIngestService`, `DonnaKnowledgeService`, `DonnaToolLogger`, `Google/*`)
- Modelos: `app/Models/Donna*.php` (11 modelos)
- Migraciones: 17 archivos `*donna*` en `database/migrations/`
- Vistas: `resources/views/donna/**`, `resources/views/donna.blade.php`, `resources/views/shopping/historialCliente.blade.php` (panel cliente, ahí vive la config del agente)
- Middleware: `app/Http/Middleware/DonnaApiKeyMiddleware.php`
- Seeder: `database/seeders/RoleSeeder.php`

### Workflows n8n (JSON, carpeta `docs/donna/`)
| Archivo | Nodos | Estado real |
|---|---|---|
| `donna_business_whatsapp_streamify_template.json` | 32 | **Obsoleto / no usar.** Llama a `POST /api/donna/business/tools/media/image-ocr`, endpoint que **no existe** en `routes/api.php`. Es una versión anterior a v4. |
| `donna_business_whatsapp_streamify_template_v4_static_resolve.json` | 51 | **Es el que coincide con el backend actual** (probablemente el activo en producción, según confirmas). Internamente su sticky note dice "Cambios v5", o sea el archivo está adelantado a su propio nombre — falta renombrarlo/versionarlo bien. |
| `donna_personal_workflow_fixed.json` | 36 | Workflow de **Donna Personal** (Telegram), no Business. Coincide 100% con `routes/api.php:409-427`. |

No había revisado estos JSON en la primera pasada de la auditoría — esta sección lo corrige.

---

## 1. Paridad endpoints Laravel ↔ nodos n8n (Donna Business v4)

Cada `httpRequest`/`httpRequestTool` del JSON `v4_static_resolve` fue comparado 1 a 1 contra `routes/api.php:443-477`:

| Endpoint Laravel | Nodo n8n que lo llama | Coincide |
|---|---|---|
| `POST /donna/business/resolve-webhook` | `Resolver cliente/canal Streamify` | ✅ |
| `POST /donna/business/ingest` | `POST Ingest fromMe / Ignorar loop`, `POST Donna Business Ingest` | ✅ |
| `POST /donna/business/should-respond` | `¿Debo responder?` | ✅ |
| `GET /donna/business/context` | `GET Donna Business Context` | ✅ |
| `POST /donna/business/save-respond` | `POST Donna Business Save Respond` | ✅ |
| `PATCH /donna/business/messages/{id}/extractions` | **ninguno** | ❌ Ver hallazgo #3 |
| `tools/knowledge/search` | `donna_business_knowledge_search` | ✅ |
| `tools/memory/search` | `donna_business_memory_search` | ✅ |
| `tools/customer/context-search` | `donna_business_customer_context_search` | ✅ |
| `tools/data/query` | `donna_business_data_query` | ✅ |
| `tools/data/upsert-lead` | `donna_business_upsert_lead` | ✅ |
| `tools/calendar/list-events` | `donna_business_calendar_list_events` | ✅ |
| `tools/calendar/freebusy` | `donna_business_calendar_freebusy` | ✅ |
| `tools/calendar/create-event` | `donna_business_calendar_create_event` | ✅ |
| `tools/calendar/update-event` | `donna_business_calendar_update_event` | ✅ |
| `tools/calendar/delete-event` | `donna_business_calendar_delete_event` | ✅ |
| `tools/sheets/get-rows` | `donna_business_sheets_get_rows` | ✅ |
| `tools/sheets/append-row` | `donna_business_sheets_append_row` | ✅ |
| `tools/sheets/update-row` | `donna_business_sheets_update_row` | ✅ |

**18 de 19 endpoints Business tienen nodo correspondiente.** Es la paridad correcta para que el agente funcione: las 12 tools del AI Agent (`Nota v4 static config` lo confirma: "Tools disponibles (12 total)") están todas conectadas a su endpoint real.

Workflow Personal (`donna_personal_workflow_fixed.json`): paridad 100% contra `routes/api.php:409-427` (register-telegram, context, respond, calendar x5, sheets x4).

---

## 2. Hallazgo crítico — API key de producción hardcodeada en el JSON del repo

El valor literal `donna-secret-key-pablin-712` aparece **19 veces** en texto plano dentro de `donna_business_whatsapp_streamify_template_v4_static_resolve.json`, en el header `X-Donna-Key` de cada nodo HTTP (ingest, context, resolve-webhook, las 12 tools, should-respond, save-respond). Ejemplo, nodo `Resolver cliente/canal Streamify`:

```json
"headerParameters": {
  "parameters": [
    { "name": "X-Donna-Key", "value": "donna-secret-key-pablin-712" }
  ]
}
```

Esto **no usa una credencial de n8n ni `$env`** — está pegado como string literal en cada nodo. Implicaciones:

- Esta clave es la misma `DONNA_API_KEY` que protege **`DonnaApiKeyMiddleware`** (`app/Http/Middleware/DonnaApiKeyMiddleware.php:12`), y es **global para todos los tenants** (`config/services.php:70`) — no hay clave por cliente.
- El archivo está en `docs/donna/` dentro del repositorio Git. Cualquiera con acceso al repo (o a quien se le comparta este JSON para "ver cómo está armado el flujo") tiene la llave maestra de **toda** la API de Donna Business, de todos los clientes SaaS.
- Es exactamente el tipo de fuga que un comprador/auditor externo del producto SaaS detectaría de inmediato y usaría para desconfiar del producto.

**Acción recomendada antes de vender o activar más clientes:**
1. Rotar `DONNA_API_KEY` en `.env` de producción.
2. En n8n, mover el valor a una **credencial tipo "Header Auth"** reutilizable (o a una variable de entorno de n8n), no a un string literal repetido en 19 nodos.
3. Si se exporta este JSON para clonar/entregar a alguien, sanitizarlo primero (o no commitear el export con la key real — usar placeholders `{{STREAMIFY_DONNA_API_KEY}}`).

La nota interna del propio workflow (`Nota variables`, sticky note) dice que debería usar variables (`STREAMIFY_BASE_URL`, `DONNA_API_KEY`, `EVO_API_KEY`...), pero **el v4 real no las usa** — es texto descriptivo desactualizado que no refleja el JSON actual (la URL base y la key están hardcodeadas, no via `$env`).

---

## 3. Hallazgo — endpoint construido pero nunca invocado, y campo silenciosamente descartado

- **`DonnaBusinessMessageController::updateExtractions`** (`app/Http/Controllers/Api/Donna/Business/DonnaBusinessMessageController.php`, ruta `PATCH /donna/business/messages/{message_id}/extractions`) existe y está completo, pero **ningún nodo de ningún workflow lo llama** (`extractions` aparece 0 veces en los 3 JSON). El workflow v4 resuelve transcripción/OCR de otra forma: el nodo `Parsear imagen`/`Transcribe` mete el texto directamente en el body del `POST Donna Business Ingest` (campos `ocr_text`/`transcription_text`), sin un paso posterior de PATCH. Es decir, el diseño cambió y este controller quedó como código muerto. No rompe nada, pero es deuda/confusión para quien mantenga el módulo después.

- **`image_analysis_json` se pierde en el servidor.** El nodo `Parsear imagen` del v4 manda en el body de ingest:
  ```js
  ocr_text: textoDetectado || descripcion || "",
  image_analysis_json: JSON.stringify(parsed),
  agent_input_text: agentInput
  ```
  Pero `DonnaBusinessIngestController::store` (líneas 21-52) valida con `$request->validate([...])` y **`image_analysis_json` no está en esa whitelist** — Laravel descarta el campo antes de que llegue al `DonnaBusinessIngestService`. La línea `DonnaBusinessIngestService.php:191` (`'metadata_json' => !empty($data['image_analysis_json']) ? [...] : null`) nunca se activa en producción: siempre guarda `null`.
  - **Impacto real:** bajo. El texto descriptivo de la imagen (`ocr_text`) sí llega y sí se usa como `effective_text` del mensaje — el agente "ve" la imagen igual. Lo que se pierde es el JSON estructurado completo (`{tipo, es_comprobante, descripcion, texto_detectado, confianza, datos}`) en `metadata_json`, útil para auditoría/depuración de comprobantes de pago, por ejemplo.
  - **Fix de una línea:** agregar `'image_analysis_json' => 'nullable|string'` al `validate()` de `DonnaBusinessIngestController.php`.

---

## 4. ¿El archivo `template.json` (no-v4) se usa en algo?

No debería. Además del endpoint OCR inexistente (`tools/media/image-ocr`), tiene solo 6 tools conectadas al AI Agent (vs. 12 en v4), no tiene `resolve-webhook` (usa `$env.STREAMIFY_BASE_URL` fijo, sin resolución dinámica de canal), no filtra grupos de WhatsApp, y no tiene el patrón `should-respond` + `Wait` para agrupar ráfagas de mensajes. Es una versión claramente anterior. **Recomendación: moverlo a una subcarpeta `docs/donna/archive/` o borrarlo**, para que nadie lo confunda con el activo y lo importe por error en una instalación nueva de n8n.

---

## 5. Resto de la auditoría (backend Laravel, sin cambios respecto a la primera pasada)

### 5.1 Tabla de los 15 criterios de aceptación

| # | Criterio | Estado | Evidencia |
|---|---|---|---|
| 1 | Empleado crea/edita planes con precio | CUMPLE | `app/Http/Controllers/DonnaPlanController.php` |
| 2 | Precio dinámico en /donna | CUMPLE | `routes/web.php:102-129`, `resources/views/donna.blade.php:554,705` |
| 3 | Cliente solicita y empleado aprueba | CUMPLE | `ClienteDonnaController::solicitar` + `DonnaRequestController::approve` |
| 4 | Cliente autocontrata con saldo | CUMPLE | `ClienteDonnaController::activar:76-81` |
| 5 | Empleado configura el agente (prompt/contexto/reglas) | **NO CUMPLE** | Solo existe en panel cliente (`resources/views/shopping/historialCliente.blade.php:1253-1735`) |
| 6 | Empleado registra canal WhatsApp/Telegram | PARCIAL | Solo embebido en alta de suscripción (`DonnaSubscriptionController::store:86-100`); sin pantalla de gestión posterior |
| 7 | Cliente conecta Google desde el panel | CUMPLE | `DonnaGoogleController` |
| 8-9 | n8n consulta `/context` e `/ingest` | DIFIERE del plan | Rutas reales `/api/donna/business/context` y `/api/donna/business/ingest` (prefijo `business/`) — **ya verificado que el workflow real usa estas rutas correctas**, el "plan" documental simplemente nombraba mal el endpoint |
| 10 | No responde si servicio vencido | PARCIAL | Validado en `ingest`/`resolve-webhook`, no en `context` (`DonnaBusinessContextController.php:53`, falta chequeo de `expires_at`) ni en ningún tool controller |
| 11 | No responde si canal inactivo | PARCIAL | Igual que el anterior, falta defensa en profundidad en las tools |
| 12 | Donna Personal solo responde al dueño autorizado | CUMPLE | `DonnaServiceValidator::resolveByTelegram` |
| 13 | Credenciales cifradas | CUMPLE en BD (`Crypt::encryptString` en Channel/Integration) — **pero ver Hallazgo #2: la API key compartida sí está en texto plano en el JSON del workflow** |
| 14 | Aislamiento de datos por cliente | PARCIAL | Aislamiento lógico por `client_id` en queries, pero clave API global y sin verificación criptográfica de que el `client_id` corresponda al canal de origen en cada tool call |
| 15 | Empleado ve historial de conversaciones | PARCIAL | Funciona, pero `DonnaDashboardController` no tiene `Gate::allows()` — cualquier empleado logueado en `/admin` puede verlo |

### 5.2 Gaps críticos backend
1. ~~No existe migración de creación de `donna_channels`~~ **CORREGIDO (falso positivo).** Verificado a fondo: la migración `2026_05_28_154341_add_activation_code_to_donna_channels_table.php`, pese al nombre, tiene un guard `if (!Schema::hasTable('donna_channels'))` que crea la tabla completa con TODAS las columnas (incluidas las que en apariencia agregan las dos migraciones siguientes) cuando no existe. Comparado columna por columna contra `SHOW CREATE TABLE donna_channels` real: coincide exactamente. Las migraciones 2 y 3 son no-ops en una instalación nueva porque las columnas ya existen. Orden cronológico correcto (`donna_subscriptions` se crea antes, así que el FK no falla). **Un servidor nuevo sí puede instalar Donna sin problema en este punto — no era un blocker.**
2. ~~No hay `DonnaChannelController` ni vista de canales~~ **Sigue pendiente** (confirmado de nuevo leyendo rutas reales: no existe `/admin/donna/canales`).
3. ~~No hay configuración de agente en el panel admin~~ **RESUELTO 2026-06-30.** Ver sección 7.
4. **Validación de servicio/canal no centralizada en Business** — cada controller repite su propia versión, las 6 tools no validan nada. Sigue pendiente.
5. **`GET /api/donna/service-status` no existe** en código, solo se menciona en los docs de planificación. Sigue pendiente.

### 5.3 Gaps menores
- Permiso `donna.chats` no existe en `RoleSeeder.php`; sidebar reutiliza `donna.suscripciones` para mostrar Dashboard/Conversaciones.
- `DonnaDashboardController` sin Gate en ningún método.
- Conocimiento = búsqueda por palabras clave en texto plano (`DonnaKnowledgeService.php`), sin PDFs ni embeddings; límite 5000 caracteres por ítem.
- API key global, no por tenant.

### 5.4 Lo que sí funciona bien
- Flujo conversacional Business end-to-end completo y sin stubs.
- Aislamiento por `client_id` consistente en queries de modelos.
- Credenciales en BD cifradas con `Crypt`.
- Ciclo comercial (planes → solicitud → aprobación → suscripción → activación) completo con auditoría en `Historial`.
- Las 11 tablas Donna existen y están en uso en la BD actual.
- Integración Google OAuth + Calendar + Sheets funcional con refresh de tokens.
- **El workflow v4 de n8n tiene paridad casi perfecta (18/19 endpoints) con el backend real** — la ingeniería de la integración n8n↔Laravel está bien hecha, el problema es higiene de secretos y un par de cabos sueltos, no el diseño.

---

## 6. ¿Está listo Donna Business para activarse en el WhatsApp de distintos clientes?

**Arquitectura: sí, está pensada para multi-tenant con un solo workflow.** El nodo `Resolver cliente/canal Streamify` resuelve dinámicamente `instance_name` → `client_id`/`channel_id`/`subscription_id` desde la base de datos en cada mensaje entrante (no hay nada hardcodeado por cliente en la lógica del workflow). Esto significa que, en teoría, **un mismo workflow n8n + un mismo servidor Evolution API pueden atender N clientes distintos**, cada uno con su propia instancia de WhatsApp, sin duplicar el workflow.

**Pero "listo para activar de verdad" depende de cerrar esto primero**, en orden de urgencia:

### Bloqueante real (antes de cualquier cliente nuevo)
1. **Rotar y proteger `DONNA_API_KEY`** (Hallazgo #2). Hoy cualquiera con el JSON tiene acceso a los datos de todos los clientes. **Sigue pendiente.**
2. ~~Crear la migración faltante de `donna_channels`~~ **Descartado — era falso positivo, ver 5.2.1.**

### Antes de prender el segundo cliente real (no solo Streamify)
3. Cerrar el gap de validación: que `DonnaBusinessContextController::show` también rechace por `expires_at` vencido, y que al menos las tools de Calendar/Sheets (las que tocan datos sensibles/Google) verifiquen suscripción activa antes de ejecutar — hoy confían ciegamente en que n8n ya filtró. **Pendiente.**
4. Decidir qué hacer con `template.json` (archivarlo o borrarlo) para que nadie lo reactive por error. **Pendiente** (el archivo desapareció del disco en algún momento de esta sesión, fuera del control del agente — confirmar que fue intencional).
5. Agregar la línea de validación faltante para `image_analysis_json` (cosmético, pero gratis de arreglar). **Pendiente.**

### Antes de vender a un tercero (fuera de Streamify)
6. Pantalla de administración de canales (alta/edición/baja de instancias WhatsApp sin tocar la BD a mano). **Pendiente.**
7. ~~Pantalla de administración de configuración del agente~~ **RESUELTO 2026-06-30** — ver sección 7.

---

## 7. Configuración del agente en el panel admin (implementado 2026-06-30)

Antes, el prompt/contexto/reglas de Donna (tabla `donna_agent_configs`) solo se podía editar desde el panel del **cliente** (`ClienteDonnaController::saveConfig`/`saveBusinessConfig`, vista en `historialCliente.blade.php`). El equipo de Streamify no tenía forma de revisar o corregir la configuración de un cliente sin tocar la base de datos a mano.

Se agregó:
- `app/Http/Controllers/DonnaAgentConfigController.php` — `edit()`/`update()`, gateado por `donna.suscripciones` (ver) y `donna.suscripciones.store` (guardar), mismo patrón que `DonnaSubscriptionController`.
- Rutas: `GET/POST /admin/donna/suscripciones/{id}/config` (`donna.suscripciones.config` / `.config.update`).
- Vista `resources/views/donna/configuraciones/edit.blade.php` — formulario completo con TODOS los campos del modelo `DonnaAgentConfig` (variables del agente, contexto del negocio, prompts/mensajes, herramientas y comportamiento), con secciones distintas para `service_type=personal` vs `business`.
- Botón de acceso (icono engranaje) agregado en cada fila de `donna/suscripciones/index.blade.php`.
- Auditoría en `Historial` al guardar.

No se tocó el formulario del cliente (`saveBusinessConfig`/`saveConfig`) — ambos caminos escriben al mismo registro `donna_agent_configs` vía `updateOrCreate`, así que conviven sin conflicto: el cliente edita lo básico, el admin puede ver/corregir todo.

Verificado: `php -l` sin errores en el controller, rutas registradas (`route:list`), vista Blade compila sin errores de sintaxis PHP.

**Pendiente de probar manualmente en navegador** (no se hizo, ya que está fuera del alcance de esta sesión de código): abrir `/admin/donna/suscripciones/{id}/config` con un usuario real y confirmar que el formulario carga y guarda correctamente para una suscripción Business existente.

### Plan de prueba concreto para activar/validar un canal de WhatsApp ahora mismo
Dado que la arquitectura ya soporta multi-tenant, para probar Donna Business en el WhatsApp de un cliente nuevo (de forma controlada, sin esperar a resolver todo lo de arriba):

1. **Crear/verificar instancia en Evolution API** para ese cliente (número de WhatsApp dedicado), con el webhook apuntando a la URL pública del workflow v4 (`.../webhook/donna-business-whatsapp`, ver nodo `Webhook Evo API`, path `donna-business-whatsapp`).
2. **Dar de alta la suscripción en el admin** (`/admin/donna/suscripciones`, `DonnaSubscriptionController::store`) con `service_type=business`, indicando `instance_name`, `api_base_url` y `api_key` exactos de la instancia Evo creada — esto crea el `DonnaChannel` con `status=active` automáticamente.
3. **Configurar el agente** desde el panel del cliente (`/cliente` → sección Donna Business → nombre del negocio, descripción, tono, horarios, y opcionalmente prompt custom).
4. **Cargar al menos 3-5 ítems de conocimiento** (productos/FAQ/políticas) vía `cliente.donna.knowledge.store` para poder probar que `donna_business_knowledge_search` trae resultados reales y no respuestas inventadas.
5. **Probar el flujo real**, mandando un WhatsApp de prueba al número del cliente, y verificando en orden:
   - Llega el webhook → revisar ejecución en n8n (`Resolver cliente/canal Streamify` debe devolver `success:true` con el `client_id` correcto).
   - `donna_messages` y `donna_conversations` en BD tienen el registro (`SELECT * FROM donna_messages ORDER BY id DESC LIMIT 5`).
   - El agente responde coherente con el conocimiento cargado (no inventa precios).
   - Revisar `donna_tool_logs` para confirmar qué tools se llamaron y con qué `client_id`/`channel_id` (sirve para detectar fugas de aislamiento).
   - Probar un mensaje que dispare agenda (si Calendar está habilitado) y confirmar que el evento aparece en el Google Calendar correcto del cliente.
6. **Probar el corte de servicio**: suspender la suscripción desde el admin (`donna.suscripciones.suspend`) y confirmar que un mensaje nuevo no genera respuesta (debe caer en `blocked_service_inactive` en `donna_messages.processing_status`).

Con eso puedes validar un cliente piloto **ya mismo**, en paralelo a ir cerrando los puntos 1-7. Lo único que yo no haría sin arreglar primero es el punto 1 (la API key) si vas a compartir este workflow o repo con alguien fuera del equipo, o si vas a clonar el workflow para más de un cliente real con datos de producción.

---

## 7. ¿Aguanta una base de conocimientos muy grande sin que Donna "se confunda"?

Pregunta directa: **no, no de forma confiable, tal como está implementado hoy.** El motor de búsqueda de conocimiento es texto plano + conteo de palabras, no semántico. Con una KB chica (10-30 ítems bien redactados) funciona razonablemente bien porque la probabilidad de colisión léxica es baja. Con una KB grande o con productos/servicios parecidos entre sí, el riesgo de confusión sube en proporción al tamaño.

### Cómo busca realmente (`app/Services/Donna/DonnaKnowledgeService.php:14-64`)
```php
$items = $itemsQuery->get(); // trae TODOS los ítems activos del client_id (y del type, si se filtró)
$scored = $items->map(function ($item) use ($words) {
    $haystack = strtolower($item->title . ' ' . $item->content_text);
    $score = 0;
    foreach ($words as $word) { $score += substr_count($haystack, $word); } // conteo literal de substring
    return ['item' => $item, 'score' => $score];
})->filter(fn ($r) => $r['score'] > 0)->sortByDesc('score')->take($limit);
```

Esto es **bag-of-words por substring**, no embeddings/similitud semántica. Consecuencias concretas para una KB grande:

1. **No entiende sinónimos ni paráfrasis.** Si el cliente final escribe "¿tienen para el dolor de cabeza?" y el ítem de la KB dice "Analgésico — Paracetamol 500mg", el match es ~0 porque no comparten palabras literales. Cuantos más ítems tenga la KB, más común es este tipo de fallo porque hay más formas de preguntar lo mismo sin pegarle a las palabras exactas del texto cargado.
2. **Con texto parecido entre ítems, puede traer el incorrecto.** Si hay 15 productos cuyo título comparte palabras ("Crema X 50ml", "Crema X 100ml", "Crema Y para manos"), el score por conteo de substring no distingue intención — puede rankear primero el ítem equivocado y la IA construye la respuesta sobre esa base, sonando "segura" pero equivocada (justo el tipo de error que más daña la percepción de "profesional").
3. **`substr_count` matchea dentro de palabras**, no por palabra completa — una consulta con la palabra "100" puede sumar puntos en cualquier ítem que contenga "100" en cualquier contexto (precio, código, etc.), generando ruido en KBs grandes.
4. **Sin paginación a nivel de BD**: cada búsqueda trae `->get()` de **todos** los ítems activos del cliente (filtrados solo por `type` si se especifica) y los puntúa en PHP en memoria. Con una KB de cientos de ítems esto es lento en cada turno de conversación (cada mensaje del cliente final puede disparar 1-3 búsquedas), y compite con el `wait_seconds` (default 35s, configurable 3-60s en `DonnaAgentConfig::wait_seconds`) que define cuánto espera el agente antes de responder.
5. **Sin tope de resultados en servidor.** Tanto `DonnaBusinessKnowledgeToolController::search` (`limit` default 5) como `DonnaBusinessDataToolController::query` (`limit` default 10, `app/Http/Controllers/Api/Donna/Business/Tools/DonnaBusinessDataToolController.php:26`) toman el `limit` que decide el propio modelo de IA **sin clamp máximo en el backend**. Con una KB muy grande, si el agente pide un `limit` alto (p. ej. al armar un catálogo completo), no hay techo que evite mandar un payload gigante de vuelta al LLM — sube costo de tokens y aumenta la chance de que la respuesta final se vuelva larga/desordenada pese a que el `system_message` le pide ser breve.
6. **Ítems limitados a 5000 caracteres** (`ClienteDonnaKnowledgeController::store`, validación `content_text => max:5000`) y carga 100% manual (copiar/pegar texto, sin subir PDF/Word ni trocear documentos largos automáticamente — confirmado en la auditoría original, sección de conocimiento). Si el cliente tiene un catálogo grande, alguien tiene que partirlo a mano en muchos ítems de <5000 caracteres, lo cual en la práctica frena que las KBs grandes lleguen a cargarse bien curadas.

### Lo que sí juega a favor
- El `system_message` (`DonnaBusinessContextService::buildSystemMessage`, `app/Services/Donna/DonnaBusinessContextService.php:293`) es explícito y estricto: *"OBLIGATORIO: ... SIEMPRE llama a donna_business_knowledge_search. NUNCA respondas sobre el negocio desde tu conocimiento propio ni inventes información."* Esto reduce alucinación pura (Donna no se va a inventar un precio de la nada), pero **no protege contra una recuperación incorrecta** — si la búsqueda trae el ítem equivocado, la IA lo va a presentar con la misma confianza que si fuera el correcto.
- `response_style` (`concise` por defecto, máx. 2 oraciones) ayuda a que errores de redacción no se noten tanto, pero no resuelve errores de contenido.
- El filtro `type` (`product`, `service`, `faq`, `policy`, `table`) si el agente lo usa bien, reduce el universo de búsqueda y mitiga algo el problema de escala.

### Conclusión práctica (estado original, antes del fix de la sección 8)
- **KB chica/mediana (decenas de ítems, bien segmentados por `type`, sin nombres de producto muy parecidos entre sí): funcionaba bien, profesional y consistente.**
- **KB grande (cientos de ítems, catálogos extensos, productos con nombres/variantes similares): riesgo real de respuestas seguras-pero-equivocadas**, y de lentitud por traer+puntuar todo en memoria en cada búsqueda.

> **Actualización 2026-06-30: implementado.** Ver sección 8 — se reemplazó el scoring por substring por búsqueda semántica con embeddings (con fallback automático a keyword-match) y se agregó tope server-side a `limit`. Pendiente: troceo automático de documentos largos (sigue siendo carga manual en bloques de 5000 caracteres).

---

## 8. Fix implementado: búsqueda semántica (embeddings) en la base de conocimientos

Para que Donna Business deje de depender de coincidencia literal de palabras y entienda sinónimos/paráfrasis como lo haría un empleado real, se implementó búsqueda híbrida: **semántica (embeddings de OpenAI + similitud de coseno) con fallback automático a palabras clave** cuando no hay embedding disponible (por ejemplo, si `OPENAI_API_KEY` no está configurada, o un ítem recién creado aún no se procesó). El sistema **nunca deja de responder** por un fallo de embeddings — solo se vuelve menos preciso para ese ítem puntual.

### Qué se agregó
- **Migración** `database/migrations/2026_06_30_000001_add_embedding_to_donna_knowledge_items_table.php` — columnas `embedding_json` (JSON, vector de floats), `embedding_model`, `embedding_updated_at` en `donna_knowledge_items`. Ya aplicada (`php artisan migrate`).
  - No se usó un tipo `VECTOR` nativo porque la BD corre **MySQL 8.4.3**, y MySQL recién incorpora tipo vector en la rama 9.x. Para el volumen esperado por cliente (cientos/pocos miles de ítems) calcular similitud de coseno en PHP en el momento de la búsqueda es suficientemente rápido; si algún cliente crece mucho más, ahí sí conviene migrar a un motor vectorial dedicado (pgvector, Meilisearch, etc.).
- **`app/Services/Donna/DonnaEmbeddingService.php`** (nuevo) — llama a `POST https://api.openai.com/v1/embeddings` (modelo `text-embedding-3-small` por defecto, configurable), expone `embed()`, `embedBatch()` y `cosineSimilarity()` estático. Si no hay API key o la llamada falla, devuelve `null` y queda logueado — nunca lanza excepción hacia el flujo que lo llama.
- **`app/Services/Donna/DonnaKnowledgeService.php`** (reescrito) — ahora es búsqueda híbrida: si el ítem tiene `embedding_json`, puntúa por similitud de coseno contra el embedding de la consulta (+ boost leve si también hay coincidencia literal, para no perder nombres propios/códigos cortos que los embeddings a veces subponderan); si no tiene embedding, cae al scoring por palabras clave de siempre. **Tope duro de resultados** vía `config('services.donna.knowledge_max_limit')` (default 15), sin importar qué `limit` pida el agente de IA.
- **`app/Http/Controllers/ClienteDonnaKnowledgeController.php`** — al crear/editar un ítem de conocimiento (`store`/`update`), genera y guarda su embedding automáticamente (`refreshEmbedding()`), de forma no bloqueante (si OpenAI falla, el ítem se guarda igual).
- **`app/Console/Commands/DonnaBackfillKnowledgeEmbeddings.php`** — comando `php artisan donna:knowledge:backfill-embeddings` para generar embeddings de ítems existentes (los que se cargaron antes de este cambio). Soporta `--client=ID`, `--force` (regenerar todos) y `--chunk=N` (tamaño de lote por llamada batch a OpenAI).
- **`app/Http/Controllers/Api/Donna/Business/Tools/DonnaBusinessDataToolController.php::query`** — también ahora clampea `limit` al mismo tope server-side (antes no tenía ningún límite máximo, ver Hallazgo de la sección 7).
- **`config/services.php`** — nuevo bloque `'openai' => ['api_key' => env('OPENAI_API_KEY')]` y 3 claves nuevas en `'donna'`: `embedding_model`, `embedding_min_score` (umbral mínimo de similitud para aceptar un resultado semántico, default `0.15`), `knowledge_max_limit` (default `15`).

### Qué falta para que quede 100% activo (operativo, no código)
1. **Agregar `OPENAI_API_KEY=sk-...` al `.env` de producción.** Sin esto, el sistema sigue funcionando exactamente como antes (fallback 100% a keyword-match) — es seguro desplegar este cambio aunque la key no esté puesta todavía, no rompe nada.
2. **Correr el backfill una vez que la key esté puesta**, por cliente o global:
   ```
   php artisan donna:knowledge:backfill-embeddings
   ```
   Sin esto, los ítems de conocimiento ya cargados (de clientes existentes) van a seguir usando keyword-match hasta que se editen o se corra el comando — el comando es la forma de "despertarlos" todos de una.
3. **Probar con preguntas parafraseadas** (no las palabras exactas del ítem) sobre una KB real, y mirar el campo `match_type` (`semantic` vs `keyword`) y `score` en la respuesta de `donna_business_knowledge_search` — quedan también registrados en `donna_tool_logs` para auditar caso por caso.
4. **Afinar `DONNA_EMBEDDING_MIN_SCORE`** con datos reales si se nota que trae de más (bajar threshold) o de menos (subirlo) — 0.15 es un punto de partida razonable pero no está calibrado contra el dominio real de ningún cliente todavía.
5. Sigue pendiente (no se tocó en este cambio): troceo automático de documentos largos / subida de PDF. Hoy el conocimiento sigue cargándose a mano en bloques de hasta 5000 caracteres por ítem (`ClienteDonnaKnowledgeController::store`, validación `content_text => max:5000`). Para catálogos extensos esto sigue siendo trabajo manual de armado de la KB, aunque la búsqueda en sí ya sea semántica.
6. Costo operativo: cada guardado/edición de ítem genera 1 llamada a la API de embeddings, y cada búsqueda del agente genera 1 llamada más (para vectorizar la pregunta del cliente final). Con `text-embedding-3-small` el costo es marginal (~$0.02 por millón de tokens), pero requiere una cuenta de OpenAI con billing activo asociada a la `OPENAI_API_KEY`.
