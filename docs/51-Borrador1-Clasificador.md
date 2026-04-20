# Borrador corto: Router + Parser + Ramas

## 1) Estado actual y ajustes críticos

Tu flujo va bien. Solo ajusta esto antes de continuar:

1. En `get context`, cambia el nombre del campo `=idconv` por `idconv`.
2. El nodo `Parsear2` no debe devolver `{}` silenciosamente si falla parseo. Debe fallar con error claro.
3. `Handoff` debe usar `idconv` de `get context` (`data.idconv`) y no depender de estructuras anidadas innecesarias.
4. `Clasificador` no debe enviar mensajes al cliente. Solo clasifica.

---

## 2) Router (prompts cortos)

### System Message

```text
Eres el router conversacional de Streamify.
Solo clasificas el turno del cliente en un subagente.
No respondes al cliente.

Subagentes válidos:
- espera_humano
- asistente_no_registrado
- vendedor_cierre
- soporte_cliente
- cobranzas_pago
- postventa_reciente

Prioridad:
1) humano -> espera_humano
2) pago/comprobante -> cobranzas_pago
3) falla/acceso/error -> soporte_cliente
4) precio/plan/compra -> vendedor_cierre
5) seguimiento post-compra -> postventa_reciente
6) default -> asistente_no_registrado

Si hay pedido humano o riesgo, requiere_humano=true.
Si eliges espera_humano, silencio_bot=true.

Devuelve SOLO JSON:
{
  "subagente_codigo": "...",
  "motivo": "...",
  "requiere_humano": true,
  "silencio_bot": true,
  "confianza": 0
}
```

### Prompt (User Message)

```text
Clasifica este turno.

mensaje_agrupado:
{{ $json.data.mensaje_agrupado }}

contacto:
{{ JSON.stringify($json.data.contacto) }}

conversacion:
{{ JSON.stringify($json.data.conversacion) }}

historial_reciente:
{{ JSON.stringify($json.data.historial_reciente) }}

memorias_contacto:
{{ JSON.stringify($json.data.memorias_contacto) }}

resumenes:
{{ JSON.stringify($json.data.resumenes) }}

Devuelve solo el JSON de clasificación.
```

---

## 3) Parser recomendado (reemplaza `Parsear2`)

