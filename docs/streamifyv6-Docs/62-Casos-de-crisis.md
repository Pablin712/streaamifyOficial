# ¿Qué hacer cuando se caen masivamente las cuentas?
En casos de caídas masivas, el enfoque debe ser la comunicación proactiva y la gestión eficiente de la crisis. Aquí hay algunos pasos clave a seguir:

Este trabajo es para el subagente de soporte.

1. El agente gracias a la api de get cliente o usuarios activos puede obtener los usuarios de este, así como el estado de las cuentas que posee, entonces este sabe si una cuenta está dañada o no.
2. Quiero que el subagente de estas alternativas al cliente: Que acepte que le demos otra plataforma como garantía, por ejemplo prime video, max, crunchyroll, paramount, flujo tv, las cuales son mucho más rentables y estables, ya que las más populares son así mismo las más complicadas y las que más caen: Netflix, Spotify, y por hoy Disney (muy rara vez disney)
3. Si el cliente no acepta la alternativa, entonces se le pide espera, porque no se dará compensación o reembolsos, ya que todos perdimos al invertir o comprar estas cuentas.
4. Entonces quiero que el subagente sea capaz de cambiar un usuario a otro servicio (así como ya existen los botones de cambio a otro servicio en las vistas de perfiles o usuarios activos), esta api tendría:
POST /api/v2/chat/assistant/postventa/cambio-servicio
Body:
{
  "telefono": "1234567890",
  "nuevo_servicio": "prime_video"
  "iddet": "56",
}
y el subagente puede usar esta api si y solo sí el cliente ocupa una cuenta dañada y la cuenta pertenece a los servicios de Netflix, Spotify o Disney, que son las más dificultosas. 
Entonces en ese caso está autorizado en hacer eso.
Que por cierto, quiero que la fecha de vencimiento se dé una semana más de la que tiene el cliente en esa cuenta dañada como compensación, que no sea la misma fecha de vencimiento de la cuenta dañada, entonces, al entregar la nueva cuenta, tiene el servicio más tiempo.

Esta es la fórmula que quiero usar para ofrecer garantía a los clientes.

## Caso para spotify
Esto es 100% humano, los clientes por lo general quieren si o si spotify, entonces se les da la opción de cambiar de servicio, pero estos prefieren esperar spotify. Así que aquí el agente tan solo pide paciencia que ya le atienden, y registra el soporte.

Para el caso de hoy, 6 de mayo 2026, quiero hacer esto solo para netflix y disney, ya que en eso estoy ahora, les mandaré mensaje masivo a todos, y luego, cuando mensajeen los clientes, el subagente se encargará de cambiarlos conforme estos decidan. hasta mientras yo puedo preparar stock para las cuentas:
Max
Prime video
Crunchyroll
Paramount
Flujo TV

Confirmame si entendiste y hagamoslo ya! y que esté super bien, y funcional! que me saque de este aprieto y estrés laboral
