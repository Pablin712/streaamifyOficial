# Requisitos y backlog del módulo WhatsApp

## Estado actual

Este documento aterriza los requerimientos funcionales y técnicos del módulo de mensajería WhatsApp de Streamify.

Estado de referencia:
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
