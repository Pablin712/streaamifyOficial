# Requisitos para la optimización de Streamify 7.4
Ahora, quiero aprovechar asistencias, se me ocurrió esta idea, que cada empleado pueda elegir su horario de conexión en calendario, por lo que deberá haber una pestaña más en calendario para que muestre horarios de los empleados.

Este horario tendría la función para cada empleado, de escoger sus horarios de conectividad ciertos días o semanas futuras, y conforme pasen los días, con la ayuda de asistencias.ping se verifica que el empleado haya estado conectado esas horas, días, o semanas reservadas, incluso con las tareas que completó.

## Vista para empleado:
el empleado externo o cualquier otro rol no admin, podría ver el calendario semanal o mensual, agendarse, e incluso cancelar ciertos días (decir que no estará activo) por un evento que este tuviera, podría ver horarios donde otros empleados estarán conectados, o nadie esté conectado, para dar prioridad si es que este empleado puede conectarse en los días donde nadie está conectado, las horas o días pueden estar ocupadas por varias personas en simultáneo, no hay problema, o puede que el admin configure un límite de personas que estarán conectadas ciertos días o semanas.

## Vista para admin y gerente
El admin, podría ver el calendario semanal o mensual, de sus empleados con detalles, aquí se usa el control de asistencias, para verificar que los empleados si asistieron o no en las fechas pasadas reservadas, o incluso si un empleado no reservó su horario, o no estaba su horario de atención definido y estuvo en esas horas trabajando, igual se registrará, para reconocer actividad del empleado.

El empleado puede ser que falle en horas o minutos, en donde no se encuentra presente, entonces para ayudar al admin a ver todo esto, el calendario mostraría actividad de todos los empleados (activos con roles) en simultáneo, o seleccionar uno o algunos para ver por separado, y ver su actividad con distintos colores.
1. horarios futuras que el empleado confirmó su presencia y conectividad: amarillo
2. horarios pasados que el empleado asistió a su compromiso (sus reservas son confirmadas por tabla de asistencias): verde
3. horarios padados que el empleado no asistió a su compromiso: rojo
4. horarios pasados que el empleado asistió, pero no estaba registrado su compromiso (horarios extra del empleado): azul

El admin si puede reservar compromisos de los empleados, igual que el gerente

## Cuando se vea la actividad de 2 o más empleados
Mapa de calor:
0 empleados conectados: sin color, blanco
1 empleado conectado: color respectivo claro
2 empleados conectados: color respectivo un poco más intenso
4 empleados conectados: color respectivo con intensidad normal
...
10 empleados conectados: color respectivo muy muy oscuro, casi negro

Puse color respectivo, ya que la paleta que se usará es depende sean fechas futuras comprometidas (amarillo), asistencias confirmadas (verde), faltas a su compromiso (rojo), horarios extra (azul)

