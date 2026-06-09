# Chat Test Plan

## Alcance

Modulo: WhatsApp Helpdesk interno.

Rutas:

- Web: `GET /chat/whatsapp`
- Inbound API: `POST /api/chat/whatsapp/inbound`

## Endpoints

### `GET /chat/whatsapp`

Requiere:

- Sesion Laravel `auth`
- Permiso `chat.ver`

Acciones Livewire:

- `selectConversation`
- `takeConversation`
- `assignTo`
- `closeConversation`
- `reopenConversation`
- `sendText`
- `sendImage`
- `sendAudio`

### `POST /api/chat/whatsapp/inbound`

Header requerido:

```http
X-Chat-Webhook-Token: <CHAT_WEBHOOK_TOKEN>
```

Tambien acepta `token` en payload para pruebas, pero en produccion debe usarse header.

## Payload Inbound Esperado

```json
{
  "canal_user_id": "593999999999@s.whatsapp.net",
  "telefono": "593999999999",
  "nombre": "Cliente Demo",
  "mensaje": "Hola, necesito ayuda",
  "tipo": "texto",
  "external_message_id": "wamid.demo-001",
  "instance": "Streamify Azul",
  "origen": "n8n",
  "payload": {
    "source": "evolution-api"
  }
}
```

Imagen:

```json
{
  "canal_user_id": "593999999999@s.whatsapp.net",
  "telefono": "593999999999",
  "tipo": "imagen",
  "media_url": "https://example.com/image.jpg",
  "mime_type": "image/jpeg",
  "external_message_id": "wamid.demo-img-001"
}
```

Audio:

```json
{
  "canal_user_id": "593999999999@s.whatsapp.net",
  "telefono": "593999999999",
  "tipo": "audio",
  "media_url": "https://example.com/audio.ogg",
  "mime_type": "audio/ogg",
  "external_message_id": "wamid.demo-audio-001"
}
```

## Payload Outbound Esperado

El panel guarda primero el mensaje local y luego envia al webhook configurado en:

- `N8N_CLIENT_MESSAGE_WEBHOOK`

Texto:

```json
{
  "instance_name": "Streamify Azul",
  "instance_apikey": "...",
  "numero": "593999999999@s.whatsapp.net",
  "mensaje": "Respuesta del operador",
  "tipo_contenido": "texto"
}
```

Media:

```json
{
  "instance_name": "Streamify Azul",
  "instance_apikey": "...",
  "numero": "593999999999@s.whatsapp.net",
  "mensaje": "Adjunto",
  "tipo_contenido": "imagen",
  "media_url": "https://app.example.com/storage/chat/images/archivo.jpg",
  "mime_type": "image/jpeg"
}
```

## Checklist QA

- Operador con `chat.ver` abre `/chat/whatsapp`.
- Operador sin `chat.ver` recibe `403`.
- Operador con `chat.responder` envia texto.
- Operador sin `chat.responder` no puede responder.
- Imagen valida se guarda en `storage/app/public/chat/images`.
- Audio valido se guarda en `storage/app/public/chat/audio`.
- Documento, ubicacion y plantilla no aparecen por defecto.
- Conversacion nueva entra desde webhook y aumenta no leidos.
- Mensaje duplicado por `external_message_id` no duplica registros.
- Tomar conversacion asigna `assigned_to`.
- Supervisor reasigna a otro operador.
- Cerrar cambia estado a `cerrado`.
- Reabrir cambia estado a `abierto`.
- Al seleccionar una conversacion se limpian no leidos.
- Dos operadores ven asignacion en polling.

## Casos Borde

- Token webhook incorrecto: respuesta `401`.
- Payload sin numero: respuesta `422`.
- Payload sin texto ni media: respuesta `422`.
- Tipo deshabilitado (`documento`, `ubicacion`, `plantilla`): respuesta `422`.
- Archivo mayor a `chat_max_upload_mb`: validacion Livewire.
- MIME no permitido: validacion Livewire.
- N8N caido: mensaje queda local con `error_message`.
- Conversacion cerrada recibe nuevo inbound: se reabre/crea flujo activo.

## Como Probar con n8n

1. Configurar variable `CHAT_WEBHOOK_TOKEN`.
2. Crear workflow con Webhook inbound de Evolution API.
3. Normalizar payload a los campos de este documento.
4. Enviar `POST /api/chat/whatsapp/inbound` con header `X-Chat-Webhook-Token`.
5. Verificar respuesta `201`.
6. Abrir `/chat/whatsapp` y confirmar que aparece la conversacion.
7. Configurar `N8N_CLIENT_MESSAGE_WEBHOOK` para recibir outbound del panel.
8. Responder desde el panel y validar que n8n recibe payload outbound.

## Como Probar con Evolution

1. Conectar instancia Evolution y confirmar `instance_name`.
2. Guardar canal en `chat_whatsapp_channels`.
3. Enviar mensaje real desde WhatsApp.
4. Confirmar llegada a n8n.
5. Confirmar llamada a `/api/chat/whatsapp/inbound`.
6. Responder desde `/chat/whatsapp`.
7. Confirmar que n8n reenvia a Evolution API.
8. Confirmar recepcion en WhatsApp.

## Errores Comunes

- `401 Token de webhook invalido`: falta `CHAT_WEBHOOK_TOKEN` o header incorrecto.
- `403 No tienes permiso`: faltan permisos Spatie `chat.ver`, `chat.responder` o `chat.supervisor`.
- Mensaje guardado pero no enviado: falta `N8N_CLIENT_MESSAGE_WEBHOOK`, instancia o API key.
- Media no visible: revisar `php artisan storage:link` y permisos de `storage/app/public/chat`.
- Audio rechazado: MIME no incluido en la lista permitida.
