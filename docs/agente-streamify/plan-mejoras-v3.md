# Plan de Mejoras — Agente WhatsApp Streamify v3

> Análisis realizado: 2026-06-04  
> Estado actual: v2 en producción con bugs críticos  
> Objetivo: v3 con prompts mejorados, soporte activo, árbol configurable

---

## Arquitectura actual (referencia)

```
Webhook (Azul/Verde — Evolution API)
  → Normalizar (JS: extrae número, tipo, instancia, apikeys hardcodeadas)
  → Tipo mensaje
      audio  → Transcribe (OpenAI Whisper)
      imagen → Analyze (GPT-4o-mini) → Parsear1 → si comprobante → Load [DISABLED]
      texto  → directo
  → fromMe? → mensajes propios → save-respond
             → del cliente    → ingest msg (debounce 35s) → Wait5
  → get context (API /chat/router/context)
  → If4 (debe_responder?)
  → Merge (adjunta instancia)
  → Clasificador (DeepSeek AI Agent — reglas hardcodeadas)
  → Parsear2
  → If5 (handoff?) → Handoff → fin
                   → Switch Subagente
                       asistente_no_registrado → Asistente no registrado
                       vendedor_cierre         → Vendedor cierre
                       soporte_cliente         → Soporte cliente [DISABLED ← BUG CRÍTICO]
                       cobranzas_pago          → Cobranzas
                       postventa_reciente      → Postventa
  → Parsear3 → save respond → fin
```

**APIs Streamify usadas:**
- `POST /api/v2/chat/router/ingest` — ingesta mensajes con debounce
- `POST /api/v2/chat/router/context` — contexto del turno
- `POST /api/v2/chat/router/respond` — guarda respuesta IA
- `POST /api/v2/chat/router/handoff` — transfiere a humano
- `POST /api/v2/chat/router/save-respond` — guarda respuesta operador
- `GET  /api/v2/chat/assistant/cliente` — busca cliente por teléfono
- `POST /api/v2/chat/assistant/cliente/create` — crea cliente
- `POST /api/v2/chat/assistant/venta` — crea venta
- `GET  /api/v2/precios` y `/precios/servicio/{servicio}` — catálogo
- `GET  /api/v2/metodos-pago` — métodos de pago
- `GET  /api/v2/chat/assistant/postventa/contexto` — contexto postventa

---

## Bugs críticos encontrados

### BUG-1: Nodo "Soporte cliente" deshabilitado
- **Nodo:** `Soporte cliente` (id: `b9d4dfb7-88eb-49f8-9130-3c6246e09575`)
- **Efecto:** Cuando el Clasificador detecta `soporte_cliente`, el Switch lo rutea ahí pero el nodo no ejecuta. El cliente no recibe respuesta. El ticket no se crea.
- **Fix:** Quitar `disabled: true` del nodo.

### BUG-2: API keys hardcodeadas en "Normalizar"
- **Nodo:** `Normalizar` — código JS líneas ~877-882
- **Contenido expuesto:** keys de instancias `bot-pagos` y `default`
- **Fix:** Mover a credenciales n8n o variables de entorno.

### BUG-3: Nodo "Load" (comprobantes) deshabilitado
- **Nodo:** `Load` (id: `6e3ba9f3-9c3e-47e6-bbf5-4a7d73610a07`)
- **Efecto:** Las imágenes identificadas como comprobantes nunca se suben al sistema.
- **Fix:** Habilitar y conectar al flujo de imagen (pendiente revisar si el endpoint `/payments/n8n/receipt-intake` está listo).

---

## Mejoras planificadas

---

### MEJORA-1: Formato WhatsApp + tono amable con emojis

**Problema:** Los prompts de todos los subagentes no tienen instrucciones de formato WhatsApp. Las respuestas salen como texto plano sin `*negrita*`, listas ni emojis. El tono es demasiado seco.

**Implementación:** Añadir este bloque en el `systemMessage` de los 5 subagentes y el Clasificador:

```
FORMATO WHATSAPP OBLIGATORIO:
- Usa *negrita* para datos clave: precios, nombres de servicios, errores
- Usa _cursiva_ solo para aclaraciones secundarias
- Usa listas con guion (-) cuando hay 2 o más opciones o pasos
- Emojis permitidos: máx 1-2 por mensaje, con propósito (✅ confirmado, ❌ error, 💳 pago, 📋 ticket)
- Tono: humano y directo — como un asesor eficiente, no un bot, no exagerado
```

**Subagentes afectados:** Todos (Clasificador, Asistente, Vendedor, Soporte, Cobranzas, Postventa)

---

### MEJORA-2: Soporte — registro forzado de ticket + handoff automático

**Problema:** Aunque se habilite el nodo, el soporte puede ayudar al cliente sin registrar el ticket, o registrarlo sin hacer handoff. El comportamiento debe ser: detectar problema → intentar ayudar → **siempre registrar soporte** → handoff.

**Cambios en el prompt de Soporte:**

```
REGLA CRÍTICA DE SOPORTE:
Cuando confirmes que hay un problema real de acceso (servicio activo + error):
1. Responde al cliente con máx 2 líneas explicando que lo revisan
2. SIEMPRE incluye accion_tipo: "crear_soporte" — sin excepción
3. SIEMPRE incluye escalar_humano: true después de crear el soporte
4. No preguntes más pasos — el humano retomará desde el handoff
```

