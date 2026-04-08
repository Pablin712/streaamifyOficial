# Agente vendedor para WhatsApp y Facebook

## Objetivo real

La meta no es solo tener un bot que responda, sino un agente comercial operativo que:

- salude y clasifique al cliente;
- responda rápido con mensajes cortos y precisos;
- informe precios, combos, planes y métodos de pago;
- negocie dentro de límites de rentabilidad;
- reciba comprobantes e imágenes;
- valide pagos con apoyo del sistema existente;
- registre la venta y deje trazabilidad;
- recuerde el contexto de cada cliente para continuar la conversación después.

## Lo que ya existe en el proyecto

Tu sistema ya tiene piezas muy valiosas para no empezar desde cero:

### 1. Base de chat y conversaciones

Ya existe un módulo de conversaciones y mensajes, con soporte para cliente, empleado y bot/IA.

- Conversaciones: tabla `conversaciones`
- Mensajes: tabla `mensajes`
- Endpoints de chat para cliente, anónimo y n8n/IA

### 2. Endpoints para IA comercial

Ya tienes endpoints útiles para un asistente:

- consultar servicios;
- consultar precios;
- buscar clientes;
- revisar historial de ventas;
- consultar perfiles disponibles.

### 3. Endpoints públicos para mensajes comerciales

Ya existe generación de mensajes de:

- precios;
- combos;
- planes por servicio;
- métodos de pago.

### 4. Registro y detalle de ventas

También ya hay lógica fuerte alrededor de ventas y mensajes de entrega, lo que ayuda a cerrar el ciclo después de cobrar.

## Conclusión inicial

Tu problema ya no es "crear un chatbot". Tu problema real es construir una capa de automatización comercial encima de una base que ya existe.

Eso es bueno, porque reduce bastante el trabajo.

## Qué debe hacer el agente

Para que este agente venda de verdad, conviene dividirlo en capacidades concretas.

### A. Atención inicial

El agente debe:

- detectar si el cliente llega nuevo o ya había escrito antes;
- saludar con tono corto;
- detectar intención: precio, combo, renovación, soporte, pago, estado del servicio, reclamo;
- pedir solo el dato faltante mínimo.

Ejemplo de estilo deseado:

- corto;
- sin párrafos largos;
- una sola pregunta por turno;
- directo a cerrar venta.

### B. Venta guiada

El agente debe poder:

- sugerir el mejor plan según necesidad;
- ofrecer combo si mejora margen;
- proponer alternativa si el cliente pide algo muy barato;
- aplicar descuentos controlados;
- intentar cierre con CTA corto.

### C. Cobro

Debe poder:

- enviar métodos de pago;
- pedir comprobante;
- recibir imagen;
- validar el pago o enviarlo al verificador;
- avisar si falta confirmación humana.

### D. Postventa y registro

Debe poder:

- registrar que hubo intención de compra;
- registrar que hubo pago;
- registrar venta cerrada;
- dejar notas para seguimiento;
- disparar mensaje de entrega o renovación.

## Arquitectura propuesta

## 1. Capa de canal

Aquí entran los mensajes desde fuera.

Posibles entradas:

- WhatsApp Cloud API;
- Messenger de Facebook;
- formularios o leads de anuncios de Facebook;
- widget web de chat.

### Punto importante

No es lo mismo que un cliente llegue por anuncio que por chat.

- Si llega por click-to-WhatsApp o Messenger, ya tienes una conversación iniciada.
- Si llega por Lead Ads, normalmente recibes datos del lead y luego debes abrir el contacto por un flujo permitido, con consentimiento y según la ventana de mensajería del canal.

Primera decisión técnica importante: definir si tu agente va a atender principalmente:

1. WhatsApp;
2. Messenger;
3. ambos;
4. leads de formularios + seguimiento posterior.

## 2. Capa de normalización

Todos los canales deben terminar en el mismo formato interno.

Entrada ideal normalizada:

```json
{
	"canal": "whatsapp",
	"canal_user_id": "573001112233",
	"nombre": "Cliente Demo",
	"mensaje": "hola, precio de netflix",
	"tipo_contenido": "texto",
	"archivo_url": null,
	"timestamp": "2026-04-06T10:00:00Z"
}
```

Esto luego crea o reutiliza:

- cliente;
- conversación;
- mensaje;
- metadata del canal.

## 3. Capa de orquestación

Aquí conviene usar n8n como coordinador de flujo.

El flujo recomendado sería:

1. llega webhook del canal;
2. se normaliza el mensaje;
3. se guarda en Laravel;
4. se consulta contexto del cliente;
5. se llama al modelo IA con herramientas disponibles;
6. se valida la respuesta contra reglas de negocio;
7. se responde al canal;
8. se registra resultado y trazabilidad.

## 4. Capa de decisión del agente

El modelo no debe contestar libremente sin restricciones.

Debe trabajar con:

- prompt de sistema;
- herramientas/API concretas;
- memoria resumida del cliente;
- reglas de negocio duras.

La IA no debería inventar:

- precios;
- descuentos;
- disponibilidad;
- métodos de pago;
- estados de venta.

Todo eso debe salir de APIs o tablas internas.

## 5. Capa de herramientas del agente

Las herramientas mínimas del agente deberían ser estas:

### Herramientas ya casi listas o reutilizables

- `consultar_precios`
- `consultar_planes_por_servicio`
- `consultar_combos`
- `consultar_metodos_pago`
- `buscar_cliente`
- `consultar_historial_cliente`
- `consultar_perfiles_disponibles`

### Herramientas nuevas necesarias

- `crear_o_vincular_cliente_por_canal`
- `guardar_resumen_conversacion`
- `calcular_descuento_permitido`
- `registrar_intencion_compra`
- `registrar_comprobante`
- `validar_comprobante`
- `registrar_venta_desde_chat`
- `escalar_a_humano`
- `programar_seguimiento`

## 6. Capa de memoria

Sí, este agente necesita memoria, pero no una memoria libre e infinita.

La memoria correcta aquí es híbrida:

### Memoria estructurada

Guardar en base de datos campos concretos como:

- nombre del cliente;
- teléfono o identificador del canal;
- servicios preguntados;
- presupuesto estimado;
- objeciones detectadas;
- último precio ofrecido;
- descuento máximo ofrecible;
- estado del embudo;
- último método de pago enviado;
- último comprobante recibido;
- última venta cerrada;
- fecha del último contacto.

### Memoria resumida

Además guardar un resumen corto por conversación o cliente, por ejemplo:

"Cliente interesado en Netflix o combo familiar. Dice que su presupuesto máximo es 6. Ya se ofreció descuento de 1 dólar. Pidió banco Pichincha y quedó en pagar hoy."

### Regla importante

La memoria no debe reemplazar la base de datos transaccional.

- La memoria sirve para conversar mejor.
- La base de datos sirve para operar el negocio.

## Negociación y descuentos

Esta parte no debería quedar a criterio libre del modelo.

Debes definir una política exacta.

Ejemplo de política:

- precio base del producto;
- margen mínimo por producto o familia;
- descuento máximo absoluto;
- descuento máximo por tipo de cliente;
- si el cliente regatea más de cierto límite, ofrecer alternativa en vez de seguir bajando;
- si el producto tiene poca disponibilidad, no ofrecer descuento.

### Recomendación

Crear una tabla o configuración de reglas comerciales, por ejemplo:

- `precio_base`
- `precio_minimo`
- `descuento_maximo`
- `permite_combo`
- `prioridad_venta`
- `texto_cierre`

Entonces la IA no "decide descuentos" desde cero. La IA decide entre opciones permitidas.

## Imágenes y comprobantes

Como ya mencionas que tienes verificador de pagos, aquí el diseño correcto es:

1. el canal entrega imagen o archivo;
2. se guarda el adjunto;
3. se registra un mensaje de tipo `imagen`;
4. se manda al verificador;
5. el agente recibe un resultado estructurado;
6. según el resultado, responde:
	 - pago validado;
	 - pago dudoso;
	 - falta revisión humana.

La IA no debería ser el verificador final de pago. Debe apoyarse en un verificador dedicado.

## Registro de ventas

Aquí conviene separar claramente estados.

No todo lo conversado debe terminar como venta confirmada.

Estados recomendados:

- `nuevo_lead`
- `interesado`
- `cotizado`
- `negociando`
- `esperando_pago`
- `comprobante_recibido`
- `pago_validado`
- `venta_creada`
- `entregado`
- `requiere_humano`
- `perdido`

