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

## Subagentes reales para este proyecto

Aquí sí conviene usar subagentes, pero no como sistemas totalmente separados ni con bases aisladas.

La forma correcta es:

- un router central decide;
- un subagente queda activo por conversación;
- todos leen la misma base de memoria y las mismas APIs;
- cada subagente cambia tono, objetivo y tools permitidas.

### Subagentes iniciales recomendados

Estos son los que dejé preparados como base de datos inicial:

- `router_general`: clasifica el caso y deriva;
- `espera_humano`: silencia al bot y deja la conversación para una persona;
- `asistente_no_registrado`: atiende leads nuevos y preguntas frecuentes;
- `vendedor_cierre`: cotiza, orienta y lleva a pago;
- `soporte_cliente`: atiende clientes con problemas o dudas de servicio;
- `cobranzas_pago`: gestiona métodos de pago y comprobantes;
- `postventa_reciente`: acompaña a quien acaba de comprar.

### Regla práctica

No necesitas un modelo distinto para cada subagente.

Lo que necesitas es que cada subagente tenga:

- `prompt_base`;
- criterios de entrada;
- lista de tools permitidas;
- prioridad;
- estado activo.

Eso ya quedó modelado en la tabla `chat_subagentes`.

## Tablas que ya quedaron preparadas

La base que conviene para arrancar ya quedó definida así:

### `chat_contactos_canal`

Para mapear el contacto externo con tu sistema.

Guarda:

- canal;
- identificador externo;
- teléfono normalizado;
- nombre del canal;
- posible `idcli`;
- estado de relación (`lead`, `vinculado`, `cliente`, `bloqueado`).

### `chat_mensajes_canal`

Para guardar el mapping entre mensaje interno y mensaje externo.

Guarda:

- `external_message_id`;
- dirección de mensaje (`inbound`, `outbound`, `status`);
- estado entregado/leído/fallido;
- media y payload.

### `chat_subagentes`

Para registrar los subagentes activos y su comportamiento.

Guarda:

- código;
- tipo;
- descripción;
- prompt base;
- criterios;
- tools;
- prioridad.

### `chat_memoria_negocio`

Esta es tu memoria general del negocio.

Aquí debe ir todo lo que el agente puede usar como conocimiento general y reusable.

Tipos ya contemplados:

- `faq`
- `servicio`
- `metodo_pago`
- `politica_venta`
- `politica_descuento`
- `confianza`
- `marca`
- `objecion`
- `guion`

### Qué meter aquí

La memoria general debe incluir principalmente información visible o utilizable del lado del cliente, por ejemplo:

- qué servicios vendes;
- cómo funcionan los planes;
- métodos de pago;
- tiempos de entrega;
- políticas de soporte;
- cómo responder objeciones comunes;
- frases cortas de confianza y autoridad;
- diferencias entre combos y planes individuales;
- qué pedir para validar una compra;
- reglas de escalación.

Aquí también cabe un poco de labia comercial, pero controlada.

No para manipular, sino para sostener un tono:

- seguro;
- profesional;
- directo;
- breve;
- confiable.

### `chat_memoria_contactos`

Esta es la memoria estructurada por persona.

Sirve para guardar hechos puntuales como:

- presupuesto aproximado;
- servicio de interés;
- objeción recurrente;
- método de pago preferido;
- tipo de cliente;
- si ya pidió descuento;
- si ya envió comprobante.

### `chat_memoria_resumenes`

Esta es la memoria temporal o resumida.

Sirve para guardar:

- resumen de conversación;
- resumen cliente;
- handoff a humano;
- follow-up.

Aquí es donde encaja tu idea de conservar contexto corto, por ejemplo últimas 24-48 horas o un resumen operativo que expira.

## Cómo se usarían las dos memorias

Tu idea de doble memoria es correcta. La forma limpia de usarla sería así:

### Memoria 1. General del negocio

Fuente principal:

- `chat_memoria_negocio`
- APIs del sistema

Uso:

- responder preguntas repetidas;
- mantener tono y confianza;
- no inventar datos del negocio;
- unificar el discurso comercial.

### Memoria 2. Chat y cliente

Fuente principal:

- `chat_memoria_contactos`
- `chat_memoria_resumenes`
- historial reciente de `mensajes`
- datos vivos de Laravel por API.

Uso:

- recordar qué pidió ese cliente;
- retomar conversaciones;
- detectar si es soporte, venta o postventa;
- evitar repetir preguntas.

## Lo que haría el router

El router no responde mucho. Decide.

## Cómo clasificar los chats de los clientes

La clasificación no debería depender de una sola condición. Lo correcto es construir un bloque de variables derivadas por cada mensaje entrante y con eso decidir el subagente.

La idea es esta:

1. entra mensaje nuevo;
2. se lee el contacto, conversación, cliente y memoria;
3. se calculan variables de clasificación;
4. se aplica prioridad de reglas;
5. se asigna `subagente_codigo`.

## Variables base que debes calcular

Estas variables son las más útiles para tu router.

### 1. Variables del canal

- `canal`: `whatsapp`, `messenger`, `telegram`, `webchat`
- `canal_user_id`: identificador externo del cliente
- `tipo_contenido`: `texto`, `imagen`, `audio`, `archivo`, `sistema`
- `es_media`: `true/false`
- `origen`: `whatsapp_directo`, `whatsapp_ads`, `messenger`, `webchat`

### 2. Variables del contacto

- `contacto_canal_id`
- `estado_relacion`: `lead`, `vinculado`, `cliente`, `bloqueado`
- `idcli`: nullable
- `cliente_existe`: `true/false`
- `telefono_normalizado`

### 3. Variables de conversación

- `idconv`
- `estado_conversacion`: `abierta`, `en_atencion`, `en_espera`, `cerrada`, `bot_activo`
- `subagente_actual`
- `requiere_humano`
- `ultima_actividad`
- `ultimo_mensaje_empleado_at`
- `mensajes_no_leidos`

### 4. Variables del mensaje actual

- `mensaje_texto`
- `mensaje_normalizado`
- `longitud_mensaje`
- `intencion_principal`
- `intenciones_detectadas`
- `sentimiento`: opcional
- `urgencia`: `alta`, `media`, `baja`

### 5. Variables del cliente

- `ya_compro`
- `tiene_compra_reciente`
- `horas_desde_ultima_compra`
- `tiene_ventas`
- `tiene_soporte_abierto`
- `servicio_activo`
- `mora_o_pago_pendiente`: si luego lo conectas a ventas o cobranzas

### 6. Variables de memoria del contacto

- `servicio_interes`
- `presupuesto_detectado`
- `objecion_detectada`
- `pidio_descuento`
- `metodo_pago_preferido`
- `ya_envio_comprobante`
- `pidio_humano`
- `mensajes_cliente_post_handoff`

## Variables derivadas de clasificación

Con las variables base, el router debería producir flags simples. Estos flags son los que realmente redirigen.

### Flags recomendados

- `flag_solicitud_humano`
- `flag_espera_humano_activa`
- `flag_reactivar_ia_por_insistencia`
- `flag_nuevo_lead`
- `flag_cliente_registrado`
- `flag_compra_reciente`
- `flag_necesita_soporte`
- `flag_quiere_comprar`
- `flag_quiere_pagar`
- `flag_envio_comprobante`
- `flag_solo_consulta_general`
- `flag_bloqueado`

## Cómo obtener cada flag

### `flag_solicitud_humano`

`true` si detectas frases como:

- humano
- asesor
- persona
- operador
- agente real
- no quiero bot

También puedes apoyarte en memoria previa si ya había pedido humano hace poco.

### `flag_espera_humano_activa`

`true` si:

- `requiere_humano = true`
- o `subagente_actual = espera_humano`

### `flag_reactivar_ia_por_insistencia`

`true` si:

- `flag_espera_humano_activa = true`
- el cliente envió 3 o más mensajes adicionales
- dentro de la ventana definida, por ejemplo 10 minutos
- y no hubo mensaje humano en ese intervalo

### `flag_nuevo_lead`

`true` si:

- no hay `idcli`
- o `estado_relacion = lead`
- y no existe historial de compra real

### `flag_cliente_registrado`

`true` si:

- hay `idcli`
- o `estado_relacion = cliente`

### `flag_compra_reciente`

`true` si:

- `horas_desde_ultima_compra <= 48`

Puedes ajustar esa ventana luego.

### `flag_necesita_soporte`

`true` si detectas frases como:

- no entra
- no sirve
- contraseña
- pantalla
- error
- se cayó
- no funciona

Y además hay señales de cliente real o compra previa.

