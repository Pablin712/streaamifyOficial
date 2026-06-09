# Envio Directo de Mensajes de Entrega por WhatsApp

## Objetivo

Eliminar la dependencia de n8n para los modals de mensajes de entrega y renovacion usados por el equipo operativo.

Desde este cambio, los mensajes ya no salen por webhook intermedio. Se envian directo a Evolution API usando los canales configurados en `chat_whatsapp_channels`.

## Alcance

Aplica a los modals que reutilizan la ruta web `usuarios.enviarMensajeCliente`:

- vista de crear venta
- vista de renovacion/usuarios
- modal de resultados de movimientos de usuarios

## Comportamiento nuevo

Cada modal mantiene el boton `Copiar Mensaje` y ahora expone dos botones de envio:

- `WhatsApp verde`
- `WhatsApp alterno`

Ambos llaman la misma ruta backend y solo cambian la preferencia de canal enviada en el payload:

- `channel_preference = verde`
- `channel_preference = alterno`

## Resolucion de canal

La seleccion del canal se hace en `UsuarioController` con estas reglas:

- `verde`: primer canal activo con salida habilitada y `color = verde`
- `alterno`: primer canal activo con salida habilitada y `color != verde`

Para el alterno se prioriza `azul` cuando existe; si no, toma el siguiente disponible.

## Flujo backend

Ruta involucrada:

- `POST /admin/usuarios/enviar-mensaje-cliente`

Pasos del flujo:

1. Valida telefono, mensaje y `channel_preference`.
2. Resuelve el canal WhatsApp segun color.
3. Envia el texto directo por `WhatsAppOutboundService` hacia Evolution API.
4. Registra el outbound del operador en chat interno:
   - crea o reutiliza `chat_contactos_canal`
   - crea o reutiliza `conversaciones`
   - crea `mensajes`
   - crea `chat_mensaje_canal`
5. Devuelve respuesta JSON al modal con el resultado del envio.

## Persistencia del outbound

Los mensajes enviados desde estos modals quedan guardados como `outbound` para mantener trazabilidad en el sistema.

Metadatos registrados:

- origen `delivery-modal`
- canal usado
- color del canal
- instancia de Evolution
- estado del dispatch
- `external_message_id` cuando Evolution lo devuelve

## Estados de respuesta

- `200/201`: enviado correctamente por el canal seleccionado
- `207`: el mensaje se guardo en chat, pero WhatsApp no confirmo el envio
- `422`: falta telefono valido o no existe canal configurado para esa preferencia
- `500`: error interno durante envio o persistencia

## Archivos ajustados

- `app/Http/Controllers/UsuarioController.php`
- `resources/views/sales/ventas/create.blade.php`
- `resources/views/inventory/usuarios/index.blade.php`
- `resources/views/inventory/cuentas/modals/movement-results.blade.php`

## Nota operativa

Este cambio solo reemplaza los modals de entrega/renovacion que ya usaban `usuarios.enviarMensajeCliente`.

No modifica otros flujos que todavia dependan de webhooks n8n, como mensajes masivos, mensajes a proveedores u otros procesos externos.
