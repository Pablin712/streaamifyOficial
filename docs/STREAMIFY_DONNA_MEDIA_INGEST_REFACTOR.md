# Refactor del flujo n8n + Streamify para guardar audios e imágenes con transcripción

## Contexto

El proyecto Streamify ya tiene una base funcional:

- Base de datos existente.
- Vistas existentes para clientes, chats y flujo operativo.
- Workflow en n8n conectado a Evo API.
- Endpoints actuales como:
  - `POST /api/v2/chat/router/ingest`
  - `POST /api/v2/chat/router/save-respond`
  - `POST /api/v2/payments/n8n/receipt-intake`
- Flujo actual que ya diferencia mensajes de texto, audio e imagen.
- Extracción de media desde Evo API usando `getBase64FromMediaMessage`.
- Transcripción de audio con OpenAI.
- Análisis de imagen con OpenAI.

Por lo tanto, NO se debe reconstruir Donna desde cero. La tarea es pulir y extender el flujo existente para que Streamify almacene correctamente el mensaje textual útil para el agente, junto con los archivos multimedia.

---

## Problema actual detectado

El workflow actual ya tiene ramas para:

- Texto.
- Audio.
- Imagen.
- Comprobantes de pago.
- Mensajes enviados por operadores o empleados.

Sin embargo, hay un problema importante en el manejo de multimedia:

1. Cuando llega un audio, el flujo obtiene el archivo y lo transcribe, pero la transcripción no queda integrada correctamente en el `ingest` final.
2. Cuando llega una imagen, el flujo obtiene la imagen y la analiza, pero el resultado textual tampoco siempre se guarda como parte del mensaje útil para el agente.
3. En algunos caminos, `ingest` se ejecuta antes de tener la transcripción o análisis del archivo.
4. Los nodos `Audio content` e `Image content` generan contenido útil, pero actualmente no están conectados hacia un `ingest` final único.
5. La rama de comprobantes está parcialmente desactivada (`Load` e `ingest msg3` aparecen deshabilitados), por lo que conviene separar claramente:
   - Guardado del chat.
   - Procesamiento de comprobante.
6. Para imágenes con caption/texto, se debe guardar:
   - El archivo de imagen.
   - El texto escrito por el usuario junto a la imagen.
   - La descripción/transcripción/análisis textual de la imagen.

---

## Objetivo del refactor

El endpoint `ingest` debe guardar SIEMPRE un texto útil para el agente, sin importar el tipo de mensaje.

### Texto normal

Guardar:

- `mensaje`: texto recibido.
- `tipo_contenido`: `texto`.
- Sin media.

### Audio

Guardar:

- Archivo de audio.
- Base64 o ruta del archivo, según la implementación actual.
- MIME type.
- Nombre del archivo.
- Transcripción textual del audio.
- Texto final para el agente.

Ejemplo de texto final para el agente:

```text
<audio>
Transcripción: Hola, quisiera saber si tienen disponibilidad para mañana a las 3 de la tarde.
</audio>
```

### Imagen sin texto escrito por el usuario

Guardar:

- Archivo de imagen.
- MIME type.
- Nombre del archivo.
- Descripción o transcripción visual generada por IA.
- Texto final para el agente.

Ejemplo:

```text
<imagen>
Descripción: La imagen muestra una captura de pantalla de un comprobante de transferencia bancaria por $20.
</imagen>
```

### Imagen con texto/caption escrito por el usuario

Guardar:

- Archivo de imagen.
- Texto del mensaje/caption.
- Descripción o transcripción visual de la imagen.
- Texto final combinado para el agente.

Ejemplo:

```text
<imagen>
Texto del usuario: Este es el comprobante de mi pago.
Descripción de la imagen: Se observa un comprobante de transferencia del Banco Pichincha por $20 realizado por Juan Pérez.
</imagen>
```

### Imagen que es comprobante de pago

Guardar en dos niveles:

1. Como chat normal en `chat/router/ingest`, para que el historial del cliente quede completo.
2. Como comprobante en el endpoint de pagos, si el análisis indica que realmente es un comprobante.

El comprobante no debe saltarse el historial de chat. Primero o en paralelo debe quedar guardado como mensaje multimedia recibido.

---

## Regla principal de implementación

El flujo debe cambiar de:

```text
Recibir mensaje -> obtener media -> guardar ingest rápido -> luego transcribir/analizar
```

A:

```text
Recibir mensaje -> obtener media -> transcribir/analizar -> construir payload normalizado -> guardar ingest una sola vez
```

La excepción son mensajes enviados por el operador (`fromMe = true`), que deben ir a `save-respond`, pero también con el mismo criterio: si tienen media, guardar archivo + texto útil.

---

## Nuevo contrato recomendado para `chat/router/ingest`

Mantener compatibilidad con los campos actuales para no romper el proyecto:

```json
{
  "telefono": "593999999999",
  "numero": "593999999999",
  "canal_user_id": "593999999999",
  "canal": "whatsapp",
  "instance": "Streamify Azul",
  "instance_name": "Streamify Azul",
  "instance_apikey": "...",
  "server_url": "https://evoapi.abigailsoft.com",
  "origen": "n8n",
  "tipo_contenido": "texto|audio|imagen|video|documento",
  "external_message_id": "ABC123",
  "external_thread_id": "593999999999@s.whatsapp.net",
  "mensaje": "texto final útil para el agente",
  "mensaje_original": "texto/caption original enviado por el usuario",
  "texto_extraido": "transcripción o descripción generada por IA",
  "media_base64": "base64 opcional",
  "media_file_name": "archivo.ogg|imagen.jpg",
  "media_mime_type": "audio/ogg|image/jpeg",
  "media_kind": "audio|image|video|document",
  "media_transcription": "texto transcrito del audio o descripción de imagen",
  "media_caption": "caption original de WhatsApp si existe",
  "media_analysis_json": {},
  "payload": {},
  "debounce_seconds": 35,
  "from_me": false,
  "tipo_remitente": "cliente|empleado|operador"
}
```

### Campos mínimos que debe aceptar el backend

El backend debe aceptar como mínimo:

- `mensaje`
- `mensaje_original`
- `texto_extraido`
- `tipo_contenido`
- `media_base64`
- `media_file_name`
- `media_mime_type`
- `media_transcription`
- `media_caption`
- `media_analysis_json`
- `payload`

---

## Cambios recomendados en Streamify/Laravel

### 1. Localizar el endpoint actual

Claude Code debe buscar en el proyecto:

```bash
rg "chat/router/ingest"
rg "save-respond"
rg "ChatRouter"
rg "ingest"
```

Probablemente existan rutas en:

```text
routes/api.php
routes/web.php
app/Http/Controllers/...
```

La instrucción es extender el endpoint existente, no crear uno paralelo salvo que sea estrictamente necesario.

---

### 2. Extender validación del request

El método `ingest` debe permitir los nuevos campos opcionales.

Ejemplo aproximado:

```php
$request->validate([
    'telefono' => 'nullable|string|max:30',
    'numero' => 'nullable|string|max:30',
    'canal_user_id' => 'nullable|string|max:80',
    'canal' => 'nullable|string|max:40',
    'tipo_contenido' => 'nullable|string|max:40',
    'mensaje' => 'nullable|string',
    'mensaje_original' => 'nullable|string',
    'texto_extraido' => 'nullable|string',
    'media_base64' => 'nullable|string',
    'media_file_name' => 'nullable|string|max:255',
    'media_mime_type' => 'nullable|string|max:120',
    'media_kind' => 'nullable|string|max:40',
    'media_transcription' => 'nullable|string',
    'media_caption' => 'nullable|string',
    'media_analysis_json' => 'nullable',
    'payload' => 'nullable',
    'external_message_id' => 'nullable|string|max:255',
    'external_thread_id' => 'nullable|string|max:255',
    'from_me' => 'nullable',
    'tipo_remitente' => 'nullable|string|max:40',
]);
```

---

### 3. Crear campos nuevos si no existen

No eliminar columnas existentes. Agregar columnas compatibles.

Posibles columnas para la tabla donde se guardan mensajes de chat:

```php
Schema::table('chat_messages', function (Blueprint $table) {
    $table->longText('mensaje_original')->nullable()->after('mensaje');
    $table->longText('texto_extraido')->nullable()->after('mensaje_original');
    $table->longText('texto_agente')->nullable()->after('texto_extraido');

    $table->string('media_kind')->nullable()->after('tipo_contenido');
    $table->string('media_mime_type')->nullable();
    $table->string('media_file_name')->nullable();
    $table->string('media_path')->nullable();
    $table->longText('media_transcription')->nullable();
    $table->longText('media_caption')->nullable();
    $table->json('media_analysis_json')->nullable();
});
```

