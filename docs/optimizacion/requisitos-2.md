# Mejoras, Requisitos v2
Quiero seguir mejorando al sistema, ayúdame uno por uno.
1. **Mejorar gráfico de barras del dashboard**
 - Actualmente, se muestran de los últimos 20 días cuando se filtra por días, 13 últimas semanas cuando se filtra por semanas, 9 últimos meses cuando se filtra por meses, y así. Entonces quiero que se muestre historial completo, sin problemas de lentitud o carga, como funcionan los brokers, se puede deslizar a la izquierda para ver más atrás en el tiempo, en cualquiera de sus filtros.
2. **Mejorar la tarea de agregar stock**
 - Actualmente, crea tareas de agregar stock de youtube, dgo etc, esos servicios no son los principales, los que quiero que si agregue stock, son los servicios principales, calculando espacios a ver si hay menos de 3, entonces agrega stock, básate en la vista de cuentas, en ese controller, se usa CuentasService, el cual ya cuenta con el método de calcular espacios por servicio, y los servicios principales son Netflix, Disney Premium, Spotify, Max, Prime, Crunchyroll, Paramount y Flujo tv.
3. **Guardar total de tareas en dailyStatistics**
 - Para mejorar control, las tareas generadas cada día también se guardan en dailyStatistics, esto ayudará a organizar en un futuro metas, sueldo, o compensación por tareas completadas para los empleados.
