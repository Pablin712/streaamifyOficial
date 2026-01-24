Quiero realizar algunos cambios en cuentas, te detallo en una lista

1. idcue será registrado automático y no manual, con ayuda de un trigger (implementar lógica)
2. el trigger actual que crea perfiles asociados a la cuenta habrá que analizarlo para construir el idcue
 - Agregar al formulario de creación de cuenta, en lugar de idcue, preguntar si es completa, o individual, en caso de que sea completa, entonces se registra por ejemplo para netflix: NETFLIX-1, pero en caso de ser individual entonces: IND.NETFLIX-1
3. Agregar modelos que heredan de Cuenta para los 8 servicios principales (netflix, max, prime, spotify, crunchyroll, flujo o magis, disney premium, paramount)
 - Porque cada uno se maneja distinto
 - A spotify hay que tratarlo diferente
 - Ejemplo con spotify: Modelo Spotify extends Cuenta: idcue, usuariocue, contrasenacue, fechavencue, clientes (propiedad), caidacue, Agregar propiedades: perfil1 devuelve usuariocue, contrasenacue ya que el perfil 1 es cuenta admin, desde el perfil 2, se revisaría el texto perfil.pinper y esos serían los datos usuariocue y contrasenacue de spotify, ya que estas cuentas se manejan diferente como ves, tu mismo lo sabes como funciona, funciona distinto a las demas
 