Ajustar el nombre real de la tabla según el proyecto. Si la tabla actual no se llama `chat_messages`, Claude Code debe detectar la tabla correcta antes de crear la migración.

---

### 4. Normalizar el texto útil para el agente en backend

El backend debe calcular un campo `texto_agente` o equivalente.

Regla:

```php
$tipo = $request->input('tipo_contenido');
$mensajeOriginal = trim((string) $request->input('mensaje_original', ''));
$textoExtraido = trim((string) $request->input('texto_extraido', ''));
$mensaje = trim((string) $request->input('mensaje', ''));

if ($mensaje !== '') {
    $textoAgente = $mensaje;
} elseif ($tipo === 'audio') {
    $textoAgente = "<audio>\nTranscripción: {$textoExtraido}\n</audio>";
} elseif ($tipo === 'imagen') {
    $textoAgente = "<imagen>\n";
    if ($mensajeOriginal !== '') {
        $textoAgente .= "Texto del usuario: {$mensajeOriginal}\n";
    }
    if ($textoExtraido !== '') {
        $textoAgente .= "Descripción de la imagen: {$textoExtraido}\n";
    }
    $textoAgente .= "</imagen>";
} else {
    $textoAgente = $mensajeOriginal ?: $textoExtraido;
}
```

Luego guardar:

- `mensaje`: mantenerlo como texto final o texto visible principal.
- `mensaje_original`: caption/texto original.
- `texto_extraido`: transcripción o descripción IA.
- `texto_agente`: texto final usado por Donna.

---

### 5. Guardado de archivos multimedia

Si llega `media_base64`, guardar el archivo en storage.

Recomendación:

```text
storage/app/public/chat_media/{canal}/{instance_name}/{YYYY}/{MM}/{external_message_id}.{ext}
```

El endpoint debe:

1. Detectar extensión por `media_mime_type`.
2. Decodificar base64.
3. Guardar archivo.
4. Guardar ruta en `media_path`.
5. No guardar base64 completo en base de datos, salvo que el proyecto ya lo haga por una razón específica.

---

## Cambios recomendados en n8n

### Cambio 1: no ejecutar `ingest` antes de transcribir/analizar

Actualmente la rama de audio/imagen puede pasar por `Get audio` o `Get imagen` y luego enviar a `ingest msg1` mediante `fromMe`, antes de que el audio pase por `Transcribe` o la imagen por `Analyze`.

Se recomienda cambiar a:

```text
Tipo mensaje -> audio
  -> Get audio
  -> Convert audio
  -> Transcribe
  -> Preparar payload multimedia
  -> fromMe media?
      -> false: chat/router/ingest
      -> true: chat/router/save-respond
```

```text
Tipo mensaje -> imagen
  -> Get imagen
  -> Convert image
  -> Analyze
  -> Parsear1
  -> Preparar payload multimedia
  -> Si comprobante: también enviar a receipt-intake
  -> fromMe media?
      -> false: chat/router/ingest
      -> true: chat/router/save-respond
```

---

### Cambio 2: reemplazar varios `ingest msg*` por un solo nodo reusable

Actualmente existen varios nodos:

- `ingest msg`
- `ingest msg1`
- `ingest msg2`
- `ingest msg3`

La recomendación es dejar un patrón claro:

- `ingest_texto`
- `ingest_media`
- `save_respond_texto`
- `save_respond_media`

O, mejor aún, un solo `HTTP Request` para ingest y otro para save-respond, alimentados por un nodo previo que prepare el payload.

---

### Cambio 3: crear nodo `Preparar payload para Streamify`

Agregar un nodo Code después de `Transcribe` y después de `Parsear1` para producir el mismo formato.

#### Audio: Code node sugerido

Nombre recomendado: `Preparar audio para ingest`

```js
const normal = $('Normalizar').first().json;
const media = $('Get audio').first().json;
const transcription = $json.text || $json.transcription || '';
const caption = normal.message.content || '';

const textoAgente = `<audio>\nTranscripción: ${transcription}\n</audio>`;

return [{
  json: {
    telefono: normal.contact.numero,
    numero: normal.contact.numero,
    canal_user_id: normal.contact.numero,
    canal: 'whatsapp',
    tipo_contenido: 'audio',
    external_message_id: normal.message.id,
    external_thread_id: normal.message.chat_id,
    instance: normal.instance.name,
    instance_name: normal.instance.name,
    instance_apikey: normal.instance.apikey,
    server_url: normal.instance.server_url,
    origen: 'n8n',
    debounce_seconds: 35,

    mensaje: textoAgente,
    mensaje_original: caption,
    texto_extraido: transcription,
    media_kind: 'audio',
    media_transcription: transcription,
    media_caption: caption,

    media_base64: media.base64,
    media_file_name: media.fileName,
    media_mime_type: media.mimetype,

    payload: normal,
    from_me: normal.message.fromMe,
    tipo_remitente: normal.message.fromMe ? 'operador' : normal.contact.tipo
  }
}];
```

