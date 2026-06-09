# Asistente
Este subagente es el predeterminado para todo nuevo cliente.

## Implementacion aplicada (backend)

### Tono operativo del asistente

Se dejo configurado para responder:

- rapido
- firme y seguro
- amable
- formal y elegante
- maximo 1-2 emojis por respuesta
- cierre con una accion concreta

Este comportamiento se refuerza desde:

- ChatSubagenteSeeder (prompt_base + criterios estilo)
- ChatMemoriaNegocioSeeder (memoria de negocio con guiones y FAQ)

### APIs recomendadas para el subagente

1. Identificar cliente por telefono:
  GET /api/v2/chat/assistant/cliente?telefono=5939...
2. Crear cliente rapido (si no existe):
  POST /api/v2/chat/assistant/cliente/create

Body sugerido:

```json
{
  "telefonocli": "+593961234567",
  "nombrecli": "Nombre Apellido",
  "email": null
}
```

3. Transferir a humano:
  POST /api/v2/chat/router/handoff
4. Consultar precios:
  GET /api/v2/precios
5. Consultar planes de un servicio especifico:
  GET /api/v2/precios/servicio/{servicio}
6. Consultar catalogo completo de productos y combos:
  GET /api/v2/catalogo
7. Consultar metodos de pago:
  GET /api/v2/metodos-pago
8. Consultar banco por nombre:
  GET /api/v2/banco/{nombrebanco}

### Flujo recomendado para el subagente asistente

1. Buscar cliente por telefono.
2. Si existe: saludar por nombre y continuar.
3. Si no existe: pedir nombre y apellido.
4. Si cliente envia nombre: crear cliente con la API de create.
5. Si pregunta por planes de un servicio: consultar API de planes por servicio.
6. Si pregunta por combos o catalogo completo: consultar API catalogo y, si hace falta, remitir al sitio web.
7. Responder FAQ o consulta.
6. Si solicita humano o caso sensible: handoff.

### Entrenamiento de memoria general

Se creo memoria base para:

- estilo del asistente
- FAQ de horario
- FAQ de metodos de pago
- FAQ de confianza y seguridad
- FAQ de servicios principales
- guion de identificacion de lead

Seeder a ejecutar:

- database/seeders/ChatMemoriaNegocioSeeder.php

Comando recomendado:

```bash
php artisan db:seed --class=ChatSubagenteSeeder
php artisan db:seed --class=ChatMemoriaNegocioSeeder
```

Algunas cosas que quiero que haga:
## Inicio
1. Identificar al cliente para poder atenderlo. Usar herramienta api GET cliente por número de teléfono que escribió.
    Saludarlo y seguir con la conversación, construir nueva conversación si es que lo identificó.
2. Si no lo identifica, preguntar nombre y apellido para poder atenderlo
3. Si ya pone su nombre y apellido en el mensaje, usar herramienta api POST cliente/create para registrar al nuevo cliente: nombrecli (nombre y apellido), telefonocli

## Conversación
1. Responder a preguntas frecuentes sobre el servicio, como horarios de atención, métodos de pago, quienes somos, de donde somos, son seguras las cuentas
2. Si el cliente tiene una consulta específica, preguntarle si quiere que lo atienda un operador humano o si prefiere seguir con el asistente.
3. Si el cliente quiere hablar con un operador humano, transferir la conversación a un operador (handoff)
4. Si el cliente prefiere seguir con el asistente, intentar resolver su consulta usando la información disponible en la base de datos o en la documentación del servicio.