### `flag_quiere_comprar`

`true` si el mensaje contiene intención comercial como:

- precio
- plan
- combo
- comprar
- promoción
- cuánto cuesta
- descuento

### `flag_quiere_pagar`

`true` si detectas:

- pagar
- transferencia
- cuenta bancaria
- banco
- depósito
- paypal
- binance

### `flag_envio_comprobante`

`true` si:

- `tipo_contenido = imagen` o `archivo`
- y el texto o contexto sugiere pago

### `flag_solo_consulta_general`

`true` si:

- no es soporte
- no es pago
- no es compra inmediata
- y solo pregunta cosas generales del negocio

### `flag_bloqueado`

`true` si:

- `estado_relacion = bloqueado`
- o la conversación quedó administrativamente bloqueada

## Prioridad de clasificación

No todas las reglas pesan igual. Debes aplicar prioridad, no coincidencia simple.

Orden recomendado:

1. `flag_bloqueado`
2. `flag_solicitud_humano`
3. `flag_espera_humano_activa` sin reactivación
4. `flag_reactivar_ia_por_insistencia`
5. `flag_envio_comprobante`
6. `flag_quiere_pagar`
7. `flag_compra_reciente`
8. `flag_necesita_soporte`
9. `flag_quiere_comprar`
10. `flag_nuevo_lead`
11. `flag_cliente_registrado`
12. `flag_solo_consulta_general`

## Mapeo de variables a subagente

Usa una tabla lógica como esta:

| Condición dominante | Subagente |
| --- | --- |
| `flag_solicitud_humano = true` | `espera_humano` |
| `flag_espera_humano_activa = true` y `flag_reactivar_ia_por_insistencia = false` | `espera_humano` |
| `flag_envio_comprobante = true` | `cobranzas_pago` |
| `flag_quiere_pagar = true` | `cobranzas_pago` |
| `flag_compra_reciente = true` | `postventa_reciente` |
| `flag_necesita_soporte = true` | `soporte_cliente` |
| `flag_quiere_comprar = true` | `vendedor_cierre` |
| `flag_nuevo_lead = true` | `asistente_no_registrado` |
| `flag_cliente_registrado = true` sin otra intención fuerte | `soporte_cliente` o `postventa_reciente` según contexto |
| `flag_solo_consulta_general = true` | `asistente_no_registrado` |

## Payload recomendado del router

Para que luego lo uses en n8n o Laravel, conviene que el router devuelva algo así:

```json
{
	"idconv": 123,
	"contacto_canal_id": 55,
	"idcli": 900,
	"subagente_codigo": "vendedor_cierre",
	"requiere_humano": false,
	"debe_responder_ia": true,
	"motivo_router": "intencion_compra_detectada",
	"flags": {
		"flag_solicitud_humano": false,
		"flag_espera_humano_activa": false,
		"flag_reactivar_ia_por_insistencia": false,
		"flag_nuevo_lead": false,
		"flag_cliente_registrado": true,
		"flag_compra_reciente": false,
		"flag_necesita_soporte": false,
		"flag_quiere_comprar": true,
		"flag_quiere_pagar": false,
		"flag_envio_comprobante": false,
		"flag_solo_consulta_general": false,
		"flag_bloqueado": false
	},
	"variables": {
		"estado_relacion": "cliente",
		"tipo_contenido": "texto",
		"intencion_principal": "precio",
		"horas_desde_ultima_compra": 120,
		"mensajes_cliente_post_handoff": 0
	}
}
```

## Variables mínimas que te conviene persistir

Si no quieres recalcular todo desde cero en cada mensaje, estas son las más valiosas para guardar:

- `subagente_codigo`
- `requiere_humano`
- `estado_relacion`
- `intencion_principal`
- `servicio_interes`
- `pidio_humano`
- `mensajes_cliente_post_handoff`
- `horas_desde_ultima_compra`
- `ultimo_mensaje_empleado_at`

## Recomendación práctica

No intentes hacer la clasificación con un único prompt libre.

Hazla por capas:

1. reglas duras por palabras clave y estado;
2. apoyo de memoria estructurada;
3. si hace falta, clasificador IA solo para casos ambiguos.

Así el router será predecible y no cambiará de subagente por respuestas inestables del modelo.

## Adaptación a tu diagrama real

Tu diagrama sí se adapta bastante bien a la base que ya quedó creada, pero con una precisión importante:

- las migraciones y seeders actuales cubren bien el nivel de `router -> subagente principal`;
- todavía no implementan por sí solas toda la lógica fina de cambios de estado entre compra, entrega, soporte y retorno;
- esa parte debe vivir en un `ChatRouterService` con reglas y variables derivadas.

## Qué sí coincide ya con tu visión

### 1. Memoria global + historial de chat

Esto sí encaja directamente con lo modelado:

- `chat_memoria_negocio` = memoria global del negocio;
- `chat_memoria_contactos` = memoria estructurada por contacto;
- `chat_memoria_resumenes` = memoria temporal y resumen operativo;
- `mensajes` + `chat_mensajes_canal` = historial real del chat.

### 2. Clasificación inicial por tipo de persona

Tu idea de distinguir:

- persona nueva;
- cliente sin usuarios activos;
- cliente activo;
- cliente con soporte;
- persona que quiere humano;

sí se adapta al diseño actual.

Lo que faltaba era expresarlo como variables, y eso ya quedó documentado.

### 3. Handoff a humano

Tu caso de:

- no responder si pide humano;
- esperar persona real;
- volver a IA si insiste demasiado;

también encaja con el subagente `espera_humano` que ya quedó definido.

## Qué no está completo todavía

Aquí está la diferencia entre tu diagrama y lo que hoy quedó implementado.

### 1. No existe aún el motor que ejecute las transiciones

Hoy existe la estructura para guardar:

- contacto;
- conversación;
- subagente actual;
- memoria;
- criterios de subagente.

Pero todavía no existe el servicio que haga automáticamente cosas como:

- pasar de `asistente` a `vendedor`;
- pasar de `vendedor` a `soporte` después de entregar cuenta;
- pasar de `vendedor` a `asistente` si no paga o se arrepiente;
- pasar de `soporte` a `vendedor` si en realidad quiere renovar.

Eso debe hacerlo `ChatRouterService`.

### 2. No están persistidas todas las variables que tu diagrama usa implícitamente

Tu flujo usa variables de negocio que todavía no están formalizadas como cálculo del router, por ejemplo:

- `cliente_tiene_usuarios_activos`
- `cliente_tiene_cuentas_activas`
- `transferencia_aprobada`
- `venta_anotada`
- `cuenta_entregada`
- `cliente_quiere_renovar`
- `cliente_se_arrepintio`
- `cliente_no_pago`

La base actual sí permite guardar estas señales, pero todavía no hay código que las calcule de forma central.

### 3. Tus “subagentes para delegar” aún no existen como segundo nivel

En tu diagrama aparece algo más avanzado:

- subagente principal;
- APIs permitidas;
- subagentes internos de delegación;
- posible paso a trabajador humano o router.

Hoy la base solo modela el primer nivel de subagente principal. El segundo nivel todavía habría que implementarlo como:

- tools con acciones internas;
- o `accion_sugerida` dentro del resultado del router;
- o cola de trabajo para empleados.

## Cómo usar la base actual para comportarse como tu diagrama

La mejor forma de usar lo que ya tienes es pensar en dos capas.

### Capa 1. Subagente principal

Solo decide el modo de atención dominante:

- `asistente_no_registrado`
- `vendedor_cierre`
- `soporte_cliente`
- `cobranzas_pago`
- `postventa_reciente`
- `espera_humano`

### Capa 2. Acción o transición

Además del subagente, el router debería devolver una acción sugerida.

Ejemplos:

- `accion = cotizar`
- `accion = pedir_comprobante`
- `accion = verificar_pago`
- `accion = registrar_soporte`
- `accion = entregar_cuenta`
- `accion = derivar_humano`
- `accion = reactivar_ia`
- `accion = renovar_venta`

Con eso consigues el comportamiento fino de tu diagrama sin crear 20 subagentes distintos.

## Variables adicionales que te conviene crear para seguir tu flujo

Si quieres que tu diagrama funcione tal como lo dibujaste, te recomiendo añadir al cálculo del router estas variables derivadas:

- `cliente_tiene_usuarios_activos`
- `cliente_tiene_soporte_pendiente`
- `cliente_tiene_pago_pendiente`
- `cliente_tiene_compra_reciente`
- `cliente_tiene_entrega_reciente`
- `cliente_quiere_renovar`
- `cliente_quiere_otra_compra`
- `cliente_pidio_confirmacion_pago`
- `cliente_pago_aprobado`
- `cliente_pago_rechazado`
- `cliente_recibio_cuenta`

