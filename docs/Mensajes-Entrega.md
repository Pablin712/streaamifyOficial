# Como es conveniente entregar a los clientes

## Netflix, disney, max, prime video, paramount, crunchyroll:
el mensaje sigue siendo el mismo formato:
*DISNEYP*
Usuario: tuli174849@outlook.com
Clave: @premium266
PIN de perfil Nro 1: 6768
*Prohibido:* Modificar perfiles o contraseñas.

## Spotify
el mensaje para copiar actualmente está construido así:
🎵 *SPOTIFY PREMIUM* 🎵

👤 *Usuario:* revillamarcia27@gmail.com
🔑 *Contraseña:* Fr3ddy:)123
📍 *Perfil:* #2

*Prohibido:* Modificar perfil o contraseña.
¡Gracias por tu confianza! 🎶

En spotify no es necesario indicar el perfil, por lo que quiero eliminar la indicación del perfil, viendose así:
🎵 *SPOTIFY PREMIUM* 🎵

👤 *Usuario:* revillamarcia27@gmail.com
🔑 *Contraseña:* Fr3ddy:)123

*Prohibido:* Modificar perfil o contraseña.
¡Gracias por tu confianza! 🎶

Y mejorar los mensajes de recordatorio, actualmente, quien tiene el perfil 1 en spotify, se puede copiar el mensaje usando las credenciales de la cuenta, y no del perfil, ya que el perfil 1 de spotify, con la cuenta de spotify son las mismas credenciales, así que prefiero que para únicamente el perfil 1, tome en cuenta las credenciales de la cuenta, y no del perfil (usuariocue, contrasenacue), los otros datos del usuario si los toma del modelo correspondiente, pero para usuario y contraseña las toma de Cuenta.

## Flujo TV
También con id de servicio MAGIS, aquí en cambio no existen perfiles, por lo que el copiar mensaje se ve algo así: 
*FLUJO*
Usuario: 6sebas
Clave: messi10goat
PIN de perfil Nro 1: 1111
*Prohibido:* Modificar perfiles o contraseñas.

aquí quiero quitar perfil del mensaje, que solo se vea usuario y clave, y el mensaje de prohibido, sería prohibido modificar contraseña y nada más

## Agregar mensaje de entrega de venta (como factura) a clientes
Cuando se vende, se entrega al cliente con el formato ya visto, pero falta la fecha de vencimiento del cliente, lamentablemente no se puede obtener esta información desde cuentas y perfiles, sino desde usuarios activos (ViewUsuarioActivo), por lo que quiero que la vista ventas, tenga la opción de acción de copiar un mensaje:
*DISNEYP*
Usuario: tuli174849@outlook.com
Clave: @premium266
PIN de perfil Nro 1: 6768
Fecha límite: $venta->detalle->fecha_vencimiento
*Prohibido:* Modificar perfiles o contraseñas.

algunas ventas tienen algunos detalles de venta, porque los clientes compran mucho, entonces sería grandioso agregar en el mensaje todos estos detalles:
*DISNEYP*
Usuario: tuli174849@outlook.com
Clave: @premium266
PIN de perfil Nro 1: 6768
Fecha límite: 24/04/2026

*FLUJO*
Usuario: 6sebas
Clave: messi10goat
PIN de perfil Nro 1: 1111
Fecha límite: 24/04/2026

*Total*: $5,99
*Prohibido:* Modificar perfiles o contraseñas.

### Agregar mensaje de copiar por Modal al crear una venta
En la vista de crear ventas, quiero que al crearse una venta, se pueda ver un modal de copiar mensaje de venta, y al dar clic en copiar entonces se copia al portapapeles

Esta es una gran mejora y será de gran utilidad para comunicación con el cliente
