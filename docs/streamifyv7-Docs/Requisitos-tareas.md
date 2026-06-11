# Requisitos para Tareas 10 de junio 2026
las ubicaciones de las vistas principales a trabajar están en:
*tareas:* resources\views\employee\tareas.blade.php
*actualizar venta:* resources\views\sales\ventas\edit.blade.php
*cuentas:* resources\views\accounts\index.blade.php
*soportes:* resources\views\supports\index.blade.php
*chats:* resources\views\chat\whatsapp.blade.php entre otros

1. Las tareas de soporte no especifican de que cliente o que cuenta necesita soporte, así mismo para renovar cuentas, y todas las demás tareas, se debe informar lo más relevante, necesario para realizar la tarea, lo más urgente es la tarea de soporte para cada cliente.
2. Para las tareas de soporte, se debe mostrar un botón para ir directo al chat del cliente, y así poder ayudarlo lo más rápido posible, o que vaya a la vista de soportes y abra el modal de ese soporte para solucionar o ver la descripción del soporte.
3. Cuando una tarea se complete, esa tarea se marque como completa sin necesidad de que el empleado vaya donde la tarea a completarla manualmente, es decir, que las tareas estén vinculadas en todo el sistema, para que sea un sistema inteligente y detecte las actividades realizadas.
4. Para las tareas de cobrar, si el usuario está marcado como cobrado, entonces se inhabilita ese botón, o mejor, que la tarea se marque como completada, cuando ya se cobró a ese cliente (mandandole mensaje desde la tarea, o marcando cobrado en usuarios) entonces la tarea de cobro se ha completado.


Para realizar estas mejoras hay este principal reto:
1. No existen vistas individuales por tarea, o cuenta, usuario, etc. Ya que todo se maneja por una única vista index en cada crud, donde hay varios modals.
2. Esta lógica es compleja, así hay que hacer un plan para que todo salga super bien, y optimizar el trabajo para los empleados.