Con esto puedes medir conversión real del agente.

## Qué conviene construir primero

No intentes empezar con un agente "super inteligente" completo. Eso suele romperse rápido.

El orden correcto sería:

### Fase 1. Asistente comercial controlado

Objetivo:

- responder saludos;
- responder precios;
- responder combos;
- responder métodos de pago;
- detectar intención;
- escalar a humano cuando no sepa.

En esta fase el agente no negocia ni registra ventas automáticamente.

### Fase 2. Memoria y seguimiento

Objetivo:

- recordar contexto por cliente;
- reanudar conversaciones;
- registrar lead status;
- programar seguimiento automático.

### Fase 3. Negociación controlada

Objetivo:

- aplicar descuentos dentro de reglas;
- ofrecer alternativas según presupuesto;
- intentar cierre con mayor margen.

### Fase 4. Pago y comprobante

Objetivo:

- recibir imágenes;
- validar pago con el verificador;
- mover el lead a pago validado.

### Fase 5. Cierre operativo

Objetivo:

- registrar venta;
- disparar entrega;
- dejar trazabilidad completa.

## Qué ya tienes aprovechable hoy

Según el estado actual del proyecto, ya puedes reutilizar bastante:

- API de precios;
- API de métodos de pago;
- API interna de IA para servicios, precios, clientes y ventas;
- módulo de conversaciones y mensajes;
- rutas para que n8n lea mensajes pendientes y responda;
- base de ventas y mensajes de entrega.

## Gaps que aún faltan

Para llegar al agente vendedor completo todavía faltaría consolidar estas piezas:

### 1. Identidad unificada por canal

Necesitas mapear:

- `facebook_psid`;
- `whatsapp_phone`;
- `lead_id`;
- `idcli` interno.

### 2. Memoria comercial persistente

Necesitas tabla o esquema claro de memoria por cliente.

### 3. Política formal de descuentos

Sin esto, la IA puede regalar margen.

### 4. Estado comercial del embudo

Hoy chat y ventas existen, pero falta una capa intermedia de lead/comercial.

### 5. Soporte robusto para adjuntos del canal externo

Recibir, guardar, clasificar y reenviar imágenes/comprobantes.

### 6. Auditoría de decisiones del bot

Debes guardar:

- qué preguntó el cliente;
- qué herramientas usó la IA;
- qué precio ofreció;
- qué descuento aplicó;
- por qué escaló a humano.

## Cómo almacenar conversaciones de WhatsApp

Sí, lo que existe hoy parece orientado a chats propios del sistema, pero se puede adaptar bien.

La idea correcta no es guardar WhatsApp en un sistema aparte y luego duplicarlo todo. Lo correcto es usar tus tablas actuales de `conversaciones` y `mensajes` como núcleo interno, y agregar una capa de integración de canal.

### Qué no conviene hacer

No conviene:

- guardar solo texto plano en una tabla aparte;
- depender únicamente de n8n para persistencia;
- mezclar el número de WhatsApp directamente en `clientes` sin trazabilidad del canal;
- perder el `message_id` oficial de Meta.

Si haces eso, después será difícil:

- deduplicar mensajes;
- saber si un mensaje ya fue procesado;
- responder sobre el mismo hilo;
- manejar imágenes, estados y errores del canal.

## Diseño recomendado

### Opción correcta: reutilizar el núcleo actual + tablas de canal

Conservar:

- `conversaciones`
- `mensajes`

Agregar:

- una tabla para identificar al contacto externo;
- una tabla para mapear mensajes de WhatsApp con mensajes internos;
- metadata de canal en conversación y mensaje.

## Estructura propuesta

### 1. Tabla `chat_contactos_canal`

Sirve para vincular el contacto externo con tu cliente interno.

Campos sugeridos:

- `id`
- `canal` (`whatsapp`, `messenger`)
- `canal_user_id`: el identificador del usuario en el canal;
- `telefono_normalizado`
- `nombre_canal`
- `idcli` nullable;
- `metadata` json;
- `last_seen_at`
- timestamps

En WhatsApp Cloud API, el `canal_user_id` normalmente será el número de teléfono del remitente en formato internacional.

### 2. Tabla `chat_mensajes_canal`

Sirve para mapear cada mensaje externo con tu tabla `mensajes`.

