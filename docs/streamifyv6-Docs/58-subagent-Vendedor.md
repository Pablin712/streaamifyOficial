# Subagente: Vendedor Cierre

## Definición en seeder

```php
'codigo'      => 'vendedor_cierre',
'nombre'      => 'Vendedor de Cierre',
'tipo'        => 'vendedor',
'descripcion' => 'Cierra ventas, sugiere plan o combo y conduce a pago.',
'prompt_base' => 'Prioriza cierre, claridad, margen y una sola llamada a la accion por mensaje.',
'criterios'   => ['intenciones' => ['precio', 'plan', 'combo', 'comprar']],
'prioridad'   => 20,
```

---

## 1. Rol y función

El subagente `vendedor_cierre` es el responsable de:

1. Identificar exactamente qué producto desea el cliente (servicio, meses, dispositivos).
2. Buscar en el catálogo el producto más cercano a lo pedido.
3. Verificar si el cliente tiene saldo suficiente para pagar.
4. Crear la venta (entrega inmediata) o el pedido (producto que requiere datos extra).
5. Confirmar la compra al cliente con los detalles finales.

Este subagente NO valida comprobantes. Eso lo hace el verificador de pagos. El vendedor cierre solo lee el saldo del cliente para saber si puede proceder.

Características de comunicación:
- Mensajes cortos, directos, una acción por mensaje.
- No repetir información que ya está en el historial.
- No pedir datos que ya se tienen en el contacto.
- Máximo 1–2 emojis.
- Tono seguro, cálido, orientado al cierre.

---

## 2. Tipos de producto

Los productos en el sistema se clasifican por `tipo_producto_id`:

| tipo_producto_id | Tipo          | Descripción                                             |
|-----------------|---------------|---------------------------------------------------------|
| 1               | Entrega inmediata | Se puede vender con `POST /api/v2/chat/assistant/venta` usando saldo del cliente |
| 2               | Pedido        | Se crea con `POST /api/v2/chat/assistant/pedido` |
| 3               | Personalizado | El cliente aporta credenciales propias (ej. Spotify)    |

Ejemplos:
- Netflix compartido → tipo 1 (inmediata)
- Combo Netflix + Disney → tipo 1 (inmediata)
- Spotify con cuenta propia → tipo 3 (el agente pide usuario y contraseña antes de crear pedido)

---

## 3. Lógica de identificación del pago

### Problema
El agente no puede saber directamente si el cliente ya pagó. El historial puede tener mensajes de imagen de comprobante, pero el agente no debería volver a cobrar.

### Solución propuesta (n8n)

Cuando el nodo de n8n detecta que el cliente envió una imagen de comprobante:

1. El nodo `Analyze Image` extrae los datos del comprobante.
2. Un nodo `Set Fields` sobrescribe el `content` del mensaje, cambiándolo de:
   ```
   <imagen> descripción de imagen </imagen>
   ```
   a:
   ```
   <comprobante> valor: $5.00 | banco: Pichincha | fecha: 01/05/2026 | referencia: 1234 </comprobante>
   ```
3. Ese `content` formateado entra al historial del chat y llega al subagente con el contexto.

Esto permite que el agente entienda que el cliente ya envió un comprobante, sin necesidad de volver a pedirlo.

### Verificación de saldo (lógica del agente)

El agente no valida el comprobante. Solo hace esto:

1. Llama a `GET /api/v2/chat/assistant/cliente?telefono=...&include_renovables=1`
2. Lee el campo `saldo` del cliente y el bloque `ventas_renovables` (incluye `idven`, `iddet`, `estado`, `dias_restantes`).
3. Compara `saldo` con el precio del producto deseado.
4. Si `saldo >= precio` → puede proceder a crear la venta.
5. Si `saldo < precio` → informa y da opciones (ver sección de casos especiales).

---

## 4. Flujo de trabajo del agente

```
1. Llega contexto: mensaje_agrupado, historial, contacto, memoria_negocio
   │
2. ¿El historial contiene <comprobante>?
   ├── Sí → el cliente ya subió comprobante, no cobrar de nuevo
   └── No → continuar normalmente
   │
3. Buscar cliente por teléfono → obtener saldo actual
   │
4. Identificar producto deseado
   ├── Servicio (Netflix, Disney, Spotify, etc.)
   ├── Meses (default 1 si no especifica)
   └── Dispositivos (default 1 si no especifica)
   │
5. Buscar en catálogo el producto más cercano
   │
6. Confirmar producto con cliente (precio, nombre, duración)
   │
7. Verificar saldo
   ├── Saldo suficiente → crear venta o pedido
   └── Saldo insuficiente → informar y dar opciones
   │
8. Crear la venta o pedido según tipo_producto_id
   ├── tipo 1 → POST /api/v2/chat/assistant/venta
   ├── renovación confirmada → POST /api/v2/chat/assistant/venta/renovar
   ├── tipo 2 → POST /api/v2/chat/assistant/pedido
   └── tipo 3 → pedir datos extra, luego POST /api/v2/chat/assistant/pedido
   │
9. Confirmar compra al cliente
```

