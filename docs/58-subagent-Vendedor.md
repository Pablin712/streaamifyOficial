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

1. Llama a `GET /api/v2/chat/assistant/cliente?telefono=...`
2. Lee el campo `saldo` del cliente (ya retornado por esa API).
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
   ├── tipo 2 → POST /api/v2/chat/assistant/pedido
   └── tipo 3 → pedir datos extra, luego POST /api/v2/chat/assistant/pedido
   │
9. Confirmar compra al cliente
```

---

## 5. APIs disponibles (ya existen)

| Método | Ruta | Uso |
|--------|------|-----|
| GET | `/api/v2/chat/assistant/cliente?telefono=...` | Obtener datos del cliente + saldo |
| GET | `/api/v2/catalogo` | Catálogo completo de productos y combos |
| GET | `/api/v2/catalogo?servicio=netflix` | Catálogo filtrado por servicio |
| GET | `/api/v2/precios` | Precios generales (1 mes, 1 dispositivo) |
| GET | `/api/v2/precios/servicio/{servicio}` | Planes detallados de un servicio |
| GET | `/api/v2/metodos-pago` | Métodos de pago disponibles |
| GET | `/api/v2/chat/assistant/cliente/{idcli}/recargas` | Últimas recargas del cliente |
| POST | `/api/v2/chat/assistant/venta` | Crear venta automática (tipo 1, inmediata) |
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
  "accion_tipo": "ninguna|buscar_producto|confirmar_producto|crear_venta|crear_pedido|pedir_datos_extra|saldo_insuficiente|handoff",
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
4. Confirma el producto con el cliente antes de crear la venta.
5. Si el saldo alcanza, crea la venta o pedido con la herramienta correspondiente.
6. Si el saldo no alcanza, informa el saldo actual y el precio del producto, y ofrece opciones.
7. Si el producto es tipo personalizado (Spotify con cuenta propia), pide usuario y contraseña antes de crear el pedido.
8. Si el historial contiene <comprobante>, el cliente ya pagó — no vuelvas a cobrar.
9. No inventes precios ni disponibilidad.
10. Si el cliente pide humano o hay un problema de pago rechazado, escalar a handoff.

Herramientas disponibles:
- buscar cliente por teléfono (con saldo)
- consultar catálogo de productos
- consultar planes de un servicio
- crear venta (entrega inmediata)
- crear pedido (producto con datos extra)
- handoff a humano

Devuelve solo JSON con este formato:
{
  "subagente_codigo": "vendedor_cierre",
  "reply_text": "texto final para el cliente",
  "accion_tipo": "ninguna|buscar_producto|confirmar_producto|crear_venta|crear_pedido|pedir_datos_extra|saldo_insuficiente|handoff",
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
| 1 | Buscar cliente (con saldo) | `GET /api/v2/chat/assistant/cliente?telefono=...` |
| 2 | Catálogo de productos | `GET /api/v2/catalogo` |
| 3 | Planes por servicio | `GET /api/v2/precios/servicio/{servicio}` |
| 4 | Crear venta | `POST /api/v2/chat/assistant/venta` |
| 5 | Crear pedido | `POST /api/v2/chat/assistant/pedido` |
| 6 | Recargas del cliente | `GET /api/v2/chat/assistant/cliente/{idcli}/recargas` |
| 7 | Handoff a humano | `POST /api/v2/chat/router/handoff` |

---

## 11. Pendientes de implementación

Antes de activar este subagente en n8n se necesita:

- [x] **API recargas por cliente**: `GET /api/v2/chat/assistant/cliente/{idcli}/recargas`.
- [x] **API crear venta desde chat**: `POST /api/v2/chat/assistant/venta` con idemp fijo en 10.
- [x] **API crear pedido desde chat**: `POST /api/v2/chat/assistant/pedido` para tipo 2 y 3.
- [ ] **Nodo Set Fields en n8n para comprobantes**: sobrescribir el `content` de mensajes de imagen que sean comprobantes con formato `<comprobante>...</comprobante>`.
- [ ] **Actualizar seeder** `vendedor_cierre` con los nuevos tools y acciones del flujo real.