Campos sugeridos:

- `id`
- `idmsg` interno;
- `idconv` interno;
- `canal`
- `direccion` (`inbound`, `outbound`)
- `external_message_id`
- `external_thread_id` nullable;
- `external_status` nullable (`sent`, `delivered`, `read`, `failed`)
- `media_url` nullable;
- `media_mime_type` nullable;
- `payload` json;
- timestamps

Esta tabla es la clave para no duplicar mensajes cuando Meta reintenta webhooks.

### 3. Extender `conversaciones`

No hace falta romper la tabla actual. Basta con agregar metadata útil.

Campos sugeridos:

- `canal_principal` nullable;
- `canal_contacto_id` nullable;
- `origen` nullable (`whatsapp_ads`, `whatsapp_directo`, `messenger`, `webchat`)

Si no quieres agregar columnas nuevas, parte de eso puede vivir en `metadata`, pero `canal_contacto_id` sí conviene que sea columna real si lo vas a consultar mucho.

### 4. Extender `mensajes`

Ya tienes `tipo_contenido`, `archivo_url` y `metadata`, lo cual ayuda bastante.

Solo te faltaría usar `metadata` de forma consistente para guardar cosas como:

- `canal`
- `external_message_id`
- `from`
- `to`
- `timestamp_canal`
- `media_id`
- `mime_type`

## Flujo de almacenamiento recomendado

### Entrada desde WhatsApp

1. Meta envía webhook a Laravel o n8n.
2. Se valida firma y estructura del payload.
3. Se extrae:
	- número del remitente;
	- nombre del perfil;
	- message id;
	- tipo de mensaje;
	- texto o media.
4. Se busca o crea `chat_contactos_canal`.
5. Se intenta vincular con `clientes` por teléfono.
6. Se busca o crea una `conversacion` abierta para ese contacto.
7. Se crea el `mensaje` interno.
8. Se crea el registro `chat_mensajes_canal` con `external_message_id`.
9. Se dispara el flujo de IA o atención humana.

### Salida hacia WhatsApp

1. Tu sistema genera una respuesta.
2. Se envía a Meta por API.
3. Se guarda el `mensaje` interno como salida.
4. Se guarda el `external_message_id` devuelto por Meta en `chat_mensajes_canal`.
5. Los estados posteriores (`delivered`, `read`, `failed`) actualizan esa misma fila.

## Relación con tu modelo actual

Tu modelo actual depende de `idcli`, y eso está bien para operación interna. El ajuste es este:

- una conversación de WhatsApp puede existir aunque aún no tengas cliente formal;
- cuando identifiques al cliente, enlazas el `chat_contactos_canal` o la `conversacion` con `idcli`.

Eso evita perder leads nuevos.

### Regla útil

No obligues a tener `idcli` desde el primer mensaje de WhatsApp.

Muchas conversaciones empiezan así:

- "hola";
- "precio de netflix";
- "me interesa un combo".

Eso todavía no siempre merece crear un cliente completo en tu módulo comercial principal.

## Estrategias posibles

### Estrategia A: crear cliente al primer mensaje

Ventajas:

- todo queda unido rápido;
- más simple para reportes.

Desventajas:

- ensucias la tabla de clientes con muchos leads fríos;
- luego debes depurar bastante.

### Estrategia B: crear contacto de canal primero y cliente después

Ventajas:

- separas lead frío de cliente real;
- mejor trazabilidad;
- mejor para anuncios y conversaciones de prueba.

Desventajas:

- agrega una tabla más.

### Recomendación

Para tu caso, la Estrategia B es la correcta.

Primero `chat_contactos_canal`, luego `clientes` cuando haya intención real, pago o venta.

## Tipos de contenido que deberías soportar

WhatsApp no es solo texto. Como mínimo deberías guardar:

- `texto`
- `imagen`
- `audio`
- `documento`
- `sticker` opcional
- `sistema`

Tu tabla `mensajes` ya tiene bastante de esto, pero te conviene normalizar `tipo_contenido` para que el agente sepa qué hacer con cada uno.

## Adjuntos

Para imágenes o comprobantes, no guardes solo la URL temporal de Meta.

Haz esto:

1. recibes webhook;
2. obtienes `media_id`;
3. descargas el archivo desde Meta;
4. lo guardas en tu storage o S3;
5. registras la URL propia en `archivo_url`;
6. guardas datos del archivo en `metadata`.