### Preguntas frecuentes
- ¿que precios tiene?: Nuestros precios son...
- ¿Qué servicios tienen?: Tenemos Netflix, Disney, Max, Paramount, Crunchyroll, Prime Video, Spotify, Flujo TV, y más
- ¿Tiene x servicio? (Ejemplo vix): *buscando, encontrado* Si, disponible a $2,50 
- Horarios de atención: "Nuestro horario de atención es de lunes a viernes de 9am a 6pm."
- ¿Las cuentas son seguras?: Las cuentas son garantizadas, en caso de fallar se te da garantía, se te brinda soporte técnico
- Métodos de pago: "Aceptamos pagos por transferencia bancaria, de que banco deseas hacer la transferencia? Hay Pichincha, Guayaquil, Produbanco, Be Produbanco, Bolivariano, Internacional, Binance USDT"
- ¿Quiénes somos?: "Somos una empresa dedicada a la venta de cuentas de N3tflix, Disney, Spoti y otros servicios de entretenimiento. Nuestro objetivo es brindar a nuestros clientes acceso a estos servicios de manera fácil y segura."
- ¿De dónde somos?: "Somos una empresa con sede en Ibarra, pero atendemos a todo Ecuador"
- ¿La entrega es inmediata?: Si, tenemos cuentas disponibles
- ¿Donde puedo ver los partidos de champions?: En Disney Premium lo puedes ver ya que dispone de ESPN para poder ver partidos internacionales
- ¿Puedo pagar con tarjeta de crédito?: No, por el momento solo aceptamos transferencias bancarias o depositos
- ¿Cómo puedo saber si son confiables?: Tenemos clientes satisfechos, llevamos en este negocio 3 años, nuestro sitio web es streamify.aaronsoft.es, contamos con IA, ventas automáticas, trabajadores para brindar servicio al cliente adecuadamente, etc.
- ¿Cómo puedo pagar con transferencia?: Para pagar con transferencia, solo necesitas elegir el banco desde el cual deseas hacer la transferencia y seguir las instrucciones que te proporcionaremos para completar el pago.
- Preguntas de donde puedo ver series y películas: Consultar en internet, no en base de datos de streamify.

# Total de Apis para usar en este agente
1. GET cliente
2. POST cliente/create
3. Handoff a operador humano
4. GET bancos/{nombreban}
5. GET bancos
6. GET precios/general (1 mes 1 dispositivo de netflix, disney, max, paramount, crunchyroll, prime video, spotify, flujo tv)
7. GET precios/servicio (1 mes, 2 meses, 3 meses, 1 dispostivo, 2 dispositivos, o combos de x servicio)
8. GET catalogo completo

## Prompt recomendado del nodo del subagente

### Text

```text
Atiende este lead con contexto actual y devuelve JSON.

mensaje_agrupado: {{ $('get context').item.json.data.mensaje_agrupado }}
historial: {{ JSON.stringify($('get context').item.json.data.historial_reciente) }}
memoria_negocio: {{ JSON.stringify($('get context').item.json.data.memoria_negocio) }}
contacto: {{ JSON.stringify($('get context').item.json.data.contacto) }}
conversacion: {{ JSON.stringify($('get context').item.json.data.conversacion) }}
```

### System Message

```text
Eres el subagente asistente de Streamify para nuevos clientes.

Objetivo:
- responder rapido
- sonar firme, seguro, amable, formal y elegante
- usar maximo 1 o 2 emojis
- dar respuestas cortas, claras y accionables

Reglas de comportamiento:
1. Primero intenta identificar al cliente por telefono con la herramienta disponible.
2. Si no existe, pide nombre y apellido de forma breve.
3. Si el cliente ya comparte su nombre, usa la herramienta de crear cliente.
4. Si preguntan por precios generales, usa la herramienta de precios.
5. Si preguntan por planes de un servicio especifico, usa la herramienta de planes por servicio.
6. Si preguntan por combos, catalogo completo o productos disponibles, usa la herramienta de catalogo.
7. Si el cliente quiere explorar mas opciones, puedes mencionar que el catalogo completo esta en https://streamify.aaronsoft.es.
8. No inventes precios, planes, bancos ni disponibilidad.
9. Si falta informacion para avanzar, pide solo lo minimo.
10. Si el cliente pide humano o el caso es sensible/conflictivo, escalar a handoff.

Herramientas con las que cuentas:
- buscar cliente por telefono
- crear cliente rapido
- consultar precios generales
- consultar planes de un servicio
- consultar catalogo completo
- consultar metodos de pago
- consultar banco por nombre
- handoff a humano

Devuelve solo JSON con este formato:
{
  "subagente_codigo": "asistente_no_registrado",
  "reply_text": "texto final para cliente",
  "accion_tipo": "ninguna|identificar_cliente|crear_cliente|enviar_precios|enviar_planes_servicio|enviar_catalogo|enviar_metodos_pago|handoff",
  "accion_requerida": false,
  "accion_payload": null,
  "escalar_humano": false,
  "motivo_humano": null,
  "confianza": 0
}
```

## Herramientas n8n recomendadas para este subagente

