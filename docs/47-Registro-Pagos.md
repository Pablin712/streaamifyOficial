# Pagos de comprobantes desde WhatsApp
revisemos las apis de aprobar y rechazar recarga, algo así quiero hacer pero estos datos vienen de whatsapp.

Cuando un cliente realiza el pago, normalmente lo revisa un empleado humano, bueno, la idea del agente de whatsapp es que lo haga todo, entonces, quiero controlar que los comprobantes de pago siempre sean distintos.
Digamos que un cliente paga, manda foto del comprobante en donde se ven los datos, para que el agente no confunda ese comprobante o no lo vuelva a aprobar si es que se manda el mismo, tiene que darse cuenta que a ese comprobante ya lo evaluó, digamos, lo aprueba hoy, y mañana el cliente manda el mismo, entonces el agente responde que ya vio ese comprobante.

Se me ocurre que, los pagos que se hagan, se registran en recargas, se guarda la imagen y todo el proceso sería ese, como si un usuario hiciera una recarga en el sitio web, y la compra que se haga, así como funciona el sistema actualmente.

Si, eso mejor, adaptarle a lo que ya se tiene.

Ayúdame con una api para subir datos del comprobante de pago.

como ya existe el verificador:

1. Cliente paga por whatsapp, pasa por camino de tipo de mensaje imagen (n8n) ya hecho.
2. n8n usa la api de creación de recarga (por hacer), en donde se sube la imagen, y datos para crear la recarga en el sistema (n8n tiene la imagen en binario)
3. esperar a que el verificador de pagos apruebe o rechace, ya que al crear una recarga este se activa (revisar la lógica del controller de recarga cuando un cliente la crea desde el sitio web)
4. Realizar la venta automática del producto deseado por el cliente. (revisar lógica de compra automática o pedido de shopcontroller)

## ejemplo de chat
- cliente: Hola quiero netflix y spotify
- agente: están disponibles estos productos: (*busca productos netflix y spotify en combo e individual)
Netflix: $3,75
Spotify: $2,75
Netflix + spotify: $5,99
Netflix + spotify 2m: $11
etc
- cliente: ok como lo adquiero?
- agente: para realizar la compra, confírmame tu pago (comprobante) y datos para registrar compra: nombre y apellido, producto para confirmar.
- agente: escoge un método de pago para pasarte los datos a pagar. Tenemos Banco Pichincha, guayaquil, produbanco, binance, paypal, etc.
- cliente: tiene internacional?
- agente: *busca en la api métodos de pago si hay internacional y la encuentra*
- agente: Si, estos son los datos: Banco internacional\nPablo Jiménez\n192839123
- cliente: ya ok ya le transfiero
- agente: estoy al pendiente4
- ciente: *imagen de comprobante de $5,99*
- cliente: aquí está compa
- cliente: a nombre de Pedro Galez, la de netflix y spotify por favor
- agente: espera unos segundos para completar tu compra
    *crea la recarga subiendo la foto al sistema,
    sistema se encarga de aprobar o rechazar,
    cuando ya se aprobó (3 segundos capaz) entonces hacer la compra con la api (el proceso de compra de cliente ya existe: comprobar saldo, descontar saldo, asignar cuenta, etc, solo falta crear la api, la lógica ya existe)*
    la compra ya está hecha, leer la respuesta que entregó la api de compra para poder responder al cliente. Aquí termina la api completa
- agente: tu compra se realizó correctamente, tu producto:
    Netflix
    usuario:
    clave:
    ... Spotify, etc...
    Muchas gracias por tu compra.
- cliente: muchas gracias, muy amable

Así sería el flujo, por lo que necesitaría tan solo una api, y esa api hace el proceso crear la recarga, y crear la compra, espera la respuesta, y le informa al cliente, habiendo posibles respuestas:

1. saldo insuficiente
2. compra exitosa, datos de tu producto
3. cuentas no disponibles (informar con notificacion a empleados para que estos atiendan inventario y entreguen cuenta)

## API propuesta implementada

Se agregó un endpoint único para n8n/WhatsApp:

`POST /api/v2/payments/n8n/receipt-checkout`

Requiere autenticación con `X-API-Key` o `api_key`.

### Qué hace esta API

1. resuelve o crea al cliente por `idcli` o por teléfono;
2. guarda la imagen del comprobante en `recargas`;
3. calcula una huella SHA-256 del archivo para detectar comprobantes repetidos;
4. evita registrar otra vez el mismo comprobante si ya fue evaluado;
5. dispara la verificación automática existente;
6. espera unos segundos el resultado;
7. si la recarga queda aprobada, intenta comprar el producto;
8. si no hay stock inmediato, crea un `pedido` y notifica a empleados;
9. responde a n8n con un estado final usable por el agente.

### Request esperada

Se envía como `multipart/form-data` porque la imagen va en binario.

Campos:

- `producto_id` obligatorio.
- `idban` obligatorio.
- `valor` obligatorio.
- `foto` obligatoria.
- `idcli` opcional.
- `cliente_telefono` opcional pero obligatorio si no mandas `idcli`.
- `cliente_nombre` opcional.
- `cliente_email` opcional.
- `numcomprobante` opcional. Si no se manda, el sistema genera uno con base en la huella del archivo.
- `external_reference` opcional para guardar el id del mensaje o evento externo.
- `wait_seconds` opcional, recomendado entre `6` y `10`.

### Estados de respuesta

- `duplicate_receipt`: ese comprobante ya fue evaluado antes.
- `verification_pending`: la recarga quedó creada pero aún no fue decidida.
- `payment_rejected`: el verificador rechazó la recarga.
- `balance_insufficient`: la recarga fue aprobada pero el saldo aún no alcanza para el producto.
- `purchase_success`: la compra se hizo y se devuelve la entrega.
- `stock_pending_manual`: el pago fue aprobado, pero no había stock inmediato; se creó pedido y se notificó a empleados.
- `order_pending`: el producto no es de entrega inmediata y se dejó pedido para atención manual.

### Ejemplo conceptual para n8n

- Node webhook/media de WhatsApp recibe imagen.
- Node HTTP `receipt-checkout` manda:
    - `cliente_telefono`
    - `cliente_nombre`
    - `producto_id`
    - `idban`
    - `valor`
    - `foto` binaria
    - `external_reference`
- Según `status`, el agente responde:
    - `duplicate_receipt` -> “ese comprobante ya fue revisado”
    - `payment_rejected` -> “tu pago fue rechazado”
    - `purchase_success` -> entrega usuario/clave/pin
    - `stock_pending_manual` -> “tu pago fue aprobado y un asesor completará la entrega”

## Mejora importante para comprobantes repetidos

Ya no se depende solo de `numcomprobante`.

Ahora `recargas` también guarda:

- `comprobante_hash`
- `origen`
- `external_reference`
- `metadata`

Con eso, si mañana el cliente manda exactamente la misma imagen, el sistema puede detectar que ya fue evaluada aunque cambie el texto acompañante.
