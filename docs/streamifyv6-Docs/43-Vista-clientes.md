# Mi actividad
Mejorar diseño de esta vista, para brindar más información, o dejar imágenes de explicación (generar imagen).

Hay que agregar una sección de soporte, para que el usuario pueda generar soporte.
## Soportes
el usuario puede ver sus soportes, habría que hacer una tabla nueva para hacer soportes.
tabla soportes:
- idsop
- idcue (para enlazar a una cuenta)
- tipo enum(sin suscripcion, contraseña incorrecta, muchos dispositivos, otro)
- descripcion
- solucion
- estado enum (pendiente, atendido)

de esta manera el usuario puede generar un soporte, el formulario de creación de soporte quedaría así:
Modal de soporte:
- seleccione la cuenta (solo cuentas de este usuario activo): viewUsuarioActivo->cuenta->usuariocue
Cada cuenta dará está información en el select: Servicio - usuariocue - fecha de vencimiento ejemplo: Netflix - test@netflix.com - 10/04/2026
- tipo: select de los tipos
- descripcion: input para que el usuario escriba

Cuando se crea un soporte por un cliente, mandar notificación al empleado (como están las demás notificaciones)

Entonces en esta sección el usuario puede generar soporte.
Del otro lado, el empleado debe tener una vista, acá puede acceder el rol técnico, en la sección de cuentas, abajo de mantenimientos: Soportes

Aquí el empleado puede ver la tabla de soportes, y atenderlos, la tabla debe verse así, esta vista debe adaptarse como las otras vistas están construídas (modals, enhanced-table, plantilla app o table, etc)
- idsop
- servicio
- usuariocue (agregar un botón para marcar cuenta como dañada o desmarcarla)
- contrasenacue
- perfil (el perfil que usa el cliente: viewUsuarioActivo->perfil->pinper y numper)
- descripcion (botón ojo para ver modal)
- solución (botón ojo verde para ver modal solo si ya fue solucionado)
- estado: pendiente o atendido. (si está pendiente, agregar botón amarillo con icono correspondiente para realizar atención, si ya está atendido entonces en un span verde indicar texto de solucionado o atendido)

## Mejorar diseño de notificaciones
Actualmente falta mucho diseño a la parte de notificaciones, cuando hay muchas por ejemplo, se despliega mucho, sobrepasa la pantalla, no se puede deslizar hasta el final para marcar a todas como leídas.
- al dar clic en una notificación me rotorna una vista pero esta no se marca como leída,
- al ser muchisimas notificaciones, se hace un despliegue muy grande para abajo, sobrepasando la pantalla y no poder ver las que están por debajo de las otras notificaciones, no se puede navegar entre muchas notificaciones.
- tal vez agregar más modernidad a este módulo sea imprecindible 
