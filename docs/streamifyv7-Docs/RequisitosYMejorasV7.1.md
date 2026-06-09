# Mejoras y requisitos para Streamify V7.1
Se necesitan full mejoras.

## Mejoras o arreglos rápidos:
Path: resources\views\employee\tareas.blade.php
1. En la vista de tareas, crear tarea manual no funciona, no se puede crear una tarea manual, no se guarda, hay que corregir esa parte.

Path: resources\views\sales\ventas\edit.blade.php
2. En la vista de actualizar venta, si se actualiza, registra ingreso demás, se crea una nueva transacción, lo que realmente debería hacer es sobreescribir esa transacción y sumar o restar la diferencia en el banco. Para que haya transparencia.

## Mejoras o arreglos para el agente:
Path del agente: docs\agente-streamify\flujo-n8n.json
1. El agente de cobranzas o vendedor, en whatsapp azul está dando otras cuentas bancarias, que pase a otra cuenta, eso es incorrecto y peligroso, los ingresos llegan a cuentas desconocidas, solo debe llegar a cuentas del titular ya configuradas en bancos, no debe confundir con las cuentas de clientes (que tal vez se las agarra en las imágenes de comprobantes). Esto únicamente sucede en el whatsapp azul, desconozco por qué en el verde está todo bien.
2. El agente de soportes tiene dos posibles caminos: 1. registrar ticket de soporte, 2. ayudar al cliente. Para el primero, basta con ver que no puede acceder a la cuenta con estos problemas: sin suscripción, cuenta caída, cuenta pide pago, etc. Para el segundo camino, lee al cliente que quiere (iniciar sesión, código de inicio, dudas) entonces el agente de soportes consulta la biblioteca, y para saber que responder y no inventar nada, en la biblioteca hay una sección por cada servicio, entonces la manera de responder será mucho más eficiente.
3. El agente de soportes registra soportes (todo bien) pero registra los soportes de tipo Cuenta pide código como "Otro", entonces hay que agregar este tipo.
4. El agente no tiene el mismo efecto en whatsapp azul, no responde bien como en el verde, no se a que se deba.

## Mejoras o arreglos en el sistema:
Path de chats: resources\views\chat\whatsapp.blade.php entre otros
1. En el módulo de chats, quiero ver en los chats etiquetas (así como están etiquetas de proveedor, cliente, bot, grupo), todos los clientes que tengan un soporte por atender, se muestra una etiqueta roja al lado del chat con label "Soporte", Los usuarios que hay que cobrar hoy (mostrados en tareas) se muestran con etiqueta de x color y label "Cobrar", los clientes que ya vencieron su usuario (hay que cortar o quitar) con etiqueta de y color y label "Quitar", hay que revisar los modelos Cuenta, ViewUsuarioActivo, CuentaService para guiarse.
2. En el módulo de chats, también se aplique el modo concentración, y en el caso de trabajadores externos, siempre este activo como lo hemos hecho antes, solo accede a chats que tengan que ver con sus tareas, se le muestra proveedores si tiene que hacer algo con las cuentas, como renovar, o reparar, y los clientes si tiene que ver con esa cuenta dañada, o quitar cuenta, o cobrar usuario.

## Requisito escalable para tener más trabajadores externos, o que los actuales trabajen más organizado
1. Para tener más trabajadores externos, o que los actuales trabajen más organizado, es necesario implementar un sistema de tareas, que se asignen tareas a cada trabajador externo, y que puedan ver esas tareas en su módulo de tareas, y que puedan marcar como realizadas, o pedir ayuda si no saben como hacerla, o pedir más tiempo si no pueden hacerla en el tiempo establecido. Esto ayudará a tener un mejor control de las tareas y a organizar mejor el trabajo de los trabajadores externos.

## Requisito escalable para el agente de Streamify
1. Agregar una tool http request a los subagentes para que envíen imágenes.
- el api para media de evo api es curl --request POST \
  --url https://evolution-example/message/sendMedia/{instance} \
  --header 'Content-Type: application/json' \
  --header 'apikey: <api-key>' \
  --data '
{
  "number": "<string>",
  "mediatype": "<string>",
  "mimetype": "<string>",
  "caption": "<string>",
  "media": "<string>",
  "fileName": "<string>",
  "delay": 123,
  "linkPreview": true,
  "mentionsEveryOne": true,
  "mentioned": [],
  "quoted": {
    "key": {
      "id": "<string>"
    },
    "message": {
      "conversation": "<string>"
    }
  }
}
'
- el almacenamiento de las imágenes de la biblioteca estarían en storage/app/public/agente
- habría una tool de get de imágenes, que solo trae la descripción y el id de imagen, para que el agente sepa qué envía.
- en la vista de biblioteca del agente, crearíamos un nuevo apartado para almacenar las imágenes del agente que podrá enviar.
