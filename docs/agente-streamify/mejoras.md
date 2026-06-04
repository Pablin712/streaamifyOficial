# Agente IA Streamify v3
Actualmente tengo la v2 del agente, aun no funciona como yo quiero el agente de Streamify (el que atiende a los mensajes que llegan de whatsapp en la vista de whatsapp, o el flujo n8n que te compartiré).
*flujo:* docs/agente-streamify/flujo-n8n.json
## Mejoras que quiero implementar
1. Que el agente si responda normal con emojis y muy amable, como son los modelos modernos, con emociones, explican con listas, numeraciones, etc. Pero que de un formato que soporte whatsapp.
2. Que el subagente de soporte detecte un problema con la cuenta del cliente, y aunque este intente ayudar al cliente con el acceso a la cuenta que posee, aun así registre el soporte, de esta forma nos aseguramos, luego de eso entra en handoff para no spamear de mensajes.
3. Que el agente tenga un árbol de decisiones: complejo, dinámico y configurable, el admin puede editar esto en una vista de configuración del agente de whatsapp. El árbol de decisiones tendría caminos (como si fuese un árbol binario) en el que se ayuda a decidir que responder al usuario (aunque para esto ya están los subagentes) será posible implementar esto? si hacemos esto, será que unificamos los subagentes en uno solo? o conviene mantener la actual arquitectura? la actual tiene un agente router, y 5 subagentes para distintos casos.
4. Que las respuestas del agente sean directas, y no tan largas, sean directas, atendiendo a la conversación, solo mencionando lo relevante, resumido, preciso, y no tanto texto para que el cliente no le de pereza leer.
5. Los modelos de los agentes serán con deepseek mismo, aunque en un futuro serán con claude console, cuando pueda agregar la credencial (debo actualizar el docker)
api key de claude console:
