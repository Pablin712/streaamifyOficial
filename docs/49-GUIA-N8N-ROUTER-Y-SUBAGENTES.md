# Guía n8n: router, subagentes y memoria

Esta guía documenta la parte más delicada del flujo: cómo pasar de un solo agente genérico a una arquitectura con router + subagentes especializados, manteniendo el agrupado de mensajes y usando Streamify como memoria real.

Esta guía está pensada para el flujo n8n que ya tienes con nodos como:

- `Normalizar`
- `Tipo mensaje`
- `Get audio` / `Transcribe`
- `Get imagen` / `Analyze`
- `merge A`
- `ingest msg`
- `Wait5`
- `get context`
- `If4`
- `AI Agent2`
- `Enviar mensaje1`
- `save respond`

---

## 1. Idea central

No conviene que un único agente haga todo esto al mismo tiempo:

- clasificar intención;
- decidir si vender, cobrar, dar soporte o callar;
- responder con tono correcto;
- decidir herramientas;
- recordar contexto.

La arquitectura correcta es:

1. `router`
   Decide quién debe atender ese turno.
2. `switch`
   Envía el flujo al subagente correcto.
3. `subagente`
   Responde con prompt y tools especializadas.

En otras palabras:

- el router clasifica;
- el switch enruta;
- el subagente ejecuta.

---

## 2. Subagentes reales disponibles en Streamify

Hoy los subagentes sembrados son estos:

### `router_general`

- tipo: `router`
- función: clasifica y deriva al subagente correcto

### `espera_humano`

- tipo: `router`
- función: dejar el bot en silencio y pasar a humano

### `asistente_no_registrado`

- tipo: `asistente`
- función: atender leads nuevos o clientes no vinculados

### `vendedor_cierre`

- tipo: `vendedor`
- función: precios, combos, cierre, CTA corto

### `soporte_cliente`

- tipo: `soporte`
- función: fallas, accesos, contraseña, pantalla, cuenta dañada

### `cobranzas_pago`

- tipo: `cobranzas`
- función: métodos de pago, comprobantes, validación, confirmación

### `postventa_reciente`

- tipo: `postventa`
- función: seguimiento después de compra reciente

---

## 3. Qué hace el router

El router no es el agente que manda el mensaje final en la mayoría de casos.

Su función es responder esta pregunta:

> ¿Qué especialista debe atender este turno del cliente?

Ejemplos:

- "precio de netflix" -> `vendedor_cierre`
- "ya pagué, aquí está el comprobante" -> `cobranzas_pago`
- "no me entra la cuenta" -> `soporte_cliente`
- "quiero hablar con un asesor" -> `espera_humano`
- "hola, qué planes tienen" y no está registrado -> `asistente_no_registrado`
- "compré ayer y tengo una duda" -> `postventa_reciente`

---

## 4. Qué hace el switch

El `Switch` en n8n es la pieza correcta para ti.

Tu idea de usar `Switch` conviene más que dejar que un solo nodo IA haga todo, porque:

- te da control claro del flujo;
- permite prompts separados por subagente;
- permite tools distintas por rama;
- hace más fácil depurar errores;
- evita que ventas use herramientas de soporte o viceversa.

Entonces, sí: conviene usar un `Switch` después del router.

---

## 5. Flujo recomendado completo

El flujo recomendado en tu caso queda así:

1. webhook WhatsApp
2. `Normalizar`
3. `Tipo mensaje`
4. audio o imagen se convierten en `content`
5. `merge A`
6. `ingest msg`
7. `Wait 35s`
8. `get context`
9. `If debe_responder`
10. `Definir subagente`
11. `Switch subagente`
12. `Agente especialista`
13. `Enviar mensaje1`
14. `save respond`

Si el subagente es `espera_humano`:

15. `handoff`
16. no responder o responder solo si tu política lo permite

---

## 6. Dónde poner el router

El router debe ejecutarse después de `get context`, no antes.

Razón:

Antes de `get context`, todavía no tienes:

- `mensaje_agrupado`
- historial reciente
- memorias del contacto
- contexto real de conversación
- verificación de si realmente te toca responder

Después de `get context`, sí tienes todo eso.

Entonces la secuencia correcta es:

1. guardar mensaje
2. esperar
3. obtener contexto agrupado
4. si `debe_responder = true`, recién ahí decides el subagente

---

## 7. Reglas duras antes del router IA

No todo debe decidirlo el modelo.

Conviene meter reglas duras antes del router IA.

### Reglas recomendadas

#### 1. Empleados no entran al flujo comercial

Si:

- `contact.esEmpleado = true`

Entonces sales del flujo de clientes.

#### 2. Comprobante detectado en imagen

Si tu análisis devuelve:

- `es_comprobante = true`

Entonces fuerza:

- `subagente_codigo = cobranzas_pago`

#### 3. Solicitud humana explícita

Si el mensaje contiene intención clara de humano:

- humano
- asesor
- persona
- operador

Entonces fuerza:

- `subagente_codigo = espera_humano`

#### 4. Soporte

Si detectas:

- no entra
- no funciona
- error
- clave
- contraseña
- pantalla

Entonces fuerza:

- `subagente_codigo = soporte_cliente`

#### 5. Venta o precios

Si detectas:

- precio
- plan
- combo
- netflix
- comprar
- descuento

Entonces fuerza:

- `subagente_codigo = vendedor_cierre`

#### 6. Si ninguna regla dura aplica

Entonces recién llamas al router IA o dejas un default:

- `asistente_no_registrado`

---

## 8. Arquitectura recomendada del router

La forma práctica de implementarlo es así:

### Opción recomendada: reglas duras + router IA de respaldo

1. `Code` o `IF` con reglas duras
2. si no hay match, llamar a un nodo IA de router
3. ese nodo devuelve solo clasificación, no respuesta larga

### Entonces, ¿qué es el router en la práctica?

No es un `Switch` simple por sí solo.

Tampoco conviene que sea un agente grande que haga todo.

En la práctica, el router es una capa de decisión.

Esa capa la puedes implementar de tres formas:

#### Forma 1. Solo reglas + `Switch`

Funciona así:

1. nodo `Code` o varios `IF`
2. asignas `subagente_codigo`
3. `Switch` por `subagente_codigo`

Sirve si tus casos son muy obvios y repetitivos.

Ventajas:

- más simple;
- más barato;
- más estable;
- más fácil de depurar.

Desventaja:

- se queda corto cuando el mensaje es ambiguo.

#### Forma 2. Solo agente router

Funciona así:

1. `get context`
2. nodo IA router
3. `Switch` por `subagente_codigo`

Ventaja:

- entiende mejor mensajes ambiguos o mezclados.

Desventajas:

- más riesgo de clasificar mal;
- menos control;
- más costo;
- más difícil de depurar.

#### Forma 3. Híbrido: reglas duras + agente router de respaldo

Esta es la mejor para tu caso.

Funciona así:

1. `get context`
2. `Code` con reglas duras
3. si no hubo match, llamar al router IA
4. salida final: `subagente_codigo`
5. `Switch`

Ventajas:

- controlas los casos claros;
- la IA solo decide lo ambiguo;
- reduces errores;
- mantienes flexibilidad.

### Recomendación concreta para ti

Hoy no te conviene un `Switch` solo y tampoco un router 100% IA.

Te conviene esto:

- reglas duras para:
  - comprobante
  - humano
  - soporte muy claro
  - venta muy clara
- router IA solo para lo ambiguo
- `Switch` al final para ejecutar el subagente

Traducción práctica:

- el router sí existe como concepto;
- pero en n8n no tiene que ser un solo nodo;
- el router puede ser un bloque compuesto por reglas + IA;
- el `Switch` no reemplaza al router, solo ejecuta la decisión del router.

### Cómo construir el router en n8n

Implementación mínima recomendada:

1. `get context`
2. `If debe_responder`
3. nodo `Code` llamado por ejemplo `pre-router`
4. `IF subagente_forzado existe`
5. si existe, pasar directo al `Switch`
6. si no existe, llamar a `AI Router`
7. normalizar salida JSON del router
8. `Switch` por `subagente_codigo`

### Qué hace el nodo `pre-router`

El `pre-router` no responde al cliente.

Solo mira señales obvias y devuelve algo como esto:

```json
{
  "subagente_forzado": "cobranzas_pago",
  "motivo": "imagen detectada como comprobante"
}
```

O si no encuentra nada claro:

```json
{
  "subagente_forzado": null,
  "motivo": null
}
```

### Qué hace el nodo `AI Router`

Solo corre cuando `subagente_forzado` es `null`.

Su trabajo es devolver:

```json
{
  "subagente_codigo": "asistente_no_registrado",
  "motivo": "lead general sin intención fuerte todavía",
  "requiere_humano": false,
  "silencio_bot": false,
  "confianza": 78
}
```

### Qué hace el `Switch`

El `Switch` no decide.

Solo enruta según el valor final ya decidido:

- `espera_humano`
- `asistente_no_registrado`
- `vendedor_cierre`
- `soporte_cliente`
- `cobranzas_pago`
- `postventa_reciente`

En resumen:

- router = lógica de decisión
- switch = enrutador técnico en n8n
- subagente = ejecutor especializado

### Entonces, ¿el router es un agente?

Sí.

Pero es un agente de clasificación, no un agente de respuesta final.

Eso significa:

- sí puedes implementarlo como nodo IA;
- pero su responsabilidad termina en decidir el subagente;
- no debe comportarse como vendedor, soporte o cobranzas;
- no debe redactar la respuesta final del cliente.

La forma correcta de modelarlo es como un `classifier agent`.

Su salida debe ser:

- corta;
- estructurada;
- determinista;
- lista para alimentar un `Switch`.

### APIs permitidas para el router

El router debe tener muy pocas herramientas.

#### Permitidas

##### `GET|POST /api/v2/chat/router/context`

Es la API base del router.

De aquí salen:

- `mensaje_agrupado`
- `historial_reciente`
- `memorias_contacto`
- `resumenes`
- `memoria_negocio`
- `contacto`
- `conversacion`
- `subagentes`

Uso:

- clasificar el turno actual con contexto real.

##### `POST /api/v2/chat/router/handoff`

Permitida cuando el router decide `espera_humano`.

Uso:

- marcar la conversación en espera humana;
- apagar respuesta normal del bot.

##### `POST /api/v2/chat/router/memory/summary`

Permitida, pero opcional.

Uso recomendado:

- guardar un resumen corto cuando hubo cambio fuerte de contexto;
- registrar el motivo de un handoff;
- dejar trazabilidad de una escalación importante.

No hace falta llamarla en cada turno.

##### `POST /api/v2/chat/router/memory/contact`

Permitida, pero opcional.

Uso recomendado:

- guardar una preferencia estable;
- guardar una objeción repetida;
- guardar una señal persistente como `cliente_molesto` o `prefiere_whatsapp`.

No la uses para guardar frases temporales o ruido conversacional.

#### No recomendadas para el router

No conviene que el router llame APIs de negocio profundas como si ya fuera un subagente.

Ejemplos:

- consultar precios detallados;
- calcular descuentos;
- validar comprobantes;
- diagnosticar soporte;
- crear ventas.

Eso debe ocurrir después del `Switch`, dentro del subagente correcto.

### Memoria que sí puede usar el router

El router sí debe leer memoria, pero la memoria debe venir de Streamify, no de memoria efímera del nodo IA.

Debe leer desde `context`:

- `memorias_contacto`
- `resumenes`
- `memoria_negocio`

No debe depender como fuente principal de:

- `Simple Memory`
- `Buffer Memory`
- memoria efímera del nodo agent entre ejecuciones

### Input recomendado del router

El router no debería consumir el payload bruto del webhook.

Debe recibir un objeto ya limpio, construido desde `chat/router/context`.

Ejemplo:

```json
{
  "idconv": 184,
  "trigger_idmsg": 991,
  "mensaje_agrupado": "hola\nquiero netflix\ncuanto cuesta",
  "contacto": {
    "idcli": null,
    "canal": "whatsapp",
    "canal_user_id": "593961778319@s.whatsapp.net",
    "estado_relacion": "lead"
  },
  "conversacion": {
    "idconv": 184,
    "estado": "abierta",
    "requiere_humano": false,
    "subagente_codigo": "router_general"
  },
  "historial_reciente": [],
  "memorias_contacto": [],
  "resumenes": [],
  "memoria_negocio": []
}
```

### Output obligatorio del router

El router debe devolver JSON estricto.

No debe devolver:

- texto libre;
- explicación larga;
- Markdown;
- varias opciones.

Formato recomendado:

```json
{
  "subagente_codigo": "vendedor_cierre",
  "motivo": "cliente pide precio de servicio y muestra intención de compra",
  "requiere_humano": false,
  "silencio_bot": false,
  "confianza": 92
}
```

Campos:

- `subagente_codigo`: obligatorio. Solo uno de estos valores:
  - `espera_humano`
  - `asistente_no_registrado`
  - `vendedor_cierre`
  - `soporte_cliente`
  - `cobranzas_pago`
  - `postventa_reciente`
- `motivo`: obligatorio. Una frase corta.
- `requiere_humano`: obligatorio. `true` o `false`.
- `silencio_bot`: obligatorio. `true` o `false`.
- `confianza`: obligatorio. Entero de `0` a `100`.

Regla operativa:

Si el output trae cualquiera de estas condiciones:

- `subagente_codigo = espera_humano`
- `requiere_humano = true`
- `silencio_bot = true`

Entonces el flujo debe ir a `handoff` y no a un subagente de respuesta normal.

### Prompt recomendado del router

Usa este prompt como base para el nodo IA del router:

```text
Eres el router conversacional de Streamify.

Tu única función es clasificar el turno actual del cliente y decidir qué subagente debe atenderlo.

No eres el vendedor, no eres soporte, no eres cobranzas y no eres postventa.
No debes redactar una respuesta final para el cliente.
No debes devolver texto libre.
Debes devolver solo JSON válido.

Subagentes permitidos:
- espera_humano
- asistente_no_registrado
- vendedor_cierre
- soporte_cliente
- cobranzas_pago
- postventa_reciente

Reglas obligatorias:
1. Si el cliente pide una persona, asesor, operador o atención humana, elige espera_humano.
2. Si hay indicios claros de comprobante, pago realizado, validación bancaria o envío de soporte de pago, elige cobranzas_pago.
3. Si hay indicios claros de falla, acceso, contraseña, pantalla, error o cuenta dañada, elige soporte_cliente.
4. Si hay intención comercial clara como precio, plan, combo, servicio, compra o descuento, elige vendedor_cierre.
5. Si hay compra reciente y el caso no es claramente pago ni soporte, elige postventa_reciente.
6. Si no hay match fuerte, elige asistente_no_registrado.
7. Si detectas conflicto, enojo fuerte o solicitud explícita de humano, marca requiere_humano en true.
8. Si eliges espera_humano, también debes marcar silencio_bot en true.

Usa estas entradas:
- mensaje_agrupado: {{ $json.data.mensaje_agrupado }}
- historial_reciente: {{ JSON.stringify($json.data.historial_reciente) }}
- memorias_contacto: {{ JSON.stringify($json.data.memorias_contacto) }}
- resumenes: {{ JSON.stringify($json.data.resumenes) }}
- memoria_negocio: {{ JSON.stringify($json.data.memoria_negocio) }}
- contacto: {{ JSON.stringify($json.data.contacto) }}
- conversacion: {{ JSON.stringify($json.data.conversacion) }}

Devuelve solo JSON con esta estructura exacta:
{
  "subagente_codigo": "espera_humano|asistente_no_registrado|vendedor_cierre|soporte_cliente|cobranzas_pago|postventa_reciente",
  "motivo": "frase corta",
  "requiere_humano": true,
  "silencio_bot": true,
  "confianza": 0
}

No agregues comentarios.
No agregues bloques markdown.
No expliques tu razonamiento.
```

### Ejemplos de salida del router

Caso venta:

```json
{
  "subagente_codigo": "vendedor_cierre",
  "motivo": "cliente pide precio y quiere comprar",
  "requiere_humano": false,
  "silencio_bot": false,
  "confianza": 94
}
```

Caso soporte:

```json
{
  "subagente_codigo": "soporte_cliente",
  "motivo": "cliente reporta que la cuenta no entra",
  "requiere_humano": false,
  "silencio_bot": false,
  "confianza": 91
}
```

Caso pago:

```json
{
  "subagente_codigo": "cobranzas_pago",
  "motivo": "cliente indica que ya pagó y envió comprobante",
  "requiere_humano": false,
  "silencio_bot": false,
  "confianza": 96
}
```

Caso humano:

```json
{
  "subagente_codigo": "espera_humano",
  "motivo": "cliente pidió atención humana directa",
  "requiere_humano": true,
  "silencio_bot": true,
  "confianza": 98
}
```

### Formato ideal de salida del router

```json
{
  "subagente_codigo": "vendedor_cierre",
  "motivo": "cliente pide precio y muestra intención comercial",
  "requiere_humano": false,
  "silencio_bot": false
}
```

Si la salida es:

```json
{
  "subagente_codigo": "espera_humano",
  "motivo": "cliente pidió asesor humano",
  "requiere_humano": true,
  "silencio_bot": true
}
```

Entonces llamas a `handoff` y no haces respuesta IA normal.

---

## 9. Switch recomendado en n8n

Haz un `Switch` por `subagente_codigo`.

Ramas recomendadas:

- `espera_humano`
- `asistente_no_registrado`
- `vendedor_cierre`
- `soporte_cliente`
- `cobranzas_pago`
- `postventa_reciente`
- fallback: `asistente_no_registrado`

### Expresión recomendada

```text
{{ $json.subagente_codigo }}
```

---

## 10. Tools recomendadas por subagente

Aquí es donde tu idea del `Switch` realmente gana valor.

Cada subagente debe tener tools distintas.

### `asistente_no_registrado`

#### Prompt

```text
Eres el asistente inicial de Streamify para leads y clientes no vinculados.

Objetivo:
- orientar rápido;
- responder corto;
- detectar intención comercial;
- mover al siguiente paso sin abrumar.

Reglas:
1. Responde en tono profesional, breve y directo.
2. No inventes precios, combos ni promociones.
3. Usa solo la información de las APIs permitidas.
4. Si el cliente pide precio o comprar, guía hacia cierre.
5. Si detectas soporte, pago o humano, no improvises; indica que corresponde otra atención.
6. Haz máximo una pregunta o una llamada a la acción por mensaje.
```

#### APIs permitidas

##### `GET /api/v2/precios`

- auth: pública
- uso: precios en formato mensaje listo para WhatsApp
- ejemplos:
  - `GET /api/v2/precios?tipo=general`
  - `GET /api/v2/precios?tipo=productos`
  - `GET /api/v2/precios?tipo=combos`

##### `GET /api/v2/metodos-pago`

- auth: pública
- uso: listar métodos de pago cuando el lead pregunta cómo pagar

##### `GET /api/v1/public/ai/servicios`

- auth: pública
- uso: catálogo estructurado de servicios

##### `GET /api/v1/public/ai/precios`

- auth: pública
- uso: precios estructurados por servicio
- ejemplo:
  - `GET /api/v1/public/ai/precios?servicio=NETFLIX`

##### `POST /api/v2/chat/router/memory/contact`

- auth: pública
- uso opcional: guardar una preferencia estable del lead
- ejemplo de uso:
  - guardar `tipo=preferencia`, `clave=servicio_interes`, `valor_texto=NETFLIX`

#### Cómo usarlas

1. Si preguntan en general, usa `GET /api/v2/precios?tipo=general`.
2. Si preguntan por servicio específico, usa `GET /api/v1/public/ai/precios?servicio=...`.
3. Si preguntan cómo pagar, usa `GET /api/v2/metodos-pago`.
4. Si detectas una preferencia persistente, guarda memoria de contacto.

### `vendedor_cierre`

#### Prompt

```text
Eres el vendedor de cierre de Streamify.

Objetivo:
- cerrar venta;
- recomendar la opción correcta;
- llevar al pago con una sola llamada a la acción.

Reglas:
1. Sé breve y concreto.
2. No inventes descuentos ni promociones.
3. No ofrezcas varias rutas a la vez; deja un CTA claro.
4. Si el cliente ya pagó o manda comprobante, eso ya no es venta: corresponde cobranzas.
5. Si el cliente reporta falla o acceso, eso ya no es venta: corresponde soporte.
6. Usa precios reales de API antes de responder.
```

#### APIs permitidas

##### `GET /api/v1/public/ai/precios`

- auth: pública
- uso: precios estructurados y filtrables por servicio
- ejemplo:
  - `GET /api/v1/public/ai/precios?servicio=NETFLIX`

##### `GET /api/v2/precios`

- auth: pública
- uso: mensaje comercial general, productos o combos
- ejemplos:
  - `GET /api/v2/precios?tipo=general`
  - `GET /api/v2/precios?tipo=combos`

##### `GET /api/v2/metodos-pago`

- auth: pública
- uso: cerrar con instrucción de pago real

##### `POST /api/v2/chat/router/memory/contact`

- auth: pública
- uso opcional: guardar objeciones o preferencia comercial
- ejemplos:
  - `tipo=objecion`, `clave=precio_alto`, `valor_texto=cliente considera caro`
  - `tipo=preferencia`, `clave=servicio_interes`, `valor_texto=DISNEY`

#### APIs no verificadas para exponer todavía

El tool conceptual `calcular_descuento_permitido` existe en diseño, pero en las rutas revisadas no hay un endpoint público específico para eso.

Entonces:

- no lo pongas todavía como tool real en n8n;
- si no hay precio o descuento en API, no lo inventes.

#### Cómo usarlas

1. Consulta precio real antes de ofertar.
2. Si conviene combo, usa `GET /api/v2/precios?tipo=combos`.
3. Si el cliente acepta, responde con un solo CTA y luego usa `GET /api/v2/metodos-pago`.
4. Si detectas objeción repetida, guarda memoria de contacto.

### `soporte_cliente`

#### Prompt

```text
Eres soporte al cliente de Streamify.

Objetivo:
- diagnosticar primero;
- responder con calma;
- dar una acción concreta.

Reglas:
1. No cierres venta si el cliente está reportando una falla.
2. Pide solo el dato mínimo faltante para diagnosticar.
3. Usa contexto, historial y datos del cliente antes de concluir.
4. Si el caso necesita humano, indícalo sin improvisar soluciones dudosas.
5. Si el problema es de código o acceso de cuenta, verifica antes de responder.
```

#### APIs permitidas

##### `GET|POST /api/v2/chat/router/context`

- auth: pública
- uso: fuente base de historial, memorias y conversación actual

##### `GET /api/v1/ai/buscar-cliente?q=...`

- auth: requiere `X-API-Key`
- uso: encontrar cliente cuando no tienes `idcli`
- ejemplo:
  - `GET /api/v1/ai/buscar-cliente?q=593998887777`

##### `GET /api/v1/ai/cliente/{id}/ventas`

- auth: requiere `X-API-Key`
- uso: revisar historial comercial del cliente

##### `GET /api/v1/ai/perfiles-disponibles?servicio=...&limit=10`

- auth: requiere `X-API-Key`
- uso: revisar disponibilidad de perfiles cuando el soporte depende del servicio

##### `GET|POST /api/v2/verificar-cliente-cuenta`

- auth: pública
- uso: verificar si el número y la cuenta son elegibles para pedir código o validar acceso
- campos útiles:
  - `numero` o `telefono`
  - `usuariocue` o `usuario_cue`
  - alternativamente `mensaje` si el usuario escribió el dato ahí

##### `POST /api/v2/registrar-codigo-entregado`

- auth: pública
- uso: registrar entrega o espera de código cuando tu flujo de soporte realmente envía un código
- campos útiles:
  - `numero` o `telefono`
  - `usuariocue`
  - `codigo`
  - `estado=enviado` o `estado=esperando`

##### `POST /api/v2/chat/router/memory/summary`

- auth: pública
- uso opcional: guardar resumen de incidencia relevante

#### Cómo usarlas

1. Arranca siempre con `context`.
2. Si falta identificar cliente, usa `buscar-cliente`.
3. Si necesitas saber compras o renovaciones previas, usa `cliente/{id}/ventas`.
4. Si el problema es acceso o código, usa `verificar-cliente-cuenta` antes de responder.
5. Solo usa `registrar-codigo-entregado` si de verdad hubo flujo de código.
6. Si el caso queda abierto o delicado, guarda un resumen corto.

### `cobranzas_pago`

#### Prompt

```text
Eres cobranzas y pagos de Streamify.

Objetivo:
- guiar al pago;
- pedir comprobante si falta;
- validar o registrar el pago sin confundir al cliente.

Reglas:
1. Si el cliente solo pregunta cómo pagar, responde con métodos de pago.
2. Si el cliente manda comprobante, usa el flujo de receipt-checkout.
3. No prometas aprobación manual inmediata si la API dice que está pendiente.
4. Si el pago fue rechazado, explica el estado sin inventar motivos no devueltos por la API.
5. Si ya se concretó el pago, responde como confirmación, no como venta.
```

#### APIs permitidas

##### `GET /api/v2/metodos-pago`

- auth: pública
- uso: listado general de métodos de pago

##### `GET /api/v2/banco/{nombrebanco}`

- auth: pública
- uso: detalle puntual de un banco o método específico

##### `POST /api/v2/payments/n8n/receipt-checkout`

- auth: pública
- uso: registrar comprobante, verificarlo y disparar compra o pedido
- content-type: `multipart/form-data`
- se usa cuando ya tienes:
  - cliente o teléfono
  - producto
  - banco
  - valor
  - imagen del comprobante

##### `GET /api/v2/payments/n8n/recargas/{idrec}`

- auth: pública
- uso opcional: consultar el estado de una recarga creada

##### `POST /api/v2/chat/router/memory/summary`

- auth: pública
- uso opcional: guardar resumen de pago exitoso, pendiente o rechazado

#### Cómo usarlas

1. Si solo pregunta cómo pagar, usa `GET /api/v2/metodos-pago`.
2. Si pide datos de un banco concreto, usa `GET /api/v2/banco/{nombrebanco}`.
3. Si manda comprobante, ejecuta `POST /api/v2/payments/n8n/receipt-checkout`.
4. Si la respuesta es `verification_pending`, informa que está en revisión.
5. Si la respuesta es `purchase_success`, confirma al cliente.
6. Si necesitas trazabilidad, guarda un resumen.

### `postventa_reciente`

#### Prompt

```text
Eres postventa reciente de Streamify.

Objetivo:
- reforzar confianza;
- confirmar entrega;
- resolver dudas posteriores a la compra.

Reglas:
1. Parte del supuesto de que ya hubo una compra reciente.
2. Confirma primero el contexto antes de responder algo sensible.
3. Si detectas una falla técnica, no la trates como postventa liviana; corresponde soporte.
4. Si detectas reclamo fuerte o necesidad humana, prepara handoff.
5. Mantén tono tranquilo y de seguimiento.
```

#### APIs permitidas

##### `GET|POST /api/v2/chat/router/context`

- auth: pública
- uso: revisar conversación y memoria reciente

##### `GET /api/v1/ai/buscar-cliente?q=...`

- auth: requiere `X-API-Key`
- uso: resolver cliente si el `idcli` no está claro

##### `GET /api/v1/ai/cliente/{id}/ventas`

- auth: requiere `X-API-Key`
- uso: validar compra reciente y qué se entregó

##### `POST /api/v2/chat/router/memory/summary`

- auth: pública
- uso opcional: resumir seguimiento postventa relevante

##### `POST /api/v2/chat/router/memory/contact`

- auth: pública
- uso opcional: guardar preferencia o incidencia recurrente

#### Cómo usarlas

1. Usa `context` para entender qué pasó en la conversación.
2. Si necesitas validar compra reciente, consulta `cliente/{id}/ventas`.
3. Si el caso muta a soporte, corta postventa y rerutea.
4. Si deja información útil para después, guarda resumen o memoria.

### `espera_humano`

#### Prompt

```text
Eres el estado de espera humana de Streamify.

Objetivo:
- no responder como bot;
- dejar la conversación lista para atención humana.

Reglas:
1. No redactes una respuesta comercial o técnica.
2. Si el flujo requiere mensaje, usa una frase fija y corta.
3. Tu acción principal es derivar, no conversar.
```

#### APIs permitidas

##### `POST /api/v2/chat/router/handoff`

- auth: pública
- uso: marcar `requiere_humano=true` y estado `en_espera`

##### `POST /api/v2/chat/router/memory/summary`

- auth: pública
- uso opcional: guardar motivo de la escalación

#### Cómo usarlas

1. Llama `handoff` una sola vez por escalación.
2. Si quieres trazabilidad, guarda un resumen del motivo.
3. No pases luego a un nodo IA de respuesta normal.

### Nota sobre autenticación de APIs

En la revisión actual de rutas:

- `chat/router/*`, `v2/precios`, `v2/metodos-pago`, `v2/banco/*`, `v2/verificar-cliente-cuenta`, `v2/registrar-codigo-entregado` y `payments/n8n/*` están públicas.
- `v1/ai/buscar-cliente`, `v1/ai/cliente/{id}/ventas` y `v1/ai/perfiles-disponibles` sí requieren `X-API-Key` porque están dentro del middleware `api.key`.
- `v1/public/ai/servicios` y `v1/public/ai/precios` están públicas.

Si quieres mantener todo el flujo del cliente sin API key, evita exponer al bot las rutas de `v1/ai/*` y apóyate más en `context`, memorias y endpoints públicos.

---

## 11. ¿Conviene usar Simple Memory en n8n?

En tu caso, la respuesta práctica es:

### No conviene usarla como memoria principal

Déjala vacía o desactivada en los subagentes de WhatsApp.

### Razón técnica

Tu flujo funciona por webhooks independientes:

- entra un mensaje;
- se guarda;
- espera;
- consulta contexto;
- responde.

Cada ejecución de n8n es separada.

Si usas `Simple Memory` o `Buffer Window Memory` del nodo IA como memoria principal, te metes en estos problemas:

- memoria desincronizada con la base real;
- mensajes repetidos entre ejecuciones;
- pérdida de contexto si cambia la sesión;
- duplicación entre memoria del nodo y memoria real del chat;
- más dificultad para depurar.

### Memoria correcta en tu caso

La memoria real debe venir de Streamify:

- `historial_reciente`
- `mensajes_pendientes`
- `mensaje_agrupado`
- `memorias_contacto`
- `resumenes`
- `memoria_negocio`

Eso ya te lo da `chat/router/context`.

Entonces:

- `Simple Memory` del nodo IA: mejor vacía
- memoria real: la que viene de las APIs de Streamify

---

## 12. Cuándo sí usar memoria simple

Solo tendría sentido si el subagente hiciera varios pasos internos dentro de la misma ejecución, por ejemplo:

1. analizar intención
2. llamar herramienta
3. reformular
4. validar respuesta

Y todo eso pasa dentro de un mismo run.

Aun así, sería memoria temporal del run, no memoria de conversación persistente.

Para WhatsApp conversacional real, no debe ser tu fuente principal.

---

## 13. Recomendación final sobre memoria

### Recomendación concreta para tu implementación

- En los nodos de agente: no usar `Simple Memory` como memoria principal.
- En el prompt: meter explícitamente el contexto devuelto por `chat/router/context`.
- Si luego quieres persistir memoria extraída por IA: usar estas APIs de Streamify:
  - `POST /api/v2/chat/router/memory/summary`
  - `POST /api/v2/chat/router/memory/contact`

### Traducción práctica

Sí:

- usar memoria general y de chat de Streamify

No:

- depender de `Simple Memory` del nodo agent para recordar WhatsApp entre mensajes

---

## 14. Implementación mínima recomendada en tu flujo actual

La forma mínima y estable de dejarlo es esta:

1. `Normalizar`
2. `Tipo mensaje`
3. convertir audio o imagen a `content`
4. `merge A`
5. `ingest msg`
6. `Wait 35s`
7. `get context`
8. `If debe_responder`
9. `Definir subagente`
10. `Switch subagente`
11. agente especialista
12. `Enviar mensaje1`
13. `save respond` con `subagente_codigo`

Si subagente = `espera_humano`:

14. `handoff`
15. no seguir a respuesta IA normal

---

## 15. Clasificación inicial recomendada

Esta tabla te sirve como punto de partida.

### `cobranzas_pago`

Activar si:

- análisis de imagen detecta comprobante
- texto contiene `pagar`, `comprobante`, `transferencia`, `banco`, `ya pagué`

### `soporte_cliente`

Activar si:

- texto contiene `no entra`, `no funciona`, `error`, `clave`, `contraseña`, `pantalla`

### `vendedor_cierre`

Activar si:

- texto contiene `precio`, `plan`, `combo`, `netflix`, `comprar`, `descuento`

### `espera_humano`

Activar si:

- texto contiene `humano`, `asesor`, `persona`, `operador`

### `postventa_reciente`

Activar si:

- el contexto indica compra reciente
- y el mensaje no es pago ni soporte puro

### `asistente_no_registrado`

Fallback si:

- no hay match fuerte
- o es un lead general

---

## 16. Prompt del router

Si haces router IA, el prompt debe pedir clasificación, no respuesta larga.

Ejemplo:

```text
Eres el router conversacional de Streamify.

Debes elegir solo un subagente para atender este turno.

Subagentes válidos:
- espera_humano
- asistente_no_registrado
- vendedor_cierre
- soporte_cliente
- cobranzas_pago
- postventa_reciente

Contexto actual:
- mensaje agrupado: {{ $json.data.mensaje_agrupado }}
- historial reciente: {{ JSON.stringify($json.data.historial_reciente) }}
- contacto: {{ JSON.stringify($json.data.contacto) }}
- conversación: {{ JSON.stringify($json.data.conversacion) }}

Devuelve solo JSON con:
{
  "subagente_codigo": "...",
  "motivo": "...",
  "requiere_humano": true/false,
  "silencio_bot": true/false
}
```

---

## 17. Prompt de cada subagente

Cada rama del switch debe usar un prompt distinto.

### `vendedor_cierre`

```text
Eres el vendedor de cierre de Streamify.

Objetivo:
- cerrar venta
- ser breve
- una sola llamada a la acción

Usa:
- precios
- combos
- métodos de pago

No inventes precios ni descuentos.
```

### `soporte_cliente`

```text
Eres soporte al cliente de Streamify.

Objetivo:
- diagnosticar primero
- responder con calma
- dar una acción concreta

No mezcles soporte con cierre comercial salvo que el contexto lo pida claramente.
```

### `cobranzas_pago`

```text
Eres cobranzas y pagos de Streamify.

Objetivo:
- guiar al pago
- pedir comprobante si falta
- confirmar revisión o validación

Si ya hay comprobante, actúa como flujo de confirmación de pago, no como vendedor general.
```

### `asistente_no_registrado`

```text
Eres el asistente comercial inicial de Streamify.

Objetivo:
- orientar al lead
- responder corto
- detectar si debe pasar a ventas, soporte o pago
```

### `postventa_reciente`

```text
Eres postventa reciente de Streamify.

Objetivo:
- reforzar confianza
- confirmar entrega
- detectar si el caso en realidad pasó a soporte
```

---

## 18. Qué guardar en `respond`

Cuando el subagente responda, conviene registrar también el subagente que atendió.

Ejemplo:

```json
{
  "idconv": 184,
  "contenido": "Netflix pantalla cuesta 4.50 al mes.",
  "subagente_codigo": "vendedor_cierre",
  "instance": "bot-pagos",
  "external_thread_id": "593961778319@s.whatsapp.net",
  "marcar_leidos": true,
  "metadata": {
    "provider": "evolution",
    "flujo": "chat-clientes"
  }
}
```

Eso deja trazabilidad de qué especialista atendió ese turno.

---

## 19. Decisión recomendada para tu caso

Si hoy quieres avanzar rápido y estable:

### Sí conviene

- `Switch` por subagente
- prompts separados por especialidad
- tools separadas por rama
- memoria real desde Streamify

### No conviene por ahora

- un solo agente enorme para todo
- depender de simple memory del nodo IA
- dejar que la IA decida todo sin reglas duras

---

## 20. Implementación práctica inmediata

Tu siguiente bloque real a construir es:

1. nodo `Definir subagente`
2. `Switch subagente`
3. una rama por:
   - `vendedor_cierre`
   - `soporte_cliente`
   - `cobranzas_pago`
   - `asistente_no_registrado`
   - `postventa_reciente`
   - `espera_humano`
4. `save respond` mandando `subagente_codigo`

Con eso ya dejas de tener un bot plano y pasas a una arquitectura real de router + especialistas.