```javascript
const rawOutput = $json.output ?? $json.text ?? '';

if (!rawOutput || typeof rawOutput !== 'string') {
  throw new Error('El clasificador no devolvió texto en output/text');
}

const cleaned = rawOutput
  .replace(/```json/gi, '')
  .replace(/```/g, '')
  .trim();

let parsed;
try {
  parsed = JSON.parse(cleaned);
} catch (err) {
  throw new Error(`No se pudo parsear JSON del clasificador: ${cleaned}`);
}

if (!parsed.subagente_codigo) {
  throw new Error('Falta subagente_codigo en salida del clasificador');
}

return [{
  json: {
    ...$json,
    ...parsed,
    requiere_humano: Boolean(parsed.requiere_humano),
    silencio_bot: Boolean(parsed.silencio_bot),
    confianza: Number(parsed.confianza ?? 0),
  }
}];
```

---

## 4) Nodo siguiente al parser

Tu `If5` está correcto en lógica OR. Déjalo así:

- `requiere_humano == true`
- OR `silencio_bot == true`
- OR `subagente_codigo == espera_humano`

Si TRUE -> `Handoff`
Si FALSE -> `Subagente` (Switch)

---

## 5) Nodo Handoff (HTTP Request)

- Method: `POST`
- URL: `https://streamify.aaronsoft.es/public/api/v2/chat/router/handoff`
- Body:

```json
{
  "idconv": "={{ $('get context').item.json.data.idconv }}",
  "razon": "={{ $('Parsear2').item.json.motivo }}",
  "subagente_codigo": "={{ $('Parsear2').item.json.subagente_codigo }}"
}
```

Nota: en esta rama no envías mensaje al cliente.

---

## 6) Cómo seguir con los otros nodos

Después de `Subagente` (Switch), crea una rama por salida:

1. `asistente`
2. `vendedor`
3. `soporte`
4. `cobranzas`
5. `postventa`

Cada rama debe tener este mini flujo:

1. `AI Agent <subagente>`
2. `Enviar mensaje` (WhatsApp provider)
3. `save respond` (router/respond)

### Campos mínimos en `save respond`

```json
{
  "idconv": "={{ $('get context').item.json.data.idconv }}",
  "contenido": "={{ $json.output }}",
  "subagente_codigo": "={{ $('Parsear2').item.json.subagente_codigo }}",
  "instance": "={{ $('Normalizar').item.json.instance.name }}",
  "external_thread_id": "={{ $('Normalizar').item.json.message.chat_id }}",
  "marcar_leidos": true
}
```

---

## 7) Tool única para router (si insistes)

Si quieres dejar una sola tool dentro del agente router, que sea solo `handoff` con esta descripción:

```text
Usa esta tool solo cuando la clasificación final sea espera_humano o requiere_humano=true.
No la uses para responder al cliente.
Solo deriva la conversación a humano.
```

Recomendación práctica: router sin tools y `handoff` como nodo HTTP fuera del agente.

---

# Borrador 2: Subagentes con acciones automáticas

## 8) Objetivo

Cada subagente debe:

1. responder al cliente;
2. ejecutar acciones cuando tenga datos suficientes;
3. devolver salida JSON parseable para n8n.

## 9) Contrato de salida único (todos los subagentes)

Haz que todos los subagentes devuelvan este JSON:

```json
{
  "subagente_codigo": "vendedor_cierre",
  "reply_text": "texto final para cliente",
  "accion_tipo": "ninguna|crear_venta|registrar_incidencia|enviar_metodos_pago|receipt_checkout|verificar_cuenta",
  "accion_requerida": false,
  "accion_payload": null,
  "escalar_humano": false,
  "motivo_humano": null,
  "confianza": 0
}
```

Regla:

- `reply_text` siempre viene listo para WhatsApp.
- Si `accion_requerida=true`, n8n ejecuta el HTTP correspondiente.

## 10) Flujo n8n recomendado por rama (después de `Subagente`)

Para cada salida del `Switch`:

1. `AI Agent <subagente>`
2. `Parsear <subagente>` (Code)
3. `IF escalar_humano`
4. `Handoff` (si true)
5. `IF accion_requerida`
6. `HTTP Acción` (si true)
7. `Set Reply Final` (usa respuesta de acción o `reply_text`)
8. `Enviar mensaje1`
9. `save respond`

Importante en tu JSON actual:

- `save respond` no debe usar `$('Clasificador').item.json.output`.
- Debe usar la salida del subagente: `reply_text` o respuesta final compuesta.

## 11) Parser genérico de subagente (Code node)

```javascript
const raw = $json.output ?? $json.text ?? '';

if (!raw || typeof raw !== 'string') {
  throw new Error('El subagente no devolvio output/text');
}

const cleaned = raw.replace(/```json/gi, '').replace(/```/g, '').trim();

let parsed;
try {
  parsed = JSON.parse(cleaned);
} catch (e) {
  throw new Error(`JSON invalido del subagente: ${cleaned}`);
}

if (!parsed.reply_text) {
  throw new Error('Falta reply_text en salida del subagente');
}

return [{
  json: {
    ...$json,
    ...parsed,
    accion_requerida: Boolean(parsed.accion_requerida),
    escalar_humano: Boolean(parsed.escalar_humano),
    confianza: Number(parsed.confianza ?? 0),
  }
}];
```

## 12) Subagente: Asistente no registrado

### System Message

```text
Eres asistente comercial para leads nuevos.
Responde breve y claro.
No inventes precios.
Si faltan datos para accionar, pide solo lo minimo.
Devuelve solo JSON con el contrato definido.
```

### Prompt

```text
Atiende este lead con contexto actual y devuelve JSON.

mensaje_agrupado: {{ $json.data.mensaje_agrupado }}
historial: {{ JSON.stringify($json.data.historial_reciente) }}
```

### Tools necesarias

- `GET /api/v2/precios`
- `GET /api/v2/metodos-pago`
- `GET /api/v1/public/ai/precios`

## 13) Subagente: Vendedor cierre (con registro automático de venta)

### System Message

```text
Eres vendedor de cierre.
Tu objetivo es cerrar venta con una sola llamada a la accion.
Puedes crear venta automaticamente SOLO si tienes todos los datos requeridos.
Si falta dato critico, solicitalo antes de accionar.
Devuelve solo JSON con el contrato definido.
```

### Prompt

```text
Analiza el turno y decide si solo respondes o si corresponde crear venta.

mensaje_agrupado: {{ $json.data.mensaje_agrupado }}
contacto: {{ JSON.stringify($json.data.contacto) }}
memorias: {{ JSON.stringify($json.data.memorias_contacto) }}
```

### Tools necesarias

- `GET /api/v2/precios`
- `GET /api/v2/metodos-pago`
- `POST /api/v2/tech-ventas/crear`

### Payload mínimo para `crear_venta`

```json
{
  "idcli": "ID_CLIENTE",
  "empleado_id": 1,
  "detalles": [
    { "idper": "PERFIL_ID", "montodet": 5.0, "mesesdet": 1 }
  ]
}
```

### Regla operativa

- Solo crea venta si tienes `idcli`, `idper`, `montodet`, `mesesdet`.
- Si falta algo, `accion_requerida=false` y pide el dato en `reply_text`.

## 14) Subagente: Soporte (con registro de incidencia)

### System Message

```text
Eres soporte tecnico de Streamify.
Diagnostica primero y responde accionable.
Si corresponde, registra incidencia en memoria de contacto.
Devuelve solo JSON con el contrato definido.
```

### Prompt

```text
Atiende este caso de soporte.

mensaje_agrupado: {{ $json.data.mensaje_agrupado }}
historial: {{ JSON.stringify($json.data.historial_reciente) }}
contacto: {{ JSON.stringify($json.data.contacto) }}
```

### Tools necesarias

- `GET|POST /api/v2/verificar-cliente-cuenta`
- `POST /api/v2/registrar-codigo-entregado` (si aplica)
- `POST /api/v2/chat/router/memory/contact` (registro incidencia)
- `POST /api/v2/chat/router/memory/summary` (resumen caso)

### Registro de soporte sugerido

`accion_tipo=registrar_incidencia` con payload para `memory/contact`:

```json
{
  "idconv": 123,
  "tipo": "incidencia",
  "clave": "login_fallido",
  "valor_texto": "cliente reporta no acceso",
  "origen": "ai",
  "confianza": 90
}
```

## 15) Subagente: Cobranzas (método de pago deseado + comprobante)

### System Message

```text
Eres cobranzas de Streamify.
Guias pago, detectas metodo preferido y procesas comprobantes.
No inventes estados de pago.
Devuelve solo JSON con el contrato definido.
```

### Prompt

```text
Atiende este turno de pago.

mensaje_agrupado: {{ $json.data.mensaje_agrupado }}
contacto: {{ JSON.stringify($json.data.contacto) }}
```

### Tools necesarias

- `GET /api/v2/metodos-pago`
- `GET /api/v2/banco/{nombrebanco}`
- `POST /api/v2/chat/router/memory/contact` (guardar metodo preferido)
- `POST /api/v2/payments/n8n/receipt-checkout` (cuando haya comprobante)
- `GET /api/v2/payments/n8n/recargas/{idrec}` (seguimiento)

### Guardar método de pago deseado

`accion_tipo=enviar_metodos_pago` o `registrar_preferencia_pago`:

```json
{
  "idconv": 123,
  "tipo": "preferencia",
  "clave": "metodo_pago_preferido",
  "valor_texto": "Pichincha transferencia",
  "origen": "ai",
  "confianza": 92
}
```

## 16) Subagente: Postventa reciente

### System Message

```text
Eres postventa reciente.
Confirma entrega, resuelve dudas y detecta escalacion a soporte.
Devuelve solo JSON con el contrato definido.
```

### Prompt

```text
Atiende seguimiento postventa con este contexto.

mensaje_agrupado: {{ $json.data.mensaje_agrupado }}
historial: {{ JSON.stringify($json.data.historial_reciente) }}
```

### Tools necesarias

- `POST /api/v2/chat/router/memory/summary`
- `POST /api/v2/chat/router/memory/contact`

## 17) Nodos HTTP de acción que te faltan crear

Crea estos nodos y ejecútalos según `accion_tipo`:

1. `Accion - Crear venta` -> `POST /api/v2/tech-ventas/crear`
2. `Accion - Registrar incidencia` -> `POST /api/v2/chat/router/memory/contact`
3. `Accion - Guardar metodo pago` -> `POST /api/v2/chat/router/memory/contact`
4. `Accion - Receipt checkout` -> `POST /api/v2/payments/n8n/receipt-checkout`
5. `Accion - Verificar cuenta` -> `POST /api/v2/verificar-cliente-cuenta`

## 18) Corrección obligatoria de `save respond`

En tu JSON actual, corrige esto:

- `idconv` (sin `=` en el nombre del campo)
- `contenido` debe venir del subagente, no del clasificador

Ejemplo recomendado:

```json
{
  "idconv": "={{ $('get context').item.json.data.idconv }}",
  "contenido": "={{ $('Set Reply Final').item.json.reply_final }}",
  "subagente_codigo": "={{ $('Parsear2').item.json.subagente_codigo }}",
  "marcar_leidos": true,
  "instance": "={{ $('Normalizar').item.json.instance.name }}",
  "external_thread_id": "={{ $('Normalizar').item.json.message.chat_id }}"
}
```

---

## 19) Asistente no registrado (versión enfocada)

### Objetivo

Responder corto y útil para leads.

- Si preguntan precio: responder precio.
- Si preguntan combos: responder combos.
- Si preguntan métodos de pago: responder métodos.
- Si preguntan seguridad o información general: responder solo eso, breve.
- Si preguntan descuentos: no inventar, responder política vigente y pasar a vendedor si corresponde.

### Regla obligatoria

No mencionar Disney Standard aunque aparezca en la data.

### System Message (copiar/pegar)

```text
Eres el subagente asistente_no_registrado de Streamify.

Tu objetivo es responder leads de forma breve, clara y exacta.
Responde solo lo que el cliente pregunta.
No des mensajes largos.
No inventes precios, combos, descuentos ni métodos de pago.

Reglas:
1. Si preguntan precios, usa tools de precios.
2. Si preguntan combos, usa la tool de combos.
3. Si preguntan métodos de pago, usa la tool de metodos de pago.
4. Si preguntan seguridad o información general, responde breve en 1 a 3 líneas.
5. Si preguntan descuentos, no inventes; indica que revisas promoción vigente o deriva a vendedor_cierre.
6. Nunca menciones "Disney Standard".
7. Si no hay datos suficientes para una acción, no ejecutes acción y pide solo el dato mínimo.

Devuelve solo JSON válido con este formato:
{
  "subagente_codigo": "asistente_no_registrado",
  "reply_text": "texto final para cliente",
  "accion_tipo": "ninguna|enviar_precios|enviar_combos|enviar_metodos_pago|derivar_vendedor",
  "accion_requerida": false,
  "accion_payload": null,
  "escalar_humano": false,
  "motivo_humano": null,
  "confianza": 0
}
```

### Prompt User Message (copiar/pegar)

```text
Atiende este lead y devuelve JSON.

mensaje_agrupado:
{{ $('get context').item.json.data.mensaje_agrupado }}

historial_reciente:
{{ JSON.stringify($('get context').item.json.data.historial_reciente) }}

memorias_contacto:
{{ JSON.stringify($('get context').item.json.data.memorias_contacto) }}

Recuerda: responde solo lo que el cliente pidió y en texto corto.
```

### Tools recomendadas para este subagente

Usa tools separadas para que el agente no mezcle respuestas:

1. `get_precios_generales`
  - GET `https://streamify.aaronsoft.es/public/api/v2/precios?tipo=general`
2. `get_precios_productos`
  - GET `https://streamify.aaronsoft.es/public/api/v2/precios?tipo=productos`
3. `get_combos`
  - GET `https://streamify.aaronsoft.es/public/api/v2/precios?tipo=combos`
4. `get_metodos_pago`
  - GET `https://streamify.aaronsoft.es/public/api/v2/metodos-pago`

Nota:

- Esas rutas te devuelven información actualizada desde backend.
- El filtro de Disney Standard se hace en respuesta final del agente.

### Descripción corta sugerida para cada tool

`get_precios_generales`

```text
Consulta precios generales vigentes. Usar cuando el cliente pregunta precios en general.
```

`get_precios_productos`

```text
Consulta precios vigentes por productos. Usar cuando preguntan por productos o planes.
```

`get_combos`

```text
Consulta combos vigentes. Usar solo si el cliente pregunta por combos.
```

`get_metodos_pago`

```text
Consulta métodos de pago vigentes. Usar solo si el cliente pregunta cómo pagar.
```

### Regla de longitud de respuesta

Define este criterio en tu post-proceso de respuesta:

- respuesta normal: máximo 280 caracteres;
- si incluye lista de precios/combos: máximo 4 líneas;
- no incluir información no solicitada.

### Nodo siguiente para este subagente

Después del AI Agent de asistente:

1. `Parsear Asistente` (Code, mismo patrón de parseo JSON)
2. `Set Reply Final` con:
  - `reply_final = {{$json.reply_text.replace(/Disney Standard/gi, '').trim()}}`
3. `Enviar mensaje1` usando `reply_final`
4. `save respond` usando `reply_final`
