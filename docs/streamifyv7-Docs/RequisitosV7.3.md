# Requisitos para operación del negocio
Ahora con el rol trabajador externo completado, como admin necesito un control de seguimiento de los empleados, actualmente existen las vistas control y empleados, pero no son del todo buenas.

Quiero proponer que el seguimiento esté basado en las tareas que los empleados completan, y una calculadora tipo simulación.

Las gráficas de los empleados estarían basadas en tareas y que tipo de tareas han compleatado, actualmente se ve actividad de conexión o lo que hacen en cada vista o módulo, pero lo que propongo es:

gráfico que muestra:
ventas del empleado
tareas del empleado

Eso es todo, pero el gráfico mostraría así las tareas:
total de tareas, tareas de soporte, tareas de cuentas caídas, quitar usuarios, pendientes, cobrar.

Las tareas tienen ciertos puntos de valor, ya que no todas tienen el mismo valor, digamos así:
Tareas y su valor:
1. cobrar usuario: 1pt
2. quitar usuario: 9pts
3. renovar cuenta: 5pts
4. cuenta caída: 8pts
5. ajustar espacio: 5pts
6. soporte pendiente: 9pts
7. agregar stock: 2pts

Ya que com admin, quiero saber quien se esforzó más, para pagar más, e incluso definir cuantas tareas al día puede completar al día, para ganarse su pago semanal mínimo.
Digamos:
Puntos a obtener en el día para hacer un buen trabajo:
1. Poco tiempo o esfuerzo: 50pts
2. Medio tiempo o algo de esfuerzo: 150pts
3. Trabajo normal de 7am a 12am: 300pts
4. Buen trabajo, empleado dedicado: 500pts
5. Quiere horas extra o dedicarse completo: 500+pts

Es bueno tener una sola gráfica que vea a todos los empleados ACTIVOS (con roles) para ver comparación y saber quien se esfuerza más.

## Casos de riesgo
Los empleados podrían mentir, completan tareas sin hacerlas, hacen mal o atienden a los clientes como quieran, por lo que tendría que ingeniar en algo más para que esto sea controlado y vigilado.