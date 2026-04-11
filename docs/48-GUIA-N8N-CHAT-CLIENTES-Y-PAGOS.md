# Guía n8n: chat de clientes y pagos por comprobante

Esta guía documenta las APIs nuevas que reemplazan el flujo viejo con consulta directa a MySQL para agrupar mensajes de un cliente, y la API nueva para subir comprobante y solicitar compra automática.

El objetivo es que n8n ya no consulte ni escriba una tabla externa tipo `historial_chat_pablinn`, sino que use el sistema real de Streamify.

---

## 1. Base de uso

### Base URL

Usa la URL base de tu proyecto. Ejemplos:

- local: `http://localhost:8000/api/v2`
- laragon: `http://streaamifyoficial.test/api/v2`
- servidor: `https://streamify.aaronsoft.es/public/api/v2`

### Autenticación

Estas APIs usan `api.key`.

La forma recomendada en n8n es enviar este header:

```http
X-API-Key: TU_API_KEY
Accept: application/json
```

Aunque algunos endpoints aceptan `apikey` en body por compatibilidad, en n8n conviene usar siempre `X-API-Key`.

### Canales soportados

- `whatsapp`
- `messenger`
- `telegram`
- `webchat`

---

## 2. Flujo correcto para reemplazar MySQL de mensajes agrupados

Antes hacías esto:

1. guardar cada mensaje en MySQL;
2. esperar unos segundos;
3. consultar los últimos mensajes del cliente;
4. verificar si el mensaje actual era el último;
5. unir los no leídos;
6. responder una sola vez.

Ahora el flujo correcto con APIs es este:

1. `POST /chat/router/ingest`
   Guarda cada mensaje inbound en Streamify.
2. esperar `35` segundos en n8n
   O el tiempo que definas en `debounce_seconds`.
3. `POST /chat/router/context`
   Devuelve los mensajes pendientes agrupados y el historial reciente.
4. revisar `data.debe_responder`
   Si es `false`, se termina el flujo.
5. generar respuesta con IA
   Usando `mensaje_agrupado`, `historial_reciente`, memorias y subagentes.
6. enviar mensaje al proveedor de WhatsApp
   Evolution, Baileys, Meta, etc.
7. `POST /chat/router/respond`
   Registra la respuesta de la IA y marca como atendidos los mensajes pendientes.

Si la conversación debe pasar a un humano:

8. `POST /chat/router/handoff`

---

## 3. API para guardar mensaje inbound

### Endpoint

`POST /api/v2/chat/router/ingest`

### Para qué sirve

Guarda un mensaje que llega desde WhatsApp, Messenger, Telegram o webchat dentro del sistema de Streamify.

Esta API:

- crea o reutiliza el contacto del canal;
- busca o abre una conversación;
- guarda el mensaje en `mensajes`;
- guarda el vínculo externo en `chat_mensajes_canal`;
- devuelve hasta cuándo esperar antes de pedir el contexto agrupado.

### Campos aceptados

#### Obligatorios

- `canal`: `whatsapp`, `messenger`, `telegram`, `webchat`
- `canal_user_id`: identificador del usuario en el canal

Además debes mandar al menos uno de estos:

- `mensaje`
- `contenido`
- `media_url`

#### Opcionales más usados

- `mensaje`: texto del cliente
- `contenido`: alias de `mensaje`
- `tipo_contenido`: `texto`, `imagen`, `archivo`, `audio`, `video`, `documento`
- `external_message_id`: id único del mensaje externo
- `external_thread_id`: id del hilo externo
- `telefono`: teléfono del cliente
- `numero`: alias de `telefono`
- `nombre`: nombre mostrado del contacto
- `idcli`: si ya conoces el cliente de Streamify
- `origen`: ejemplo `n8n`
- `payload`: objeto JSON crudo del proveedor
- `media_url`: URL del archivo recibido
- `media_mime_type`: mime type del archivo
- `media_id`: id del archivo en el proveedor
- `subagente_codigo`: si quieres forzar uno al iniciar
- `debounce_seconds`: segundos de espera recomendados antes de consultar contexto
- `instance`: nombre de la instancia del proveedor

### Recomendación importante

Para evitar duplicados, manda siempre `external_message_id` si tu proveedor lo da.

Si vuelves a enviar el mismo `external_message_id`, la API no duplicará el mensaje.

### Ejemplo JSON

