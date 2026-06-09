# Mejoras y requisitos para Streamify V7.1
Se necesitan full mejoras.

## Mejoras o arreglos rápidos:
1. En la vista de tareas, crear tarea manual no funciona, no se puede crear una tarea manual, no se guarda, hay que corregir esa parte.

## Mejoras o arreglos para el agente:
1. El agente de cobranzas o vendedor, en whatsapp azul está dando otras cuentas bancarias, que pase a otra cuenta, eso es incorrecto y peligroso, los ingresos llegan a cuentas desconocidas, solo debe llegar a cuentas del titular ya configuradas en bancos, no debe confundir con las cuentas de clientes (que tal vez se las agarra en las imágenes de comprobantes). Esto únicamente sucede en el whatsapp azul, desconozco por qué en el verde está todo bien.
2. El agente de soportes tiene dos posibles caminos: 1. registrar ticket de soporte, 2. ayudar al cliente. Para el primero, basta con ver que no puede acceder a la cuenta con estos problemas: sin suscripción, cuenta caída, cuenta pide pago, etc. Para el segundo camino, lee al cliente que quiere (iniciar sesión, código de inicio, dudas) entonces el agente de soportes consulta la biblioteca, y para saber que responder y no inventar nada, en la biblioteca hay una sección por cada servicio, entonces la manera de responder será mucho más eficiente.
3. El agente de soportes registra soportes (todo bien) pero registra los soportes de tipo Cuenta pide código como "Otro", entonces hay que agregar este tipo.
4. El agente no tiene el mismo efecto en whatsapp azul, no responde bien como en el verde, no se a que se deba.

## Mejoras o arreglos en el sistema:
1. En el módulo de chats, quiero ver en los chats etiquetas, todos los clientes que tengan un soporte por atender, se muestra una etiqueta roja al lado del chat con label "Soporte", Los usuarios que hay que cobrar hoy (mostrados en tareas) se muestran con etiqueta de x color y label "Cobrar", los clientes que ya vencieron su usuario (hay que cortar o quitar) con etiqueta de y color y label "Quitar", hay que revisar los modelos Cuenta, ViewUsuarioActivo, CuentaService para guiarse.
2. En el módulo de chats, también se aplique el modo concentración, y en el caso de trabajadores externos, siempre este activo como lo hemos hecho antes, solo accede a chats que tengan que ver con sus tareas, se le muestra proveedores si tiene que hacer algo con las cuentas, como renovar, o reparar, y los clientes si tiene que ver con esa cuenta dañada, o quitar cuenta, o cobrar usuario.