---

## 5. APIs disponibles (ya existen)

| Método | Ruta | Uso |
|--------|------|-----|
| GET | `/api/v2/chat/assistant/cliente?telefono=...&include_renovables=1` | Obtener datos del cliente + saldo + ventas/detalles renovables (`idven`, `iddet`) |
| GET | `/api/v2/catalogo` | Catálogo completo de productos y combos |
| GET | `/api/v2/catalogo?servicio=netflix` | Catálogo filtrado por servicio |
| GET | `/api/v2/precios` | Precios generales (1 mes, 1 dispositivo) |
| GET | `/api/v2/precios/servicio/{servicio}` | Planes detallados de un servicio |
| GET | `/api/v2/metodos-pago` | Métodos de pago disponibles |
| GET | `/api/v2/chat/assistant/cliente/{idcli}/recargas` | Últimas recargas del cliente |
| POST | `/api/v2/chat/assistant/venta` | Crear venta automática (tipo 1, inmediata) |
| POST | `/api/v2/chat/assistant/venta/renovar` | Renovar una venta existente descontando saldo |
| POST | `/api/v2/chat/assistant/pedido` | Crear pedido (tipo 2 y 3) |
| POST | `/api/v2/chat/router/handoff` | Pasar a humano |

---

## 6. APIs nuevas implementadas para vendedor cierre

Estas APIs ya están disponibles en backend:

### 6.1 Recargas del cliente
```
GET /api/v2/chat/assistant/cliente/{idcli}/recargas
```
Retorna las últimas recargas del cliente con su estado (`pendiente`, `aprobado`, `rechazado`).
Útil para que el agente confirme si hubo un pago reciente aprobado.

### 6.2 Crear venta desde chat
```
POST /api/v2/chat/assistant/venta
```
Crea una venta de entrega inmediata usando el saldo del cliente.
Basada en la lógica de `ShopController` y `TecnicoVentasController::crear`.

Body esperado:
```json
{
  "idcli": "CLI001",
  "idproducto": "PROD_NETFLIX_1M_1D",
  "meses": 1,
  "dispositivos": 1
}
```

El sistema internamente busca un perfil disponible, asigna, descuenta saldo y registra la venta.

### 6.3 Crear pedido desde chat
```
POST /api/v2/chat/assistant/pedido
```
Para productos tipo 2 (pedido) o tipo 3 (personalizado).

Body esperado:
```json
{
  "idcli": "CLI001",
  "idproducto": "PROD_SPOTIFY_PROPIA",
  "datos_extra": {
    "usuario": "cliente@gmail.com",
    "contrasena": "abc123"
  },
  "notas": "Cliente quiere Spotify con su propia cuenta"
}
```

### 6.4 Renovar venta desde chat
```
POST /api/v2/chat/assistant/venta/renovar
```
Renueva una venta ya existente para el mismo cliente usando saldo disponible.

Body esperado:
```json
{
   "idven": "VEN-045",
   "idcli": "CLI001",
   "meses": 2,
   "detalles": [90]
}
```

`detalles` es opcional:
- si se envía: renovación parcial (solo esos `iddet`),
- si no se envía: renovación completa de todos los detalles activos de la venta.

Reglas internas del backend:
- valida que la venta pertenezca al cliente;
- toma los detalles activos de la venta original;
- permite seleccionar detalles puntuales para renovar (`detalles`);
- calcula el precio mensual base usando el producto mensual exacto si existe; si no, usa la suma de los detalles activos;
- multiplica por los meses solicitados;
- verifica saldo del cliente;
- desactiva solo los detalles renovados en la venta anterior;
- crea una nueva venta separada con solo los detalles renovados y nuevas fechas;
- descuenta el saldo del cliente.

Usa esta API solo cuando el cliente ya confirmo que quiere renovar y ya tienes `idven`, `idcli`, `meses` y opcionalmente `detalles` para renovación parcial.

---

## 7. Casos especiales

### Saldo insuficiente
Si `saldo < precio_producto`:
```
Tu saldo actual es $X.XX. El producto que elegiste cuesta $Y.YY.

Posibles situaciones:
• Tu pago puede estar en revisión — en breve se confirmará.
• Si hubo un error en el comprobante, puedes enviarlo nuevamente.
• Si necesitas ayuda, puedo pasarte con un asesor humano.

¿Qué prefieres hacer?
```
Luego ofrecer: `escalar_humano: true` si el cliente lo solicita.

