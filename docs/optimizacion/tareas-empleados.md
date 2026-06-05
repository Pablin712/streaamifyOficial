# Optimización de reparto de tareas a empleados
Actualmente, cada empleado tiene un rol (bodeguero, admin, contador, tecnico, vendedor), pero todos estos tienen permisos de entrar a las cuentas (todas), todos los usuarios, clientes, todas las ventas, etc. Es decir, si un empleado tiene permiso de un crud, tiene permiso de todos los registros de esa tabla o vista.
Esto es un gran problema de seguridad y organización, ya que un empleado gestiona cuentas y puede cruzarse con el mismo trabajo al mismo tiempo con otro empleado.
Para solucionar esto, se propone implementar un sistema de asignación de tareas a empleados, donde cada empleado solo tenga acceso a las tareas que se le han asignado.
Además, el sistema ha crecido tanto, que los empleados tienen muchísimas tareas, por lo que se necesita más ayuda.

# Ejemplo Real de hoy
Existen 4 empleados: Pablo, Mateo, Ronaldo, Karol
Actualmente, Pablo es contador y admin, se encarga de revisar que todo esté bien, pagar cuentas, anotar gastos, gestionar bancos. Mateo es admin, y trabajador (ayuda a Pablo, y además mantiene todo el negocio, como si fuese un gerente, vende, da soporte). Ronaldo es cobrador (rol vendedor) y se encarga de cortar usuarios que no renuevan, y recordar o presionar a que paguen renovación. Karol ayuda a Mateo, con soporte, y con ventas.
Actualmente, el trabajo ha crecido, ahora hay más cuentas que gestionar, más clientes que cobrar, y que dar soporte, por lo que se necesitan más empleados:

Se unirán dos a la ayuda, y con el tiempo, más: Darío y Yadira
# Solución Propuesta
Está bien actualmente la asignación de roles y permisos, lo que quiero implementar, es una mejora de tareas, en tareas, el sistema solo crea 5, y son muy generales:
- Hay 20 cuentas caídas que arreglar
- Hay 114 usuarios a cobrar
- Hay 50 cuentas por renovar o quitar
- Hay 30 tickets de soporte

y la mejora que propongo es, que por cada usuario a cobrar, se cree una sola tarea, por cuenta caída otra tarea, es decir tareas individuales.
- lista de 20 tareas de cuentas caídas
- lista de 114 tareas de usuarios a cobrar
- lista de 50 tareas de cuentas por renovar o quitar
- lista de 30 tareas de soporte

entonces entre los 4 empleados que ya están, y los dos nuevos a entrar (total 6) se dividen las tareas.
- Pablo toma a su elección sus tareas, y como jefe, puede asignar a los demás empleados tareas (rol admin), el escogió 25 tareas de cuentas por renovar o quitar (sobran 25 tareas sin asignar)
- Mateo toma 20 tareas de cuentas por renovar o quitar, y 20 tareas de soporte (sobran 5 tareas sin asignar), y 10 tareas de soporte (sobran 20 tareas de soporte), también es jefe, o gerente (admin) que sería el segundo al mando (después de Pablo) también puede asignar tareas a los empleados.
- Ronaldo toma 80 tareas de usuarios a cobrar (sobran 34 tareas sin asignar)
- Karol toma 20 tareas de usuarios a cobrar (sobran 14 tareas sin asignar), y 10 tareas de soporte (sobran 10 tareas sin asignar)
- Darío toma 14 tareas de usuarios a cobrar (sobran 0 tareas sin asignar) y 20 tareas de cuentas caídas.
- Yadira toma 10 tareas de soporte (sobran 0 tareas sin asignar)

