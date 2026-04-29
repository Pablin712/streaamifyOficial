# Requisitos y backlog del módulo WhatsApp

## Estado actual

Este documento aterriza los requerimientos funcionales y técnicos del módulo de mensajería WhatsApp de Streamify.

Estado de referencia:
- Base principal del módulo en `app/Livewire/Chat/WhatsAppHelpdesk.php` y `resources/views/livewire/chat/whatsapp-helpdesk.blade.php`.
- Ya existe búsqueda de conversaciones por cliente/número.
- Ya existe soporte para envío de imagen y audio desde el helpdesk.
- Ya existe soporte de canales WhatsApp con color `verde` o `azul`.
- Ya se corrigió el salto automático al final del chat cuando no entran mensajes nuevos.
- Ya se agregó el indicador visual del canal WhatsApp en la lista y en el encabezado del chat.

## Prioridad alta

### 1. Navegación estable en historial de mensajes
Estado: hecho.

Objetivo:
- Permitir revisar mensajes antiguos sin que el polling regrese automáticamente al fondo del chat.

Resultado aplicado:
- El scroll automático ahora solo ocurre cuando cambia el último mensaje de la conversación activa.

### 2. Identificador visual del canal WhatsApp
Estado: hecho.

Objetivo:
- Mostrar un indicador pequeño de color verde o azul según la instancia de WhatsApp de origen.

Resultado aplicado:
- Se muestra el indicador del canal en la lista de conversaciones.
- Se muestra el indicador del canal en el encabezado del chat activo.

### 3. Rediseño del bloque de operador y estado de atención
Estado: pendiente.

Objetivo:
- Hacer más atractivo y claro quién atiende la conversación.
- Diferenciar mejor los estados: sin asignar, atendido por otro operador, en escritura, cerrado, reabierto.

Alcance sugerido:
- Reemplazar texto plano por chips o tarjetas visuales.
- Resaltar operador asignado, última actividad y estado del turno.
- Mejorar contraste, jerarquía tipográfica y espaciado.

Archivos probables:
- `resources/views/livewire/chat/whatsapp-helpdesk.blade.php`
- `app/Livewire/Chat/WhatsAppHelpdesk.php`

### 4. Búsqueda de mensajes dentro del chat activo
Estado: pendiente.

Observación:
- Ya existe búsqueda de conversaciones, pero no búsqueda dentro del historial de mensajes de una conversación seleccionada.

Objetivo:
- Buscar texto dentro de mensajes del contacto actual.
- Navegar entre coincidencias sin perder la posición del scroll.

Alcance sugerido:
- Campo de búsqueda dentro del panel central.
- Resaltado de coincidencias.
- Paginación o carga incremental del historial para evitar renderizar todo el hilo.

Archivos probables:
- `app/Livewire/Chat/WhatsAppHelpdesk.php`
- `resources/views/livewire/chat/whatsapp-helpdesk.blade.php`
- Posible apoyo en servicios de chat para consultas optimizadas.

## Prioridad media

### 5. Personalización visual del helpdesk
Estado: pendiente.

Objetivo:
- Hacer la interfaz más intuitiva y configurable.

Opciones sugeridas:
- Densidad compacta o cómoda.
- Preferencia de panel derecho visible u oculto.
- Tamaño de fuente del chat.
- Preferencia de auto-scroll configurable.

### 6. Rendimiento del panel de mensajería
Estado: pendiente.

Problemas observados:
- El módulo usa `wire:poll.3s`, lo que fuerza refrescos completos frecuentes.
- El historial activo parece renderizar todos los mensajes de una vez.

Objetivos:
- Reducir tiempo de refresco visual.
- Reducir costo de render y consultas repetidas.
- Mejorar percepción de inmediatez al enviar.

Líneas de trabajo sugeridas:
- Reemplazar polling general por eventos o refrescos parciales.
- Cargar mensajes por bloques al subir en el historial.
- Mantener cacheados conteos y configuraciones estables.
- Optimizar consultas del listado y del chat activo.

### 7. Notificaciones en tiempo real más livianas
Estado: parcial.

Observación:
- Ya existe una notificación sonora básica cuando aumentan los no leídos.

Pendiente:
- Llevar la sincronización a eventos más finos para evitar refrescos innecesarios.
- Mostrar cambios de estado sin repintar todo el panel.

## Prioridad funcional

### 8. Identificación de contactos por tipo
Estado: pendiente.

Objetivo:
- Diferenciar clientes, proveedores, grupos, bots de código y contactos desconocidos.

Necesidad funcional:
- Poder registrar un número desconocido como contacto relevante.
- Soportar contactos que no son clientes pero sí operativos para el negocio.

Diseño propuesto:
- Campo `tipo_contacto` en la capa de contacto canal.
- Etiquetas visuales por tipo.
- Acciones rápidas: guardar como cliente, proveedor o grupo.

### 9. Registro outbound desde modales operativos
Estado: parcial.

Observación:
- Ya existe persistencia outbound en varios flujos del backend.
- Falta revisar si todos los modales de mensajes de entrega están registrando en la misma capa de conversación/historial visible para operadores.

Objetivo:
- Garantizar que cualquier envío manual desde modales quede trazado en el historial correcto.

Archivos probables:
- `app/Http/Controllers/UsuarioController.php`
- `app/Http/Controllers/CuentaController.php`
- `app/Services/Chat/WhatsAppHelpdeskService.php`
- `app/Http/Controllers/Api/V2/ChatRouterController.php`

### 10. Botones WhatsApp verde y azul en vista de usuarios
Estado: parcial.

Observación:
- El backend ya tiene soporte para resolver canales outbound.
- Falta confirmar o completar la UI para lanzar mensajes directos desde la vista de usuarios con selección explícita de canal.

Objetivo:
- Mostrar dos acciones rápidas por usuario: enviar por WhatsApp verde y enviar por WhatsApp azul.

### 11. Envío de fotos más natural en el compositor
Estado: parcial.

Observación:
- Ya existe carga de imágenes desde selector de archivos.
- Lo pendiente es experiencia tipo WhatsApp: pegar desde portapapeles y previsualizar sin pasos extra.

Objetivo:
- Permitir pegar una imagen copiada al portapapeles directamente en el chat.

Alcance sugerido:
- Escuchar evento `paste` en el compositor.
- Detectar `image/*` del portapapeles.
- Cargar preview automática y confirmar envío.

## Orden recomendado de ejecución

1. Rediseño del bloque de operador y estados de atención.
2. Búsqueda dentro del chat activo.
3. Optimización de rendimiento con carga incremental de mensajes.
4. Tipificación de contactos y acciones para guardar desconocidos.
5. Botones de envío rápido por canal en vista de usuarios.
6. Pegado de imágenes desde portapapeles.
7. Auditoría final de registro outbound en todos los modales operativos.