#### Imagen: Code node sugerido

Nombre recomendado: `Preparar imagen para ingest`

```js
const normal = $('Normalizar').first().json;
const media = $('Get imagen').first().json;
const parsed = $json;

const caption = normal.message.content || '';

let descripcion = '';

if (parsed.es_comprobante) {
  descripcion = [
    'Imagen recibida como posible comprobante de pago.',
    parsed.data?.monto_total_detectado ? `Monto: ${parsed.data.monto_total_detectado}` : null,
    parsed.data?.banco ? `Banco: ${parsed.data.banco}` : null,
    parsed.data?.titular ? `Titular: ${parsed.data.titular}` : null,
    parsed.data?.emisor ? `Emisor: ${parsed.data.emisor}` : null,
    parsed.data?.comprobante ? `Comprobante: ${parsed.data.comprobante}` : null,
  ].filter(Boolean).join('\n');
} else {
  descripcion = parsed.content
    ? String(parsed.content).replace(/<imagen>/g, '').trim()
    : (parsed.descripcion || parsed.raw || 'Imagen recibida sin descripción disponible');
}

const partes = ['<imagen>'];
if (caption) partes.push(`Texto del usuario: ${caption}`);
if (descripcion) partes.push(`Descripción de la imagen: ${descripcion}`);
partes.push('</imagen>');

const textoAgente = partes.join('\n');

return [{
  json: {
    telefono: normal.contact.numero,
    numero: normal.contact.numero,
    canal_user_id: normal.contact.numero,
    canal: 'whatsapp',
    tipo_contenido: 'imagen',
    external_message_id: normal.message.id,
    external_thread_id: normal.message.chat_id,
    instance: normal.instance.name,
    instance_name: normal.instance.name,
    instance_apikey: normal.instance.apikey,
    server_url: normal.instance.server_url,
    origen: 'n8n',
    debounce_seconds: 35,

    mensaje: textoAgente,
    mensaje_original: caption,
    texto_extraido: descripcion,
    media_kind: 'image',
    media_transcription: descripcion,
    media_caption: caption,
    media_analysis_json: parsed,

    media_base64: media.base64,
    media_file_name: media.fileName,
    media_mime_type: media.mimetype,

    es_comprobante: parsed.es_comprobante === true,
    comprobante_data: parsed.es_comprobante ? parsed.data : null,

    payload: normal,
    from_me: normal.message.fromMe,
    tipo_remitente: normal.message.fromMe ? 'operador' : normal.contact.tipo
  }
}];
```

---

### Cambio 4: conectar `Audio content` e `Image content` hacia ingest

Si se mantienen los nodos `Audio content` e `Image content`, deben alimentar al HTTP Request final de `ingest`.

Pero lo más ordenado es reemplazarlos por los nodos:

- `Preparar audio para ingest`
- `Preparar imagen para ingest`

De esa manera ya no se depende de expresiones dispersas en los HTTP Request.

---

### Cambio 5: el comprobante también debe quedar como chat

Para imágenes que son comprobantes:

```text
Parsear1 -> Preparar imagen para ingest -> chat/router/ingest
                         └── si es_comprobante=true -> payments/n8n/receipt-intake
```

Esto evita que el comprobante quede registrado solamente como pago, pero no como parte de la conversación.

---

## Payload para `receipt-intake`

Cuando `es_comprobante = true`, enviar además:

```json
{
  "cliente_nombre": "emisor detectado",
  "cliente_telefono": "+593999999999",
  "banco_nombre": "banco detectado",
  "valor": 20,
  "numcomprobante": "123456789",
  "media_base64": "...",
  "media_mime_type": "image/jpeg",
  "media_file_name": "comprobante.jpg",
  "canal": "whatsapp",
  "external_message_id": "ABC123",
  "analysis_json": {}
}
```

El endpoint de pagos puede seguir siendo independiente, pero el chat siempre debe guardarse en `chat/router/ingest`.

