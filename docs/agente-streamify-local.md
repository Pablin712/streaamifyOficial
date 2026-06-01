# Fragmento clave del flujo del agente de whatsapp de streamify
## Descripción general
Espera a que el cliente termine de escribir, para soportar múltiples mensajes y responder a uno solo.
*Mensaje 1 del cliente*
Hola
*Mensaje 2 del cliente*
¿Cómo estás?
*Mensaje 3 del cliente*
¿Puedes ayudarme con mi pedido?
*Respuesta del agente*
¡Hola! Estoy bien, gracias por preguntar. Claro, estaré encantado de ayudarte con tu pedido. ¿Podrías proporcionarme más detalles sobre tu pedido para que pueda asistirte mejor?

## Objetivo
El objetivo de este fragmento es demostrar cómo el agente de WhatsApp de Streamify espera a que el cliente termine de escribir antes de responder, lo que permite manejar múltiples mensajes del cliente y responder a uno solo, proporcionando una experiencia de usuario más fluida y eficiente. Esto evita que el agente genere respuestas para cada uno de los mensajes, muchas veces innecesaria.

## Implementación
{
  "nodes": [
    {
      "parameters": {
        "amount": 10
      },
      "type": "n8n-nodes-base.wait",
      "typeVersion": 1.1,
      "position": [
        304,
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
        640,
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
        752,
        -912
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
        "specifyBody": "json",
        "jsonBody": "={{ $json }}",
        "options": {}
      },
      "type": "n8n-nodes-base.httpRequest",
      "typeVersion": 4.4,
      "position": [
        528,
        -1024
      ],
      "id": "dfb00f37-c886-4a74-82de-7a27e0f550a2",
      "name": "get context"
    },
    {
      "parameters": {
        "promptType": "define",
        "text": "=MENSAJE: {{ $json.data.mensaje_agrupado }}\nCONTACTO: {{ JSON.stringify($json.data.contacto) }}\nHISTORIAL: {{ JSON.stringify($json.data.historial_reciente) }}\nCONVERSACIÓN: {{ JSON.stringify($json.data.conversacion) }}\n\nClasifica el turno. Devuelve solo JSON.",
        "options": {
          "systemMessage": "=Eres el router de Streamify. Solo clasificas. No respondes al cliente.\n\nPRIORIDAD (de mayor a menor):\n1. Pide humano/asesor/persona real → espera_humano (silencio_bot=true, requiere_humano=true)\n2. Pago/comprobante/transferencia/recarga/ya pagué → cobranzas_pago\n3. No entra/error/contraseña/pantalla/no funciona/cuenta → soporte_cliente\n4. Precio/planes/quiero comprar/cuánto cuesta/combos → vendedor_cierre\n5. Compró hace menos de 3 días + duda de entrega o seguimiento → postventa_reciente\n6. Cualquier otro caso o lead nuevo → asistente_no_registrado\n\nREGLA DE CONTEXTO:\n- Si el historial muestra que ya está en un subagente y el mensaje es continuación natural, mantén ese subagente.\n- Si hay cambio claro de tema, reclasifica.\n\nDevuelve SOLO este JSON, sin texto antes ni después:\n{\n  \"subagente_codigo\": \"espera_humano|asistente_no_registrado|vendedor_cierre|soporte_cliente|cobranzas_pago|postventa_reciente\",\n  \"motivo\": \"frase corta en 5 palabras máx\",\n  \"requiere_humano\": false,\n  \"silencio_bot\": false,\n  \"confianza\": 90\n}"
        }
      },
      "type": "@n8n/n8n-nodes-langchain.agent",
      "typeVersion": 3.1,
      "position": [
        752,
        -1024
      ],
      "id": "ff69aaed-430f-4485-8724-a4f2eb6f3c23",
      "name": "Clasificador"
    },
    {
      "parameters": {
        "jsCode": "// ==========================\n// 🔹 1. OBTENER OUTPUT\n// ==========================\nlet raw = $json.output;\n\n// ==========================\n// 🔹 2. LIMPIAR TEXTO\n// ==========================\nraw = raw\n  .replace(/```json/g, '')\n  .replace(/```/g, '')\n  .trim();\n\n// ==========================\n// 🔹 3. PARSEAR JSON\n// ==========================\nlet parsed;\n\ntry {\n  parsed = JSON.parse(raw);\n} catch (e) {\n  parsed = {};\n}\n\n// ==========================\n// 🔹 4. TRAER DATOS DE MERGE\n// ==========================\nconst merge = $('Merge').first().json;\n\n// ==========================\n// 🔹 5. DEVOLVER CORRECTO\n// ==========================\nreturn [\n  {\n    json: {\n      ...parsed,\n\n      content: merge.content,\n\n      instance: {\n        name: merge?.instance?.name,\n        apikey: merge.instance.apikey,\n      },\n      context: {\n        contacto: $('get context').first().json.data.contacto,\n        cliente: $('get context').first().json.data.contacto.cliente,\n        conversacion: $('get context').first().json.data.conversacion,\n        trigger_idmsg: $('get context').first().json.data.trigger_idmsg,\n        ultimo_pendiente_idmsg: $('get context').first().json.data.ultimo_pendiente_idmsg,\n        mensajes_pendientes: $('get context').first().json.data.mensajes_pendientes,\n        mensaje_agrupado: $('get context').first().json.data.mensaje_agrupado,\n        historial_reciente: $('get context').first().json.data.historial_reciente,\n        memorias_contacto: $('get context').first().json.data.memorias_contacto,\n        resumenes: $('get context').first().json.data.resumenes,\n        memoria_negocio: $('get context').first().json.data.memoria_negocio,\n        subagentes: $('get context').first().json.data.subagentes\n      },\n\n      external_thread_id: merge.external_thread_id\n    }\n  }\n];"
      },
      "type": "n8n-nodes-base.code",
      "typeVersion": 2,
      "position": [
        992,
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
        1104,
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
        1248,
        -1104
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
        1360,
        -1056
      ],
      "id": "c69c3d48-8dfa-4291-bb7b-622398b4a78e",
      "name": "Subagente"
    },
    {
      "parameters": {
        "content": "# Agente de WhatsApp",
        "height": 352,
        "width": 1328,
        "color": 6
      },
      "type": "n8n-nodes-base.stickyNote",
      "position": [
        160,
        -1120
      ],
      "typeVersion": 1,
      "id": "71c21005-6b59-4377-9529-b44d5b36b5c1",
      "name": "Sticky Note"
    },
    {
      "parameters": {
        "jsCode": "// Toma el item actual y, si existe, el item de Normalizar\nconst current = $input.first()?.json ?? {};\nconst normalizar = $('Normalizar').first()?.json ?? {};\n\n// helpers\nconst clean = (v) => {\n  if (v === undefined || v === null) return undefined;\n  const s = String(v).trim();\n  return s === '' ? undefined : s;\n};\n\nconst asIntIfNumeric = (v) => {\n  if (v === undefined || v === null) return undefined;\n  const s = String(v).trim();\n  if (!/^\\d+$/.test(s)) return undefined;\n  return Number(s);\n};\n\n// prioridad de fuentes (Normalizar -> item actual)\nconst canalUserId =\n  clean(normalizar?.contact?.numero) ||\n  clean(normalizar?.message?.from) ||\n  clean(current?.contact?.numero) ||\n  clean(current?.message?.from);\n\nconst payload = {\n  canal: 'whatsapp',\n  canal_user_id: canalUserId,\n  historial_limite: 10,\n  memoria_limite: 8,\n};\n\n// idconv opcional, si lo tienes en alguna rama\nconst idconv =\n  clean(current?.data?.idconv) ||\n  clean(current?.idconv) ||\n  clean(normalizar?.chat?.idconv) ||\n  clean(normalizar?.idconv);\n\nif (idconv) payload.idconv = idconv;\n\n// trigger_idmsg solo si es id interno numérico\nconst triggerIdMsg =\n  asIntIfNumeric(current?.data?.idmsg) ??\n  asIntIfNumeric(current?.trigger_idmsg) ??\n  asIntIfNumeric(normalizar?.chat?.idmsg);\n\nif (triggerIdMsg !== undefined) payload.trigger_idmsg = triggerIdMsg;\n\n// external_message_id sí puede ser el id externo de WhatsApp\nconst externalMessageId =\n  clean(normalizar?.message?.id) ||\n  clean(current?.message?.id) ||\n  clean(current?.external_message_id);\n\nif (externalMessageId) payload.external_message_id = externalMessageId;\n\n// si no hay nada para resolver conversación, corta limpio\nif (!payload.canal_user_id && !payload.idconv && !payload.external_message_id) {\n  return [\n    {\n      json: {\n        _skip_context: true,\n        reason: 'Sin identificadores para contexto',\n        debug: {\n          from_normalizar_numero: normalizar?.contact?.numero ?? null,\n          from_normalizar_message_from: normalizar?.message?.from ?? null,\n          from_current_data_idconv: current?.data?.idconv ?? null,\n          from_normalizar_message_id: normalizar?.message?.id ?? null,\n        }\n      }\n    }\n  ];\n}\n\nreturn [{ json: payload }];"
      },
      "type": "n8n-nodes-base.code",
      "typeVersion": 2,
      "position": [
        416,
        -1024
      ],
      "id": "60dcab3c-7ec7-425b-9d6f-5f47e8fae368",
      "name": "payload"
    },
    {
      "parameters": {
        "assignments": {
          "assignments": [
            {
              "id": "673f9fa4-98ee-4750-ace4-b1d21784d4a2",
              "name": "instance.name",
              "value": "={{ $('Normalizar').item.json.instance.name }}",
              "type": "string"
            },
            {
              "id": "994e50ca-7ef9-4915-8046-26d5c339e311",
              "name": "external_thread_id",
              "value": "={{ $('Normalizar').item.json.canal_user_id }}@s.whatsapp.net",
              "type": "string"
            },
            {
              "id": "c8a684f4-5e02-4f10-ab76-3d0cbbf06b34",
              "name": "instance.apikey",
              "value": "={{ $('Normalizar').item.json.instance.apikey }}",
              "type": "string"
            }
          ]
        },
        "options": {}
      },
      "type": "n8n-nodes-base.set",
      "typeVersion": 3.4,
      "position": [
        192,
        -1024
      ],
      "id": "2399e92d-90c3-47e9-955e-4bfd552fc632",
      "name": "Merge"
    }
  ],
  "connections": {
    "Wait5": {
      "main": [
        [
          {
            "node": "payload",
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
        [],
        [],
        [],
        [],
        []
      ]
    },
    "payload": {
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
    "Merge": {
      "main": [
        [
          {
            "node": "Wait5",
            "type": "main",
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
