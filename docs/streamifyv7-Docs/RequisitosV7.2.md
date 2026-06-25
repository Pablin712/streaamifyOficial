# Requisitos para el sistema de Streamify
Nuevos requisitos, todo se tratará del módulo de soportes.
El objetivo es optimizar el tiempo de respuesta y resolución de las cuentas dañadas para que los clientes puedan disfrutar de estas, y su experiencia como usuario sea la mejor.
*¿Cómo lo haremos?*
enfocarse en el rol Trabajador externo, sus vistas se filtrarán, y solo mostrarán a este usuario la información permitida o asignada en tareas.

Si tiene tareas de soporte un trabajador externo, tiene acceso a estas vistas y visibilidad:
1. Vista de soportes - Solo sus casos asignados
2. Vista de chats - Solo de clientes con los casos asignados
3. Vista de cuentas - Solo de cuentas relacionadas con los clientes de los casos asignados
4. Vista de usuarios - Solo de usuarios relacionados con las cuentas de los casos asignados

Si tiene tareas de renovar cuentas, tiene acceso a estas vistas:
1. Vista de chats - Solo de proveedores asignadas a esas cuentas
2. Vista de cuentas - Solo de cuentas asignadas en sus tareas
3. Vista de usuarios - Solo de usuarios relacionados a las cuentas asignadas

Si tiene tareas de quitar usuarios, tiene acceso a estas vistas:
1. Vista de chats - Solo de clientes de las tareas asignadas
2. Vista de cuentas - Solo de cuentas relacionadas a los usuarios a quitar asignados
3. Vista de usuarios - Solo de usuarios a quitar asignados

Si tiene tareas de cobrar usuarios
1. Vista de chats - Solo de clientes de las tareas asignadas
2. Vista de usuarios - Solo de usuarios a cobrar asignados

Si tiene tareas de Arreglar cuentas caídas
1. Vista de soportes - Solo casos que se relacionen con las cuentas caídas de sus tareas asignadas
2. Vista de chats - Solo de clientes relacionados con las cuentas caídas asignadas
3. Vista de cuentas - Solo de cuentas caídas asignadas
4. Vista de usuarios - Solo de usuarios relacionados con las cuentas caídas asignadas

Si tiene tareas de ajustar espacios
1. Vista de chats - Solo los proveedores relacionados a las cuentas colapsadas asignadas
2. Vista de cuentas - Solo las cuentas colapsadas asignadas
3. Vista de usuarios - Solo usuarios relacionados a las cuentas colapsadas asignadas

Si tiene tareas de agregar stock
1. Vista de chats - Todos los proveedores
2. Vista de cuentas - Ninguna cuenta, solo a la vista y botones
3. Vista de servicios - Todos los servicios
4. Vista de valores - Todos los valores