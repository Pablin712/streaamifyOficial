# Clientes de software
Ya no tengo solamente clientes de Streaming, ahora tengo clientes de software, especialmente deseo escalar a más clientes que ocupen a Donna, la secretaria que hice con n8n, telegram/whatsapp, deepseek y openAI.

Para poder gestionar a estos clientes, necesito poder guardar sus credenciales de google, sus lógica de negocio, y sus chats.

## Requisitos y visión de este negocio dentro de Streamify
1. tabla de clientes (ya existe)
2. tabla de chats de clientes: aquí se almacenarían los chats provenientes de whatsapp de los clientes, pueden ser audios, imágenes, imagen con texto, videos, etc, todos los tipos de mensaje quiero que sean guardados.

Entonces, habría api ingest y respond más avanzado, ya que se almacenarían los chats de muchos clientes, todo ahí mismo.

3. vista de gestión de chats de clientes: aquí podría crear un nuevo canal de whatsapp de un cliente, selecciono cliente, agrego su api key, y su nombre de instancia. también habrían más opciones de configuración, como lógica de negocio, nombre de negocio, contexto, que hace, etc, para que el agente Donna, pueda entender y sea personalizada para cada cliente. Entonces en n8n estaría usando la información guardada en streamify, y no un prompt específico. Y no editaría los prompts en n8n, sino en streamify todo, el flujo de n8n estaría estático sin cambios, solo duplicaría, en streamify agregaría:
*Nuevo cliente*
- nombre del cliente (select o crear)
- api key de whatsapp
- nombre de instancia
- lógica de negocio (texto)
- contexto (texto)
- que hace (texto)
- prompt para el agente