Porque los enlaces temporales de Meta suelen expirar.

## Detección de duplicados

Esto es obligatorio.

Meta puede reenviar eventos. Por eso debes tener índice único por:

- `canal`
- `external_message_id`

Si un webhook llega repetido, se ignora sin volver a crear el mensaje.

## Qué endpoint necesitas realmente

Tu chat actual tiene endpoints para cliente propio y anónimo, pero para WhatsApp necesitas otro tipo de entrada.

Necesitas al menos:

- `POST /api/v1/channels/whatsapp/webhook`
- `GET /api/v1/channels/whatsapp/webhook` para verificación inicial de Meta
- `POST /api/v1/channels/whatsapp/send` o un servicio interno equivalente

Y por dentro eso debe terminar llamando a la misma lógica de persistencia de conversaciones y mensajes.

## Recomendación de implementación en este proyecto

La forma más limpia sería esta:

### Paso 1

Crear tablas nuevas:

- `chat_contactos_canal`
- `chat_mensajes_canal`

### Paso 2

Crear un servicio nuevo, por ejemplo:

- `WhatsAppChannelService`

Responsabilidades:

- validar payload;
- normalizar mensaje;
- resolver contacto;
- crear o reutilizar conversación;
- persistir mensaje;
- descargar media si aplica;
- registrar mapping externo.

### Paso 3

Crear un controlador específico, por ejemplo:

- `WhatsAppWebhookController`

### Paso 4

Hacer que el controlador no escriba directo en modelos. Debe delegar al servicio.

### Paso 5

Conectar después ese flujo con tu lógica IA actual.

## Lo más importante de todo

WhatsApp debe ser tratado como un canal externo, no como un chat distinto del sistema.

El sistema correcto es:

- WhatsApp entra;
- se normaliza;
- se guarda en tu núcleo interno de conversaciones;
- tu IA y tus empleados trabajan sobre ese núcleo;
- las respuestas salen otra vez por WhatsApp.

Ese enfoque te permite luego conectar también Messenger, Telegram o webchat sin rehacer toda la lógica.

## Riesgos principales

### 1. El agente habla demasiado

Solución:

- límite de longitud por respuesta;
- respuestas de 1 a 4 líneas;
- una acción por turno.

### 2. El agente inventa precios o políticas

Solución:

- todo precio desde API;
- todo descuento desde regla calculada;
- bloquear respuestas libres en temas sensibles.

### 3. El agente vende algo sin disponibilidad

Solución:

- consultar disponibilidad antes de prometer cierre.

### 4. El agente acepta un pago falso

Solución:

- verificador dedicado + revisión humana cuando haya duda.

### 5. El agente pierde contexto

Solución:

- memoria resumida + memoria estructurada.

## Diseño de MVP recomendado

Si quieres avanzar con menor riesgo, tu MVP debería ser este:

### MVP comercial

- canal principal: WhatsApp;
- entrada: webhook del canal;
- almacenamiento: `conversaciones` + `mensajes`;
- herramientas: precios, combos, métodos de pago, búsqueda de cliente;
- respuesta IA corta;
- memoria básica por cliente;
- escalación a humano;
- sin descuento automático todavía;
- sin crear venta automáticamente todavía.

Con eso ya puedes atender más rápido y medir:

- tasa de respuesta;
- tasa de cotización;
- tasa de envío de comprobante;
- tasa de conversión a venta.

## Recomendación final

Sí puedes lograrlo, y el proyecto ya tiene buena parte de la base.

La forma correcta de hacerlo no es crear un solo prompt gigante, sino un sistema con:

- canal;
- chat interno;
- herramientas reales;
- memoria estructurada;
- reglas de negocio;
- verificación de pagos;
- cierre de ventas;
- escalación humana.

## Siguiente paso recomendado

Antes de programar más, conviene definir estas 5 decisiones:

1. canal inicial: WhatsApp, Messenger o ambos;
2. política exacta de descuentos;
3. cuándo el bot debe escalar a humano;
4. qué significa para ti "venta cerrada";
5. qué datos mínimos quieres guardar como memoria comercial.

Si esas 5 decisiones quedan claras, ya se puede convertir esta visión en una implementación por fases dentro del proyecto.