```json
{
  "canal": "whatsapp",
  "canal_user_id": "593998887777",
  "telefono": "+593998887777",
  "nombre": "Carlos Mena",
  "mensaje": "Hola\nquiero netflix\ncuanto cuesta?",
  "tipo_contenido": "texto",
  "external_message_id": "wamid.HBgLN...",
  "external_thread_id": "593998887777@c.us",
  "instance": "streamify-main",
  "origen": "n8n",
  "payload": {
    "provider": "evolution",
    "raw_type": "conversation"
  },
  "debounce_seconds": 35
}
```

### Ejemplo de respuesta exitosa

```json
{
  "success": true,
  "message": "Mensaje inbound registrado correctamente.",
  "data": {
    "idconv": 184,
    "idmsg": 991,
    "contacto_canal_id": 27,
    "chat_mensaje_canal_id": 55,
    "duplicado": false,
    "esperar_hasta": "2026-04-10T19:31:14+00:00",
    "conversacion_estado": "abierta"
  }
}
```

### Si el mensaje ya existía

```json
{
  "success": true,
  "message": "Mensaje ya registrado previamente.",
  "data": {
    "idconv": 184,
    "idmsg": 991,
    "contacto_canal_id": 27,
    "chat_mensaje_canal_id": 55,
    "duplicado": true,
    "esperar_hasta": "2026-04-10T19:31:14+00:00",
    "conversacion_estado": "abierta"
  }
}
```

### Cómo usarla en n8n

Nodo `HTTP Request`:

- Method: `POST`
- URL: `{{$json.base_url}}/api/v2/chat/router/ingest`
- Send Body: `JSON`
- Headers:
  - `X-API-Key`
  - `Accept: application/json`

Guarda del resultado:

- `data.idconv`
- `data.idmsg`
- `data.esperar_hasta`
- `data.duplicado`

---

## 4. API para consultar contexto y agrupar mensajes

### Endpoint

`GET o POST /api/v2/chat/router/context`

### Para qué sirve

Esta es la API que reemplaza el `SELECT` viejo a MySQL.

Devuelve:

- la conversación actual;
- los mensajes pendientes del cliente;
- `mensaje_agrupado` listo para IA;
- historial reciente;
- memorias del contacto;
- resúmenes;
- subagentes disponibles.

### Cómo identifica la conversación

Puedes resolver la conversación de dos formas:

#### Opción A: por `idconv`

La más segura si ya guardaste el `idconv` desde `ingest`.

#### Opción B: por canal

- `canal`
- `canal_user_id`

### Cómo decide si debes responder o no

La API compara el mensaje que disparó el flujo contra el último mensaje pendiente de esa conversación.

Por eso conviene mandar uno de estos:

- `trigger_idmsg`
- `external_message_id`

Si el mensaje que disparó el flujo ya no es el último pendiente, la API devuelve `debe_responder = false`.

Eso reemplaza tu lógica vieja de:

- esperar
- consultar últimos mensajes
- comprobar si “sigo siendo el último”

### Campos aceptados

#### Para resolver conversación

- `idconv` opcional
- `canal` opcional si no mandas `idconv`
- `canal_user_id` opcional si no mandas `idconv`

#### Para control de agrupación

- `trigger_idmsg` opcional pero recomendado
- `external_message_id` opcional, alternativa si no guardaste `idmsg`

#### Para cantidad de datos

- `historial_limite` opcional, por defecto `10`
- `memoria_limite` opcional, por defecto `8`

### Ejemplo mínimo recomendado

```json
{
  "idconv": 184,
  "trigger_idmsg": 991,
  "historial_limite": 10,
  "memoria_limite": 8
}
```

### Ejemplo alternativo por canal

```json
{
  "canal": "whatsapp",
  "canal_user_id": "593998887777",
  "external_message_id": "wamid.HBgLN...",
  "historial_limite": 10,
  "memoria_limite": 8
}
```

### Ejemplo de respuesta

```json
{
  "success": true,
  "message": "Contexto listo para responder.",
  "data": {
    "debe_responder": true,
    "idconv": 184,
    "contacto": {
      "id": 27,
      "canal": "whatsapp",
      "canal_user_id": "593998887777",
      "telefono_normalizado": "593998887777",
      "idcli": 14
    },
    "conversacion": {
      "idconv": 184,
      "estado": "abierta",
      "requiere_humano": false,
      "subagente_codigo": "router_general",
      "ultima_actividad": "2026-04-10T19:31:05+00:00"
    },
    "trigger_idmsg": 991,
    "ultimo_pendiente_idmsg": 991,
    "mensajes_pendientes": [
      {
        "idmsg": 989,
        "tipo_remitente": "cliente",
        "contenido": "Hola",
        "tipo_contenido": "texto"
      },
      {
        "idmsg": 990,
        "tipo_remitente": "cliente",
        "contenido": "quiero netflix",
        "tipo_contenido": "texto"
      },
      {
        "idmsg": 991,
        "tipo_remitente": "cliente",
        "contenido": "cuanto cuesta?",
        "tipo_contenido": "texto"
      }
    ],
    "mensaje_agrupado": "Hola\nquiero netflix\ncuanto cuesta?",
    "historial_reciente": [],
    "memorias_contacto": [],
    "resumenes": [],
    "memoria_negocio": [],
    "subagentes": []
  }
}
```