---

## Criterios de aceptación

Claude Code debe considerar terminado este refactor cuando se cumpla:

### Texto

- Un mensaje de texto se guarda igual que antes.
- `mensaje` contiene el texto recibido.
- El agente sigue respondiendo normalmente.

### Audio

- El audio se descarga desde Evo API.
- El archivo se guarda en Streamify.
- La transcripción queda guardada en la base de datos.
- El campo usado por Donna contiene la transcripción.
- En el historial se puede saber que fue un audio.

### Imagen sin caption

- La imagen se guarda como archivo.
- El análisis/descripción se guarda como texto.
- Donna puede usar ese texto para responder.

### Imagen con caption

- Se guarda el caption original.
- Se guarda la descripción/análisis de la imagen.
- El texto final para Donna combina ambos.

### Comprobante

- El comprobante queda guardado en el historial de chats.
- Si es válido, también se envía al endpoint de pagos.
- Si no se detecta número de comprobante, no se inventa.

### Operador / fromMe

- Los mensajes enviados desde el WhatsApp conectado se guardan como respuesta/salida.
- Si el operador envía una imagen o audio, también se guarda con archivo y texto útil si aplica.

---

## Orden recomendado de trabajo para Claude Code

1. Revisar rutas y controlador del endpoint `chat/router/ingest`.
2. Identificar tabla/modelo donde se guardan mensajes.
3. Crear migración segura para columnas multimedia/texto si faltan.
4. Actualizar validación del endpoint.
5. Actualizar lógica de guardado de media base64.
6. Actualizar lógica de `texto_agente` o campo equivalente.
7. Verificar que el historial y el agente lean el texto normalizado.
8. Actualizar el workflow n8n:
   - No llamar ingest antes de transcribir/analizar.
   - Conectar audio transcrito hacia ingest.
   - Conectar imagen analizada hacia ingest.
   - Guardar comprobantes también como chat.
9. Probar con 4 casos reales:
   - Texto.
   - Audio.
   - Imagen con caption.
   - Comprobante de pago.

---

## Instrucción directa para Claude Code Agent

```text
Quiero extender el sistema actual de Streamify, no reconstruirlo.

Busca el endpoint existente /api/v2/chat/router/ingest y modifica su implementación para que pueda guardar mensajes multimedia con texto normalizado para el agente.

El nuevo comportamiento requerido es:

1. Para texto: guardar el mensaje como ya se hace actualmente.
2. Para audio: guardar el archivo recibido desde n8n y guardar también la transcripción textual del audio.
3. Para imagen: guardar el archivo recibido desde n8n, guardar el caption/texto original si existe y guardar también la descripción/análisis textual de la imagen.
4. Para imagen con caption: combinar caption + análisis visual en un campo de texto útil para el agente.
5. Para comprobantes: guardar la imagen en el historial de chats y, si es comprobante válido, también enviarla/procesarla con el módulo de pagos existente.
6. No eliminar compatibilidad con los campos actuales del endpoint.
7. No crear un sistema paralelo si ya existe una tabla/controlador/modelo para chats; extiende lo existente.
8. Si faltan columnas, crea una migración segura y nullable.
9. No guardar base64 permanentemente en la base de datos si se puede guardar como archivo en storage y registrar la ruta.
10. Asegúrate de que Donna use el texto normalizado/transcrito para responder, no solo el mensaje original vacío.

También revisa el workflow n8n actual y propón los cambios de conexión:
- Audio debe seguir: Get audio -> Convert audio -> Transcribe -> Preparar audio para ingest -> ingest/save-respond.
- Imagen debe seguir: Get imagen -> Convert image -> Analyze -> Parsear1 -> Preparar imagen para ingest -> ingest/save-respond.
- No ejecutar ingest antes de tener la transcripción/análisis.
```

---

## Nota para el SaaS Donna multi-cliente

Este refactor prepara la base para el SaaS Donna dentro de Streamify.

Más adelante, cuando se agreguen múltiples clientes SaaS, el campo clave para identificar a qué cliente pertenece cada mensaje será:

```text
instance_name
```

Cada instancia de Evo API deberá estar asociada a un cliente de Streamify y a una configuración específica de Donna. Así, el mismo flujo n8n podrá ser estático y Streamify resolverá:

- Cliente propietario de la instancia.
- Estado del servicio.
- Prompt del cliente.
- Contexto del negocio.
- Historial del chat.
- Credenciales necesarias.