## Qué piezas reales del proyecto ya te sirven para eso

Ya hay varias piezas del proyecto que sí ayudan a acercarte a tu diagrama:

- `ViewUsuarioActivo` para detectar usuarios activos del cliente;
- `AIAssistantController` para cliente, ventas, servicios y precios;
- `InformationController` para precios y bancos;
- `PaymentVerificationController` para flujo de comprobantes y aprobación/rechazo;
- `TecnicoCuentasController` para cuentas, mantenimientos y estado de cuentas;
- `Soporte` y `soportes` para registrar incidencias.

## Cómo se traducen tus casos 1 al 5

### Caso 1

Tu flujo:

- mensaje como nuevo -> asistente
- quiere comprar -> vendedor
- pagó aprobado -> vendedor
- recibe cuenta -> vendedor
- después de recibir cuenta -> soporte

Esto sí se adapta muy bien.

Implementación recomendada:

- subagente inicial: `asistente_no_registrado`
- si detecta intención de compra: `vendedor_cierre`
- si llega pago aprobado: `cobranzas_pago` y luego `vendedor_cierre`
- cuando venta y entrega quedan registradas: `postventa_reciente`
- si luego reporta problema: `soporte_cliente`

Observación:

Aquí yo no lo dejaría en `vendedor` después de entrega; lo más limpio es pasarlo a `postventa_reciente`, y de ahí a `soporte_cliente` si aparece incidencia.

### Caso 2

Tu flujo:

- mensaje como cliente sin usuarios activos -> asistente
- quiere comprar -> vendedor
- pagó aprobado -> vendedor
- recibe cuenta -> vendedor
- después de recibir cuenta -> soporte

Esto también encaja.

La clave es usar una variable como:

- `cliente_tiene_usuarios_activos = false`

Si existe cliente pero no tiene usuarios activos, puede comportarse casi como lead comercial.

### Caso 3

Tu flujo:

- mensaje como cliente con usuarios activos -> soporte
- quiere comprar o renovar -> vendedor
- pagó aprobado -> vendedor
- recibe cuenta -> vendedor
- después de recibir cuenta -> soporte

Esto encaja, pero requiere una prioridad contextual.

Regla correcta:

- por defecto cliente activo -> `soporte_cliente`
- si detecta intención fuerte de nueva compra o renovación -> `vendedor_cierre`

O sea: el estado base es soporte, pero la intención actual puede moverlo temporalmente a ventas.

### Caso 4

Tu flujo:

- mensaje como caso 1, 2 o 3 -> asistente
- quiere comprar -> vendedor
- no pagó o se rechazó pago -> vendedor
- pide de nuevo pago o confirmación -> vendedor
- no paga -> asistente

Esto se adapta, pero aquí conviene ajustar una cosa.

Si no paga o el pago es rechazado, yo no volvería automáticamente a `asistente`. Haría esto:

- `cobranzas_pago` si aún está intentando pagar;
- `vendedor_cierre` si sigue negociando o pidiendo confirmación;
- `asistente_no_registrado` solo si realmente se enfría la compra o deja de ser un caso comercial activo.

Es decir: en tu diagrama ese regreso a asistente es válido, pero debería ocurrir por enfriamiento, no solo por rechazo puntual.

### Caso 5

Tu flujo:

- mensaje como cliente activo -> soporte
- quiere soporte con cuenta -> soporte
- recibe solución o mensaje de seguimiento -> soporte
- no quiere IA, quiere humano -> router

Esto encaja muy bien con lo actual.

Regla recomendada:

- cliente activo + problema = `soporte_cliente`
- si pide humano = `espera_humano`
- si humano no responde y cliente insiste = volver a `soporte_cliente`

## Veredicto sobre tu diseño

Tu diseño sí es compatible con las últimas migraciones y con la lógica que se ha documentado.

Pero hay que entenderlo así:

- la base actual sí soporta tu arquitectura;
- todavía no ejecuta sola los cambios de caso;
- para que funcione como en tu dibujo falta implementar el router de transición.

## Cómo te recomiendo usarlo ya

Si lo quieres usar sin rehacer la base, te recomiendo esta estrategia:

1. Mantén solo 6 subagentes principales.
2. Haz que el router calcule variables y flags.
3. Además del subagente, devuelve `accion_sugerida`.
4. Usa `conversaciones.subagente_codigo` para el modo actual.
5. Usa `conversaciones.metadata` para estado transitorio del caso.
6. Usa `chat_memoria_contactos` para hechos del cliente.
7. Usa `chat_memoria_resumenes` para contexto temporal y handoff.

Así tu diagrama no solo encaja: se vuelve implementable sin inflar demasiado la arquitectura.

Reglas base recomendadas:

1. Si pide explícitamente hablar con una persona: `espera_humano`.
2. Si no hay cliente vinculado y pregunta general: `asistente_no_registrado`.
3. Si pregunta precio, combo, plan o descuento: `vendedor_cierre`.
4. Si menciona pago o manda comprobante: `cobranzas_pago`.
5. Si ya es cliente y reporta problema: `soporte_cliente`.
6. Si compró recientemente y vuelve a escribir: `postventa_reciente`.

## Caso especial: humano sin respuesta del bot

Este caso sí conviene modelarlo como estado real del router.

### Cuándo entra en `espera_humano`

Si el cliente dice cosas como:

- "quiero hablar con una persona";
- "pasame con un asesor";
- "quiero que me atienda alguien real";
- "no quiero bot".

Entonces el router debe hacer esto:

1. marcar `requiere_humano = true`;
2. poner `subagente_codigo = espera_humano`;
3. dejar la conversación en estado `en_espera` o `abierta` según tu flujo interno;
4. no responder absolutamente nada desde IA.

### Comportamiento deseado

Mientras la conversación esté en `espera_humano`:

- la IA no responde;
- solo se guarda el mensaje;
- se espera respuesta de empleado;
- el router reevalúa cada nuevo mensaje entrante.

### Cuándo volver de humano a IA

Aquí sí conviene una excepción para no dejar al cliente abandonado.

Si el cliente sigue escribiendo y no hubo intervención humana, el router puede devolver la conversación a IA.

Regla recomendada inicial:

- si el cliente manda 3 mensajes o más;
- dentro de 10 minutos;
- y no existe mensaje de empleado en ese intervalo;

entonces:

1. quitar `requiere_humano`;
2. sacar `subagente_codigo` de `espera_humano`;
3. volver a clasificar la conversación;
4. permitir que IA responda.

### Respuesta sugerida cuando vuelve a IA

No conviene responder como si nada. Conviene una frase corta como:

- "Mientras te toma una persona, te adelanto esto:";
- "Te sigo ayudando por aquí mientras te revisan.";
- "Voy avanzando contigo mientras entra un asesor.".

### Metadata sugerida para este caso

Este caso conviene guardarlo en `conversaciones.metadata`, por ejemplo:

```json
{
	"handoff_humano": true,
	"handoff_requested_at": "2026-04-08T18:00:00Z",
	"handoff_reason": "cliente_pidio_persona",
	"mensajes_cliente_post_handoff": 2,
	"ultimo_mensaje_empleado_at": null,
	"reactivar_ia_si_supera_mensajes": 3,
	"reactivar_ia_ventana_minutos": 10
}
```

### Regla importante

Si un humano ya respondió, la IA no debe reactivarse sola por insistencia del cliente.

La reactivación automática solo tiene sentido si:

- el cliente pidió humano;
- nadie humano contestó;
- el cliente sigue insistiendo;
- conviene evitar silencio total.

## Implementación base ya hecha

Ya quedó creada la base para empezar a desarrollar esto:

- migraciones de contactos de canal;
- migraciones de mensajes externos;
- migraciones de subagentes;
- migraciones de memoria general;
- migraciones de memoria por contacto;
- migraciones de resúmenes temporales;
- ajuste de `conversaciones` para aceptar leads de WhatsApp sin `idcli` obligatorio;
- modelos Eloquent para todas esas tablas;
- seeder inicial de subagentes.

## Qué sigue inmediatamente

Con esta base, el siguiente paso técnico correcto ya no es seguir pensando tablas. Es construir dos piezas:

1. `WhatsAppWebhookController` + `WhatsAppChannelService`
2. `ChatRouterService` para elegir subagente y contexto

Después de eso ya puedes conectar n8n o incluso mover parte de la orquestación a Laravel.

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