### Si no debes responder

```json
{
  "success": true,
  "message": "Ya existe un mensaje más reciente pendiente por procesar.",
  "data": {
    "debe_responder": false,
    "idconv": 184,
    "trigger_idmsg": 991,
    "ultimo_pendiente_idmsg": 992,
    "mensaje_agrupado": "Hola\nquiero netflix\nahora tambien disney"
  }
}
```

En ese caso, n8n debe terminar el flujo sin responder.

### Cómo usar `mensaje_agrupado`

Ese campo ya viene listo para pasar al prompt del agente.

Ejemplo:

```text
Usuario agrupado:
{{ $json.data.mensaje_agrupado }}

Historial reciente:
{{ JSON.stringify($json.data.historial_reciente) }}
```

### Cómo usarla en n8n

Nodo `HTTP Request`:

- Method: `POST`
- URL: `{{$json.base_url}}/api/v2/chat/router/context`
- Body: JSON
- Headers:
  - `X-API-Key`
  - `Accept: application/json`

Lógica siguiente:

1. si `success != true`, manejar error
2. si `data.debe_responder != true`, terminar
3. si `data.debe_responder == true`, pasar `mensaje_agrupado` a la IA

---

## 5. API para registrar la respuesta del agente

### Endpoint

`POST /api/v2/chat/router/respond`

### Para qué sirve

Registra en Streamify la respuesta generada por la IA y marca como atendidos los mensajes pendientes del cliente.

Ojo: esta API no envía el WhatsApp por sí sola. Solo registra en tu sistema la respuesta final.

### Orden recomendado

1. `context`
2. IA genera respuesta
3. proveedor WhatsApp envía el texto al cliente
4. `respond` guarda la respuesta en Streamify

Así puedes mandar también el `external_message_id` real del mensaje saliente.

### Campos aceptados

- `idconv` o `canal + canal_user_id`
- `contenido` obligatorio
- `subagente_codigo` opcional
- `metadata` opcional, objeto JSON
- `external_message_id` opcional, id del mensaje outbound del proveedor
- `external_thread_id` opcional
- `instance` opcional
- `marcar_leidos` opcional, por defecto conviene `true`

### Alias de subagente aceptados

Puedes mandar cualquiera de estos y el sistema normaliza:

- `router` -> `router_general`
- `humano` -> `espera_humano`
- `ventas` -> `vendedor_cierre`
- `soporte` -> `soporte_cliente`
- `cobranzas` -> `cobranzas_pago`
- `postventa` -> `postventa_reciente`

### Ejemplo JSON

```json
{
  "idconv": 184,
  "contenido": "Hola, Netflix pantalla cuesta 4.50 al mes.",
  "subagente_codigo": "ventas",
  "external_message_id": "wamid.outbound.7788",
  "external_thread_id": "593998887777@c.us",
  "instance": "streamify-main",
  "marcar_leidos": true,
  "metadata": {
    "modelo": "deepseek-chat",
    "provider": "evolution",
    "tokens": 483,
    "prompt_version": "chat-router-v1"
  }
}
```

### Respuesta

```json
{
  "success": true,
  "message": "Respuesta del agente registrada correctamente.",
  "data": {
    "idconv": 184,
    "idmsg": 992,
    "chat_mensaje_canal_id": 56,
    "pendientes_cerrados": 3,
    "estado_conversacion": "bot_activo"
  }
}
```

---

## 6. API para derivar a humano

### Endpoint

`POST /api/v2/chat/router/handoff`

### Para qué sirve

Marca la conversación como pendiente de humano y deja el bot en espera.

### Campos aceptados

- `idconv` o `canal + canal_user_id`
- `razon` opcional
- `subagente_codigo` opcional

Si no mandas `subagente_codigo`, se usa `espera_humano`.

### Ejemplo

```json
{
  "idconv": 184,
  "razon": "Cliente pidió hablar con asesor humano"
}
```

### Respuesta

```json
{
  "success": true,
  "message": "Conversación derivada a humano.",
  "data": {
    "idconv": 184,
    "estado": "en_espera",
    "requiere_humano": true
  }
}
```

---