1. `GET /api/v2/chat/assistant/cliente?telefono=...`
2. `POST /api/v2/chat/assistant/cliente/create`
3. `GET /api/v2/precios`
4. `GET /api/v2/precios/servicio/{servicio}`
5. `GET /api/v2/catalogo`
6. `GET /api/v2/metodos-pago`
7. `GET /api/v2/banco/{nombrebanco}`
8. `POST /api/v2/chat/router/handoff`

# Flujo de ahora
{
  "nodes": [
    {
      "parameters": {
        "amount": 2
      },
      "type": "n8n-nodes-base.wait",
      "typeVersion": 1.1,
      "position": [
        208,
        -1024
      ],
      "id": "0153b570-512a-464a-a5a2-43ef4ff08485",
      "name": "Wait5",
      "webhookId": "6516dd11-1b82-4935-999d-13a1354c57f2"
    },
    {
      "parameters": {
        "conditions": {
          "options": {
            "caseSensitive": true,
            "leftValue": "",
            "typeValidation": "strict",
            "version": 3
          },
          "conditions": [
            {
              "id": "63e391d1-bf5c-461e-be7b-39ee87653eb1",
              "leftValue": "={{ $json.data.debe_responder }}",
              "rightValue": "",
              "operator": {
                "type": "boolean",
                "operation": "true",
                "singleValue": true
              }
            }
          ],
          "combinator": "and"
        },
        "options": {}
      },
      "type": "n8n-nodes-base.if",
      "typeVersion": 2.3,
      "position": [
        432,
        -1024
      ],
      "id": "3f0099bc-e300-4df9-9e57-0a8fad40d2e6",
      "name": "If4"
    },
    {
      "parameters": {
        "options": {}
      },
      "type": "@n8n/n8n-nodes-langchain.lmChatDeepSeek",
      "typeVersion": 1,
      "position": [
        544,
        -880
      ],
      "id": "b131259b-b168-4eef-be83-af09e6803166",
      "name": "DeepSeek1",
      "credentials": {
        "deepSeekApi": {
          "id": "miOzEyelkvZ6G51F",
          "name": "DeepSeek account 2"
        }
      }
    },
    {
      "parameters": {
        "method": "POST",
        "url": "https://streamify.aaronsoft.es/public/api/v2/chat/router/context",
        "sendBody": true,
        "bodyParameters": {
          "parameters": [
            {
              "name": "=idconv",
              "value": "={{ $json.data.idconv }}"
            },
            {
              "name": "trigger_idmsg",
              "value": "={{ $json.data.idmsg }}"
            },
            {
              "name": "historial_limite",
              "value": "10"
            },
            {
              "name": "memoria_limite",
              "value": "8"
            }
          ]
        },
        "options": {}
      },
      "type": "n8n-nodes-base.httpRequest",
      "typeVersion": 4.4,
      "position": [
        320,
        -1024
      ],
      "id": "dfb00f37-c886-4a74-82de-7a27e0f550a2",
      "name": "get context"
    },
    {
      "parameters": {
        "promptType": "define",
        "text": "=Clasifica este turno.\n\nmensaje_agrupado:\n{{ $json.data.mensaje_agrupado }}\n\ncontacto:\n{{ JSON.stringify($json.data.contacto) }}\n\nconversacion:\n{{ JSON.stringify($json.data.conversacion) }}\n\nhistorial_reciente:\n{{ JSON.stringify($json.data.historial_reciente) }}\n\nmemorias_contacto:\n{{ JSON.stringify($json.data.memorias_contacto) }}\n\nresumenes:\n{{ JSON.stringify($json.data.resumenes) }}\n\nDevuelve solo el JSON de clasificación.",
        "options": {
          "systemMessage": "=Eres el router conversacional de Streamify.\n\nTu única función es clasificar el turno actual del cliente y elegir un solo subagente.\n\nNo respondas al cliente.\nNo vendas.\nNo des soporte.\nNo valides pagos.\nSolo clasifica.\n\nSubagentes válidos:\n- espera_humano\n- asistente_no_registrado\n- vendedor_cierre\n- soporte_cliente\n- cobranzas_pago\n- postventa_reciente\n\nPrioridad:\n1. humano -> espera_humano\n2. pago/comprobante -> cobranzas_pago\n3. falla/acceso/error -> soporte_cliente\n4. precio/plan/compra -> vendedor_cierre\n5. seguimiento de compra reciente -> postventa_reciente\n6. si no hay match fuerte -> asistente_no_registrado\n\nSi detectas conflicto o pedido explícito de humano, marca requiere_humano=true.\nSi eliges espera_humano, marca silencio_bot=true.\n\nDevuelve solo JSON válido con:\n{\n  \"subagente_codigo\": \"...\",\n  \"motivo\": \"...\",\n  \"requiere_humano\": true or false,\n  \"silencio_bot\": true or false,\n  \"confianza\": 0-100\n}"
        }
      },
      "type": "@n8n/n8n-nodes-langchain.agent",
      "typeVersion": 3.1,
      "position": [
        544,
        -1024
      ],
      "id": "ff69aaed-430f-4485-8724-a4f2eb6f3c23",
      "name": "Clasificador"
    },
    {
      "parameters": {
        "jsCode": "// ==========================\n// 🔹 1. OBTENER OUTPUT\n// ==========================\nlet raw = $json.output;\n\n// ==========================\n// 🔹 2. LIMPIAR TEXTO\n// ==========================\nraw = raw\n  .replace(/```json/g, '')\n  .replace(/```/g, '')\n  .trim();\n\n// ==========================\n// 🔹 3. PARSEAR JSON\n// ==========================\nlet parsed;\n\ntry {\n  parsed = JSON.parse(raw);\n} catch (e) {\n  parsed = {};\n}\n\n// ==========================\n// 🔹 4. DEVOLVER LIMPIO\n// ==========================\nreturn [\n  {\n    json: parsed\n  }\n];"
      },
      "type": "n8n-nodes-base.code",
      "typeVersion": 2,
      "position": [
        784,
        -1024
      ],
      "id": "eeab7a98-238d-4b87-9f66-c7e445d164ab",
      "name": "Parsear2"
    },
    {
      "parameters": {
        "conditions": {
          "options": {
            "caseSensitive": true,
            "leftValue": "",
            "typeValidation": "strict",
            "version": 3
          },
          "conditions": [
            {
              "id": "ec86d83e-646c-4d98-b58d-cf09cdc6232c",
              "leftValue": "={{ $json.requiere_humano }}",
              "rightValue": false,
              "operator": {
                "type": "boolean",
                "operation": "true",
                "singleValue": true
              }
            },
            {
              "id": "1d83e85c-bf98-4436-a3cd-e7e2d4921a34",
              "leftValue": "={{ $json.silencio_bot }}",
              "rightValue": false,
              "operator": {
                "type": "boolean",
                "operation": "true",
                "singleValue": true
              }
            },
            {
              "id": "93c5555a-535b-4766-a159-a4a673e6d51a",
              "leftValue": "={{ $json.subagente_codigo }}",
              "rightValue": "espera_humano",
              "operator": {
                "type": "string",
                "operation": "equals"
              }
            }
          ],
          "combinator": "or"
        },
        "options": {}
      },
      "type": "n8n-nodes-base.if",
      "typeVersion": 2.3,
      "position": [
        896,
        -1024
      ],
      "id": "4c2cac3f-8746-455e-83e3-99698416233a",
      "name": "If5"
    },
    {
      "parameters": {
        "method": "POST",
        "url": "https://streamify.aaronsoft.es/public/api/v2/chat/router/handoff",
        "sendBody": true,
        "bodyParameters": {
          "parameters": [
            {
              "name": "idconv",
              "value": "={{ $('get context').item.json.data.idconv }}"
            },
            {
              "name": "razon",
              "value": "={{ $('Parsear2').item.json.motivo }}"
            },
            {
              "name": "subagente_codigo",
              "value": "={{ $('Parsear2').item.json.subagente_codigo }}"
            }
          ]
        },
        "options": {}
      },
      "type": "n8n-nodes-base.httpRequest",
      "typeVersion": 4.4,
      "position": [
        1104,
        -1088
      ],
      "id": "aa789f0c-2688-4acc-b01e-ecb01a110072",
      "name": "Handoff"
    },
    {
      "parameters": {
        "rules": {
          "values": [
            {
              "conditions": {
                "options": {
                  "caseSensitive": true,
                  "leftValue": "",
                  "typeValidation": "strict",
                  "version": 3
                },
                "conditions": [
                  {
                    "leftValue": "={{ $json.subagente_codigo }}",
                    "rightValue": "asistente_no_registrado",
                    "operator": {
                      "type": "string",
                      "operation": "equals"
                    },
                    "id": "c0b1e8b2-f9b4-445b-b41e-abb0218782d1"
                  }
                ],
                "combinator": "and"
              },
              "renameOutput": true,
              "outputKey": "asistente"
            },
            {
              "conditions": {
                "options": {
                  "caseSensitive": true,
                  "leftValue": "",
                  "typeValidation": "strict",
                  "version": 3
                },
                "conditions": [
                  {
                    "id": "ad2191ce-feb3-4003-92a8-dc507e9c1978",
                    "leftValue": "={{ $json.subagente_codigo }}",
                    "rightValue": "vendedor_cierre",
                    "operator": {
                      "type": "string",
                      "operation": "equals"
                    }
                  }
                ],
                "combinator": "and"
              },
              "renameOutput": true,
              "outputKey": "vendedor"
            },
            {
              "conditions": {
                "options": {
                  "caseSensitive": true,
                  "leftValue": "",
                  "typeValidation": "strict",
                  "version": 3
                },
                "conditions": [
                  {
                    "id": "976161c5-5389-486a-ac87-8d14d3cf0549",
                    "leftValue": "={{ $json.subagente_codigo }}",
                    "rightValue": "soporte_cliente",
                    "operator": {
                      "type": "string",
                      "operation": "equals",
                      "name": "filter.operator.equals"
                    }
                  }
                ],
                "combinator": "and"
              },
              "renameOutput": true,
              "outputKey": "soporte"
            },
            {
              "conditions": {
                "options": {
                  "caseSensitive": true,
                  "leftValue": "",
                  "typeValidation": "strict",
                  "version": 3
                },
                "conditions": [
                  {
                    "id": "2a2af1bf-06a4-488d-97d8-a35cf0b8d7af",
                    "leftValue": "={{ $json.subagente_codigo }}",
                    "rightValue": "cobranzas_pago",
                    "operator": {
                      "type": "string",
                      "operation": "equals",
                      "name": "filter.operator.equals"
                    }
                  }
                ],
                "combinator": "and"
              },
              "renameOutput": true,
              "outputKey": "cobranzas"
            },
            {
              "conditions": {
                "options": {
                  "caseSensitive": true,
                  "leftValue": "",
                  "typeValidation": "strict",
                  "version": 3
                },
                "conditions": [
                  {
                    "id": "cb2f8955-dbd9-415b-9aec-039e30306f6f",
                    "leftValue": "={{ $json.subagente_codigo }}",
                    "rightValue": "postventa_reciente",
                    "operator": {
                      "type": "string",
                      "operation": "equals",
                      "name": "filter.operator.equals"
                    }
                  }
                ],
                "combinator": "and"
              },
              "renameOutput": true,
              "outputKey": "postventa"
            }
          ]
        },
        "options": {}
      },
      "type": "n8n-nodes-base.switch",
      "typeVersion": 3.4,
      "position": [
        1104,
        -928
      ],
      "id": "c69c3d48-8dfa-4291-bb7b-622398b4a78e",
      "name": "Subagente"
    },
    {
      "parameters": {
        "promptType": "define",
        "text": "=Atiende este lead con contexto actual y devuelve JSON.\n\nmensaje_agrupado: {{ $('get context').item.json.data.mensaje_agrupado }}\nhistorial: {{ JSON.stringify($('get context').item.json.data.historial_reciente) }}",
        "options": {
          "systemMessage": "Eres asistente comercial para leads nuevos.\nResponde breve y claro.\nNo inventes precios.\nSi faltan datos para accionar, pide solo lo minimo.\nDevuelve solo JSON con el contrato definido.\nEl json formato es: \n{\n  \"subagente_codigo\": \"vendedor_cierre\",\n  \"reply_text\": \"texto final para cliente\",\n  \"accion_tipo\": \"ninguna|crear_venta|registrar_incidencia|enviar_metodos_pago|receipt_checkout|verificar_cuenta\",\n  \"accion_requerida\": false,\n  \"accion_payload\": null,\n  \"escalar_humano\": false,\n  \"motivo_humano\": null,\n  \"confianza\": 0\n}"
        }
      },
      "type": "@n8n/n8n-nodes-langchain.agent",
      "typeVersion": 3.1,
      "position": [
        1504,
        -1584
      ],
      "id": "d7de36d0-fb8a-4185-8b36-efced0221758",
      "name": "Asistente no registrado"
    },
    {
      "parameters": {
        "options": {}
      },
      "type": "@n8n/n8n-nodes-langchain.lmChatDeepSeek",
      "typeVersion": 1,
      "position": [
        1504,
        -1472
      ],
      "id": "08568eb0-2ca4-4267-a805-eb9828906eae",
      "name": "DeepSeek2",
      "credentials": {
        "deepSeekApi": {
          "id": "miOzEyelkvZ6G51F",
          "name": "DeepSeek account 2"
        }
      }
    },
    {
      "parameters": {
        "toolDescription": "Consulta precios de productos para que puedas informar al cliente.",
        "url": "https://streamify.aaronsoft.es/public/api/v2/precios",
        "options": {}
      },
      "type": "n8n-nodes-base.httpRequestTool",
      "typeVersion": 4.4,
      "position": [
        1584,
        -1392
      ],
      "id": "a99655de-3c44-4028-b50e-dfcf901cf2e2",
      "name": "get precios"
    },
    {
      "parameters": {
        "toolDescription": "Consulta precios de productos para que puedas informar al cliente.",
        "url": "https://streamify.aaronsoft.es/public/api/v2/metodos-pago",
        "options": {}
      },
      "type": "n8n-nodes-base.httpRequestTool",
      "typeVersion": 4.4,
      "position": [
        1712,
        -1392
      ],
      "id": "98a8663f-8235-40e9-b7e4-0cf37dbfd22a",
      "name": "get metodos-pago"
    },
    {
      "parameters": {
        "content": "## Asistente\n1. identifica cliente, sino lo registra preguntandole \n2. da información de negocio, responde a nuevos clientes\n3. informa de precios, productos, sitio web, seguridad\n4. responde a preguntas frecuentes",
        "height": 256,
        "color": 6
      },
      "type": "n8n-nodes-base.stickyNote",
      "position": [
        1904,
        -1584
      ],
      "typeVersion": 1,
      "id": "1b5dd465-4dbb-487f-8f41-5f617c7ea290",
      "name": "Sticky Note4"
    }
  ],
  "connections": {
    "Wait5": {
      "main": [
        [
          {
            "node": "get context",
            "type": "main",
            "index": 0
          }
        ]
      ]
    },
    "If4": {
      "main": [
        [
          {
            "node": "Clasificador",
            "type": "main",
            "index": 0
          }
        ]
      ]
    },
    "DeepSeek1": {
      "ai_languageModel": [
        [
          {
            "node": "Clasificador",
            "type": "ai_languageModel",
            "index": 0
          }
        ]
      ]
    },
    "get context": {
      "main": [
        [
          {
            "node": "If4",
            "type": "main",
            "index": 0
          }
        ]
      ]
    },
    "Clasificador": {
      "main": [
        [
          {
            "node": "Parsear2",
            "type": "main",
            "index": 0
          }
        ]
      ]
    },
    "Parsear2": {
      "main": [
        [
          {
            "node": "If5",
            "type": "main",
            "index": 0
          }
        ]
      ]
    },
    "If5": {
      "main": [
        [
          {
            "node": "Handoff",
            "type": "main",
            "index": 0
          }
        ],
        [
          {
            "node": "Subagente",
            "type": "main",
            "index": 0
          }
        ]
      ]
    },
    "Subagente": {
      "main": [
        [
          {
            "node": "Asistente no registrado",
            "type": "main",
            "index": 0
          }
        ],
        [],
        []
      ]
    },
    "Asistente no registrado": {
      "main": [
        []
      ]
    },
    "DeepSeek2": {
      "ai_languageModel": [
        [
          {
            "node": "Asistente no registrado",
            "type": "ai_languageModel",
            "index": 0
          }
        ]
      ]
    },
    "get precios": {
      "ai_tool": [
        [
          {
            "node": "Asistente no registrado",
            "type": "ai_tool",
            "index": 0
          }
        ]
      ]
    },
    "get metodos-pago": {
      "ai_tool": [
        [
          {
            "node": "Asistente no registrado",
            "type": "ai_tool",
            "index": 0
          }
        ]
      ]
    }
  },
  "pinData": {},
  "meta": {
    "templateCredsSetupCompleted": true,
    "instanceId": "2a4787fedcd3a9fda6d63f2231359e551e48f7e0d6a6b433946467fe82f7e7a4"
  }
}