### Producto personalizado (tipo 3)
Ejemplo: Spotify con cuenta propia.

Flujo:
1. Cliente pide Spotify.
2. Agente pregunta: "¿Deseas con tu propia cuenta de Spotify o te asigno una compartida?"
3. Si responde "con mi cuenta" → pedir usuario y contraseña de Spotify.
4. Una vez que el cliente los proporcione → crear pedido con `datos_extra`.

### Pago rechazado por verificador
Si el saldo sigue en $0 aunque el cliente dijo que pagó:
- El verificador puede haber rechazado el comprobante.
- El agente informa al cliente y escala a humano para revisión manual.

---

## 8. Formato de respuesta JSON

```json
{
  "subagente_codigo": "vendedor_cierre",
  "reply_text": "Texto final para el cliente",
   "accion_tipo": "ninguna|buscar_producto|confirmar_producto|crear_venta|renovar_venta|crear_pedido|pedir_datos_extra|saldo_insuficiente|handoff",
  "accion_requerida": false,
  "accion_payload": null,
  "escalar_humano": false,
  "motivo_humano": null,
  "confianza": 0.9
}
```

Valores de `accion_tipo`:
- `ninguna` — solo responde, nada que hacer
- `buscar_producto` — necesita buscar en catálogo
- `confirmar_producto` — presentó opciones, espera confirmación del cliente
- `crear_venta` — tiene producto confirmado + saldo suficiente → crear venta
- `renovar_venta` — cliente ya confirmó renovar una venta existente
- `crear_pedido` — producto tipo 2 o 3, con datos listos → crear pedido
- `pedir_datos_extra` — producto personalizado, falta usuario/contraseña u otro dato
- `saldo_insuficiente` — saldo no alcanza, informa al cliente
- `handoff` — pasar a humano

---

## 9. Prompt recomendado del nodo n8n

### Text (Prompt)

```text
Cierra esta venta con el contexto disponible y devuelve JSON.

mensaje_agrupado: {{ $('get context').item.json.data.mensaje_agrupado }}
historial: {{ JSON.stringify($('get context').item.json.data.historial_reciente) }}
memoria_negocio: {{ JSON.stringify($('get context').item.json.data.memoria_negocio) }}
contacto: {{ JSON.stringify($('get context').item.json.data.contacto) }}
conversacion: {{ JSON.stringify($('get context').item.json.data.conversacion) }}

Prioridad critica:
- Si el cliente habla de renovar/extender/reactivar una venta existente, NO es una venta nueva.
- En renovacion, usa `renovar_venta` y nunca `crear_venta`.
```

### System Message

```text
Eres el subagente vendedor de cierre de Streamify.

Objetivo:
- Identificar el producto exacto que desea el cliente.
- Verificar su saldo para saber si puede comprar.
- Crear la venta o el pedido según el tipo de producto.
- Mensajes cortos, una acción por mensaje, tono seguro y cálido.

Reglas de comportamiento:
1. Usa la herramienta de cliente para obtener el saldo actual.
2. Usa el catálogo para encontrar el producto más cercano a lo que pide el cliente.
3. Si el cliente no especifica meses, asume 1. Si no especifica dispositivos, asume 1.
4. Antes de ejecutar una accion, clasifica SIEMPRE la intencion en una de estas rutas:
   - RUTA A: Compra nueva
   - RUTA B: Renovacion
   - RUTA C: Pedido/personalizado
5. RUTA B (Renovacion) tiene prioridad si detectas cualquiera de estas señales:
   - palabras: renovar, renovacion, extender, prorrogar, reactivar, seguir con la misma cuenta
   - presencia de `idven` o seleccion de `iddet` de una venta previa
   - cliente pide renovar solo algunos servicios/perfiles de una venta
6. Si cae en RUTA B:
   - NO uses `crear_venta`
   - NO uses catalogo para decidir compra nueva
   - usa `renovar_venta` con `idven`, `idcli`, `meses` y opcional `detalles`
   - `idven` y `iddet` se obtienen desde la API de cliente, no preguntando codigos tecnicos al cliente
7. RUTA A (Compra nueva): usa `crear_venta` solo cuando NO hay señales de renovacion.
8. RUTA C (Pedido/personalizado): usa `crear_pedido` cuando el producto no es de entrega inmediata o requiere datos extra.
9. Si el saldo no alcanza, informa saldo/faltante y ofrece opciones.
10. Si el historial contiene <comprobante>, el cliente ya pagó y no debes volver a cobrar.
11. No inventes precios, estados ni disponibilidad.
12. Si hay conflicto de intencion o error operacional repetido, escalar a handoff.

Reglas anti-error (obligatorias):
- Nunca llames `crear_venta` si la intencion es renovacion.
- Si el cliente menciona renovacion y tienes `idven`, la accion correcta es `renovar_venta`.
- Si un intento previo con `crear_venta` devolvio "El producto no esta disponible para venta inmediata" y el contexto era renovacion, corrige ruta a `renovar_venta`.
- Nunca pidas al cliente `idven`, `iddet`, IDs internos, ni codigos tecnicos.
- Para renovar, primero consulta `GET /api/v2/chat/assistant/cliente?telefono=...&include_renovables=1` y toma de ahi los datos internos.

Checklist minimo antes de `renovar_venta`:
- `idcli` confirmado
- `idven` confirmado desde la API de cliente (no desde texto del cliente)
- `meses` confirmado
- si renovacion parcial: `detalles` (iddet) seleccionados desde `ventas_renovables.detalles` de la API

Regla de lenguaje hacia cliente:
- En vez de pedir "codigo de compra" o "idven", pregunta en lenguaje natural: que servicio quiere renovar y por cuantos meses.
- Si hay varios servicios activos, muestra opciones por nombre (Netflix, Spotify, etc.) y deja que el cliente elija; luego el agente mapea esa eleccion a `iddet` internamente.

Herramientas disponibles:
- buscar cliente por teléfono (con saldo)
- consultar catálogo de productos
- consultar planes de un servicio
- crear venta (entrega inmediata)
- renovar venta existente
- crear pedido (producto con datos extra)
- handoff a humano

Devuelve solo JSON con este formato:
{
  "subagente_codigo": "vendedor_cierre",
  "reply_text": "texto final para el cliente",
   "accion_tipo": "ninguna|buscar_producto|confirmar_producto|crear_venta|renovar_venta|crear_pedido|pedir_datos_extra|saldo_insuficiente|handoff",
  "accion_requerida": false,
  "accion_payload": null,
  "escalar_humano": false,
  "motivo_humano": null,
  "confianza": 0.9
}
```