## 7. Resumen rápido del flujo de chat en n8n

### Nodo 1. Inbound webhook del proveedor

Recibes:

- número del cliente
- nombre
- texto
- media
- message id

### Nodo 2. `chat/router/ingest`

Guarda el mensaje entrante.

### Nodo 3. `Wait`

Espera `35` segundos.

### Nodo 4. `chat/router/context`

Pide contexto con `idconv + trigger_idmsg`.

### Nodo 5. `IF debe_responder`

- `false`: terminar flujo
- `true`: continuar

### Nodo 6. IA

Prompt recomendado:

```text
Canal: {{ $json.data.contacto.canal }}
Cliente: {{ $json.data.contacto.cliente.nombrecli || $json.data.contacto.nombre_canal || 'Sin nombre' }}
Subagente actual: {{ $json.data.conversacion.subagente_codigo }}

Mensaje agrupado del cliente:
{{ $json.data.mensaje_agrupado }}

Historial reciente:
{{ JSON.stringify($json.data.historial_reciente) }}

Memorias del contacto:
{{ JSON.stringify($json.data.memorias_contacto) }}

Resúmenes:
{{ JSON.stringify($json.data.resumenes) }}
```

### Nodo 7. Enviar respuesta a WhatsApp

Usas tu proveedor actual.

### Nodo 8. `chat/router/respond`

Registras la respuesta ya enviada.

### Nodo 9. Si aplica, `chat/router/handoff`

Solo si la IA decide derivar a humano.

---

## 8. API de comprobante y compra automática

### Endpoint

`POST /api/v2/payments/n8n/receipt-checkout`

### Para qué sirve

Esta API recibe un comprobante desde WhatsApp o n8n y hace todo esto:

1. resuelve o crea cliente;
2. calcula hash SHA-256 del archivo;
3. detecta comprobante repetido;
4. guarda la recarga;
5. dispara la verificación automática existente;
6. espera la decisión;
7. si la recarga se aprueba, intenta comprar el producto;
8. si no hay stock o el producto no es de entrega inmediata, crea pedido para atención manual.

### Tipo de request

Debe ser `multipart/form-data`.

No sirve enviarla como JSON puro porque `foto` es un archivo binario.

### Campos aceptados

#### Obligatorios

- `producto_id`
- `idban`
- `valor`
- `foto`

Y además uno de estos:

- `idcli`
- `cliente_telefono`

#### Opcionales

- `cliente_nombre`
- `cliente_email`
- `numcomprobante`
- `canal` (`whatsapp`, `messenger`, `telegram`, `webchat`)
- `external_reference`
- `observacion_cliente`
- `trace_id`
- `wait_seconds` entre `1` y `15`

### Recomendación práctica

Si el comprobante viene desde WhatsApp, manda siempre:

- `cliente_telefono`
- `cliente_nombre`
- `producto_id`
- `idban`
- `valor`
- `foto`
- `external_reference`
- `trace_id`

### Ejemplo `multipart/form-data`

Campos de texto:

```text
cliente_telefono = 593998887777
cliente_nombre = Carlos Mena
producto_id = 15
idban = 2
valor = 4.50
numcomprobante = 998877
canal = whatsapp
external_reference = wamid.HBgLN...
trace_id = checkout-wa-00045
wait_seconds = 8
observacion_cliente = pago por netflix pantalla
```

Archivo:

```text
foto = <archivo jpg/png/webp>
```

### Cómo configurarla en n8n

Nodo `HTTP Request`:

- Method: `POST`
- URL: `{{$json.base_url}}/api/v2/payments/n8n/receipt-checkout`
- Send Body: `Form-Data` o `Multipart Form-Data`
- Headers:
  - `X-API-Key`
  - `Accept: application/json`
- Binary Property: la propiedad binaria donde n8n tenga la imagen

### Respuestas posibles

#### `duplicate_receipt`

El archivo o el número de comprobante ya fue usado antes.

```json
{
  "success": true,
  "status": "duplicate_receipt",
  "message": "Este comprobante ya fue evaluado previamente.",
  "data": {
    "existing_recarga": {
      "idrec": 201,
      "numcomprobante": "998877",
      "idestado": 3
    },
    "evaluation_status": "approved",
    "previous_result": "purchase_success"
  }
}
```

#### `verification_pending`

La recarga quedó creada, pero el verificador todavía no respondió.

```json
{
  "success": true,
  "status": "verification_pending",
  "message": "La recarga fue registrada y está pendiente de verificación.",
  "data": {
    "recarga": {
      "idrec": 202,
      "idestado": 1
    }
  }
}
```

#### `payment_rejected`

El comprobante fue rechazado.