**Cambios en Parsear3:** Si `accion_tipo === "crear_soporte"`:
- Llamar `POST /api/v2/chat/assistant/soporte/crear` (o el endpoint correspondiente)
- Luego forzar handoff automático (`POST /api/v2/chat/router/handoff`)
- Guardar respuesta con `tipo_remitente: "ia"`

---

### MEJORA-3: Respuestas directas y cortas

**Problema:** Los LLMs ignoran los límites de longitud cuando el contexto es extenso.

**Implementación:** Reforzar con instrucción negativa explícita en todos los prompts:

```
REGLA DE LONGITUD ESTRICTA — NO NEGOCIABLE:
- MÁXIMO 3 líneas por respuesta
- PROHIBIDO: introducción, contexto de fondo, resumen al final
- Si necesitas más de 3 líneas → usa lista con - en vez de párrafo
- Si el texto supera 350 caracteres → recórtalo
- Una sola pregunta o CTA al final — nunca dos preguntas seguidas
```

---

### MEJORA-4: Árbol de decisiones configurable desde el admin

**Problema:** Las reglas del Clasificador están hardcodeadas en el `systemMessage` de n8n. El admin no puede editarlas sin acceder a n8n.

**Arquitectura:**

```
[Admin edita reglas en Vista Laravel]
         ↓
[BD: tabla whatsapp_agent_configs]
         ↓
[GET /api/v2/chat/router/config → retorna árbol como texto]
         ↓
[n8n: nodo HTTP antes del Clasificador inyecta las reglas en el prompt]
```

**En n8n:**
- Añadir nodo `GET /api/v2/chat/router/config` antes de entrar al Clasificador
- El Clasificador usa las reglas del response en vez del texto hardcodeado:
  ```
  systemMessage: "Eres el router de Streamify. Solo clasificas...\n\n{{ $('get config').item.json.data.decision_tree }}"
  ```

**En Laravel (pendiente implementar):**
- Tabla: `whatsapp_agent_configs` (`key`, `value`, `client_id`)
- Endpoint: `GET /api/v2/chat/router/config` — retorna config del agente
- Vista admin: formulario de texto para editar las reglas de clasificación
- Permisos: solo empleados con rol `admin`

**Debate: ¿unificar subagentes o mantener arquitectura?**
- **Mantener 5 subagentes especializados** — recomendado
- Cada subagente tiene herramientas distintas (Vendedor tiene `crear_venta`, Soporte tiene `crear_soporte`, etc.)
- Unificar crearía un prompt gigante y reduciría la precisión
- El árbol configurable actúa sobre el **Clasificador**, no sobre los subagentes

---

### MEJORA-5: Migración a Claude (cuando Docker esté actualizado)

**Pendiente:** Actualizar Docker del servidor para añadir credencial Claude Console.

**Cambio en n8n:** Reemplazar todos los nodos `lmChatDeepSeek` por `lmChatAnthropic` con `claude-sonnet-4-6`.

**Nodos a migrar (6 instancias de DeepSeek):**
- DeepSeek1 (Clasificador)
- DeepSeek2 (Asistente no registrado)
- DeepSeek3 (Vendedor cierre)
- DeepSeek4 (Soporte cliente)
- DeepSeek5 (Cobranzas)
- DeepSeek6 (Postventa)

> ⚠️ La API key `sk-ant-api03-...` en `mejoras.md` debe ser rotada inmediatamente desde Anthropic Console — está expuesta en texto plano en el repositorio.

---

## Orden de ejecución

| Prioridad | Mejora | Impacto | Esfuerzo |
|-----------|--------|---------|----------|
| 1 | BUG-1: Habilitar Soporte | Crítico — clientes sin respuesta | Bajo (1 flag) |
| 2 | MEJORA-1: Formato WA + tono | Alto — experiencia directa | Medio (6 prompts) |
| 3 | MEJORA-2: Soporte forzado | Alto — KPIs de soporte | Medio (prompt + Parsear3) |
| 4 | MEJORA-3: Respuestas cortas | Medio — UX | Bajo (añadir bloque) |
| 5 | BUG-2: Apikeys a entorno | Seguridad | Bajo |
| 6 | MEJORA-4: Árbol configurable | Alto — autonomía admin | Alto (Laravel + n8n) |
| 7 | BUG-3: Habilitar Load | Medio — flujo comprobantes | Bajo (1 flag, verificar endpoint) |
| 8 | MEJORA-5: Migrar a Claude | Alto — calidad respuestas | Bajo (cuando Docker listo) |

---

## Estado de ejecución

- [x] BUG-1: Habilitar nodo Soporte cliente — `disabled: true` removido
- [x] MEJORA-1: Formato WhatsApp + emojis — bloque FORMATO WHATSAPP añadido a los 5 subagentes
- [x] MEJORA-2: Lógica de registro forzado — REGISTRO FORZADO + escalar_humano:true en Soporte
- [x] MEJORA-3: Regla de longitud estricta — bloque BREVEDAD añadido a los 5 subagentes
- [ ] BUG-2: Mover apikeys a variables de entorno n8n (nodo Normalizar)
- [ ] MEJORA-4: Vista Laravel + endpoint + inyección en Clasificador
- [ ] BUG-3: Habilitar Load (verificar endpoint primero)
- [ ] MEJORA-5: Migrar DeepSeek → Claude (pendiente Docker)

> Archivo listo para importar en n8n: `docs/agente-streamify/flujo-n8n.json`
