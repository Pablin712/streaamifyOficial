# Apis que usaré en n8n

Quiero mejorar las apis actuales, y agregar nuevas.

## Lista de apis
1. Crear mantenimientos de cuenta (más en especial para netflix)
2. Obtener datos de empleado activo (para mensajearlo)
3. Crear una venta en un espacio disponible de un servicio (que no sea spotify)
4. Apis esenciales:
    4. 1. obtener información de cuentas dañadas (se usará un mensaje cron para mensajear a empleados sobre las cuentas que hay que arreglar)
    4. 2. obtener información de clientes en cuentas dañadas

## Implementación de apis en el sistema
### Ténico
1. cuando se hace un cambio a un usuario (botón amarillo, negro, rojo para cambiar servicio) además de ofrecer el mensaje para copiar, activa webhook de n8n y mensajea sobre el cambio.
2. api de botón negro: mudar usuario a una cuenta disponible (con return para saber proceso), agente técnico activa webhook y manda información de cuenta, cliente, números empleados activos (con roles) para informarles suceso.
3. En las vistas de cliente, mis suscripciones, si la cuenta está dañada, que tenga la alternativa de cambiarse la cuenta (botón negro para cliente), esto activa webhook y manda información de cliente y cuenta.

## Super importante y lo que se necesita urgente
1. botón de enviar mensaje a clientes de una cuenta (cuando se actualiza una cuenta necesito enviar esta información):
    - botón en vista de cuentas: Enviar mensaje a clientes (emoji o icono de whatsapp)
    - al dar clic abre un modal: Escribe un mensaje para los clientes de esta cuenta
    - al dar clic en enviar, entonces activa un webhook post de n8n, el resto me encargo yo, pero lo necesario, como los números de telefono o información de cliente y cuenta, enviar por el webhook.
    - Webhook: https://autobot.aaronsoft.es/webhook/cuenta-mensajear
    - Al enviar entonces, mensaje de sesión de mensajes enviados exitosamente, que el botón de enviar mensaje se bloquee por 10 minutos para no hacer spam.