```json
{
  "success": true,
  "status": "payment_rejected",
  "message": "El comprobante fue evaluado y la recarga fue rechazada."
}
```

#### `balance_insufficient`

La recarga fue aprobada, pero el saldo todavía no alcanza.

```json
{
  "success": true,
  "status": "balance_insufficient",
  "message": "La recarga fue aprobada, pero el saldo actual no alcanza para completar la compra.",
  "data": {
    "cliente": {
      "idcli": 14,
      "saldo": 3.00
    },
    "producto": {
      "id": 15,
      "precio": 4.50
    }
  }
}
```

#### `purchase_success`

La compra fue completada y la respuesta incluye la entrega.

```json
{
  "success": true,
  "status": "purchase_success",
  "message": "Compra completada correctamente.",
  "data": {
    "venta": {
      "idven": 411,
      "total": 4.50
    },
    "entrega": [
      {
        "servicio": "Netflix",
        "usuario": "correo@dominio.com",
        "clave": "clave123",
        "perfil": 2,
        "pin": "4455",
        "vence": "2026-05-10"
      }
    ]
  }
}
```

#### `stock_pending_manual`

El pago fue aprobado, pero no había stock automático.

```json
{
  "success": true,
  "status": "stock_pending_manual",
  "message": "El pago fue aprobado, pero no hay cuentas disponibles ahora mismo. Se notificó al equipo para entrega manual.",
  "data": {
    "pedido": {
      "id": 88,
      "producto": "Netflix pantalla"
    }
  }
}
```

#### `order_pending`

Producto no inmediato, queda pedido manual.

```json
{
  "success": true,
  "status": "order_pending",
  "message": "La recarga fue aprobada y el pedido quedó registrado para atención manual."
}
```

#### `verification_dispatch_failed`

La recarga se guardó, pero el disparo a verificación falló.

```json
{
  "success": false,
  "status": "verification_dispatch_failed",
  "message": "La recarga fue registrada, pero no se pudo disparar la verificación automática."
}
```

### Qué debe hacer n8n según el `status`

- `duplicate_receipt`: informar que ese comprobante ya fue procesado
- `verification_pending`: informar que el pago está en revisión
- `payment_rejected`: informar rechazo y pedir nuevo comprobante
- `balance_insufficient`: informar saldo faltante
- `purchase_success`: entregar credenciales o mensaje final de compra exitosa
- `stock_pending_manual`: informar que un asesor completará la entrega
- `order_pending`: informar que quedó pedido para atención manual
- `verification_dispatch_failed`: dejar alerta interna o reintento

---

## 9. Errores comunes

### 422 en `ingest`

Causas comunes:

- faltó `canal`
- faltó `canal_user_id`
- no mandaste ni `mensaje` ni `contenido` ni `media_url`
- `tipo_contenido` inválido

### 422 en `context`

Causas comunes:

- no mandaste `idconv`
- o faltó `canal` / `canal_user_id`
- `trigger_idmsg` no existe

### 404 en `context` o `respond`

La conversación no pudo resolverse.

Normalmente pasa porque:

- no corriste `ingest` antes
- mandaste un `idconv` incorrecto
- cambió `canal_user_id`

### 422 en `receipt-checkout`

Causas comunes:

- faltó `producto_id`
- faltó `idban`
- faltó `valor`
- faltó `foto`
- no mandaste `idcli` ni `cliente_telefono`
- `foto` no fue enviada como binario multipart

---

## 10. Recomendación final para tu flujo actual

Si quieres replicar exactamente tu patrón viejo de varios mensajes seguidos tipo:

```text
Hola
Quiero netflix
por favor
cuanto es?
```

Entonces en n8n haz esto:

1. cada mensaje entrante llama a `chat/router/ingest`
2. usa `Wait 35s`
3. llama a `chat/router/context` con `idconv` y `trigger_idmsg`
4. solo si `debe_responder = true`, responde
5. envía el mensaje por tu proveedor
6. registra el envío con `chat/router/respond`

Ese es el reemplazo correcto y completo de tu viejo MySQL `insert + select + check last + group messages`.

---

## 11. APIs cubiertas en esta guía

- `POST /api/v2/chat/router/ingest`
- `GET|POST /api/v2/chat/router/context`
- `POST /api/v2/chat/router/respond`
- `POST /api/v2/chat/router/handoff`
- `POST /api/v2/payments/n8n/receipt-checkout`

Si después quieres, la siguiente guía la hago para estas dos adicionales del mismo bloque:

- `POST /api/v2/chat/router/memory/summary`
- `POST /api/v2/chat/router/memory/contact`