---

## 10. Herramientas n8n recomendadas

| # | Herramienta | Endpoint |
|---|-------------|----------|
| 1 | Buscar cliente (con saldo y renovables) | `GET /api/v2/chat/assistant/cliente?telefono=...&include_renovables=1` |
| 2 | Catálogo de productos | `GET /api/v2/catalogo` |
| 3 | Planes por servicio | `GET /api/v2/precios/servicio/{servicio}` |
| 4 | Crear venta | `POST /api/v2/chat/assistant/venta` |
| 5 | Renovar venta | `POST /api/v2/chat/assistant/venta/renovar` |
| 6 | Crear pedido | `POST /api/v2/chat/assistant/pedido` |
| 7 | Recargas del cliente | `GET /api/v2/chat/assistant/cliente/{idcli}/recargas` |
| 8 | Handoff a humano | `POST /api/v2/chat/router/handoff` |

### Descripcion sugerida para la tool `renovar_venta`

```text
Renueva una venta existente usando idven, idcli, meses y opcionalmente detalles (iddet). Calcula el total mensual segun el producto o base actual de la venta, valida el saldo del cliente, desactiva solo los detalles renovados y crea una nueva venta de renovacion con las nuevas fechas. Si no se envian detalles, renueva todos los activos de esa venta. Usala solo cuando el cliente ya confirmo que desea renovar.
```

### Como usar la tool `renovar_venta`

1. Primero identifica la venta que el cliente quiere renovar.
2. Confirma con el cliente cuántos meses quiere renovar.
3. Asegurate de tener `idven`, `idcli` y `meses`. Si es parcial, agrega `detalles` con los `iddet` elegidos.
4. Ejecuta la tool una sola vez cuando la decisión ya esté tomada.
5. Si responde `success: true`, confirma al cliente la renovación, el total cobrado y la nueva fecha.
6. Si responde saldo insuficiente, no fuerces la compra; informa el faltante o pasa a cobranzas si corresponde.
7. Si la venta no pertenece al cliente o no tiene detalles activos, no inventes solución: explica el problema y escala si hace falta.

---

## 11. Pendientes de implementación

Antes de activar este subagente en n8n se necesita:

- [x] **API recargas por cliente**: `GET /api/v2/chat/assistant/cliente/{idcli}/recargas`.
- [x] **API crear venta desde chat**: `POST /api/v2/chat/assistant/venta` con idemp fijo en 10.
- [x] **API renovar venta desde chat**: `POST /api/v2/chat/assistant/venta/renovar`.
- [x] **API crear pedido desde chat**: `POST /api/v2/chat/assistant/pedido` para tipo 2 y 3.
- [ ] **Nodo Set Fields en n8n para comprobantes**: sobrescribir el `content` de mensajes de imagen que sean comprobantes con formato `<comprobante>...</comprobante>`.
- [ ] **Actualizar seeder** `vendedor_cierre` con los nuevos tools y acciones del flujo real.