Entonces, cuando estos empleados hayan escogido su total de tareas (o un admin se las asignó) estos deben cumplirlas, y tienen acceso a las vistas necesarias y solo de los registros necesarios que necesiten. Ejemplo: Yadira
tiene asignada 10 tareas de soporte, entonces ella solo tiene acceso a esas 10 tareas, y no a las otras tareas de soporte, ni a las tareas de cuentas por renovar o quitar, ni a las tareas de usuarios a cobrar, ni a las tareas de cuentas caídas. De esta manera, cada empleado solo tiene acceso a las tareas que se le han asignado, y no a las tareas de los demás empleados, lo que mejora la seguridad y la organización del trabajo. Y cuando abra la vista cuentas, solo puede ver las cuentas de sus tareas. Cuando entre a usuarios, solo puede ver los usuarios de sus tareas (los que estén en soporte, o cuenta con soporte, etc), cuando entre a ventas, solo tiene acceso a ventas relacionadas con esas cuentas y usuarios de soporte.

# Alcance de empleados
Los empleados, si no se les asignó tareas, estos pueden escoger sus tareas, para que vayan haciendo, sin esperar a que se les sea asignados. 
Cuando un empleado acaba sus tareas, vuelve el mismo patrón, puede escoger sus tareas, sin esperar a que un admin le asigne, si no hay tareas por hacer, ya que las demás están ocupadas por otros empleados, aun así el empleado puede tomar ciertas tareas, algunas o todas, para ayudar a ese empleado. Esto serviría para cuando un empleado, pide ayuda, permiso, o no está activo.

# Beneficios
- Mayor organización y control de las tareas asignadas a cada empleado.
- Mayor seguridad, ya que cada empleado solo tiene acceso a las tareas que se le han asignado.
- Mayor eficiencia, ya que cada empleado puede enfocarse en sus tareas asignadas sin distracciones.
- Mayor satisfacción de los empleados, ya que se sienten más valorados y reconocidos por su trabajo.
- Mayor facilidad para medir el rendimiento de cada empleado, ya que se pueden asignar tareas específicas y medir su desempeño en base a ellas.
- Mayor facilidad para identificar ár
eas de mejora, ya que se pueden analizar las tareas asignadas a cada empleado y detectar posibles cuellos de botella o áreas de oportunidad.

# Requisitos en la vista tareas
1. Como admin quiero asignar tareas a los empleados, en bloque o individuales.
2. Como empleado quiero escoger mis tareas, para ir haciendo sin esperar a que me asignen.
3. Como empleado quiero ver solo las tareas que me han asignado, para enfocarme en realizarlas. Quiero un botón en navigation "Modo concentración", si lo tengo activado, entonces las vistas de cuentas, usuarios, clientes, ventas, etc.. se limitan a solo las que estén relacionadas con las tareas que debo hacer.
ejemplo: agarré 10 tareas de usuarios por cobrar, entonces activo mi modo concentración, cuando vaya a cuentas solo puedo ver las cuentas que usan esos usuarios, cuando vaya a usuarios, puedo ver solo esos usuarios, cuando vaya a ventas, solo a ventas de esos clientes, y así. En cambio si desactivo mi modo concentración, entonces se muestra todo en cada vista que tengo acceso.
4. Como admin quiero ver el progreso de cada empleado, para saber cómo va con sus tareas asignadas. (mejorar vista de control de empleados existente)
5. Como admin quiero poder reasignar tareas a otros empleados, para optimizar la carga de trabajo.
6. Como admin quiero crear un nuevo rol Trabajador externo, el cual no tiene un modo concentración, ya que no tiene acceso a todos los registros de las vistas, solo tendrá acceso a las vistas, y registros de las vistas de sus tareas.
7. Como admin, quiero facilitar el trabajo a empleados, que haya botones de acción para acelerar el proceso, como:
- Botón mandar mensaje whatsapp (último o único canal por donde escribe el cliente) con una plantilla de mensaje de cobro predefinida por el empleado en alguna configuración.
- Botón mandar mensaje whatsapp (último o único canal por donde escribe cada cliente) con una plantilla de mensaje de cobro predefinida, el botón manda mensaje de cobro a todos los clientes.
- Botón mandar mensaje whatsapp (último o único canal por donde escribe cada cliente) con una plantilla de mensaje de cobro predefinida, el botón manda mensaje de cobro a los clientes seleccionados.
