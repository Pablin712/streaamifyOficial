# Donna personal Pablo
{
  "nodes": [
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
                    "leftValue": "={{ $json.message.voice.file_id }}",
                    "rightValue": "",
                    "operator": {
                      "type": "string",
                      "operation": "exists",
                      "singleValue": true
                    },
                    "id": "f3462016-5322-438d-8653-fe03cb9da324"
                  }
                ],
                "combinator": "and"
              },
              "renameOutput": true,
              "outputKey": "audio"
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
                    "id": "33ac9ae7-6dfa-400f-aabe-a5cdd214d502",
                    "leftValue": "={{ $json.message.text }}",
                    "rightValue": "",
                    "operator": {
                      "type": "string",
                      "operation": "exists",
                      "singleValue": true
                    }
                  }
                ],
                "combinator": "and"
              },
              "renameOutput": true,
              "outputKey": "texto"
            }
          ]
        },
        "options": {}
      },
      "type": "n8n-nodes-base.switch",
      "typeVersion": 3.4,
      "position": [
        0,
        -224
      ],
      "id": "a70eb733-9310-4873-b88c-17fff7007150",
      "name": "Switch"
    },
    {
      "parameters": {
        "resource": "file",
        "fileId": "={{ $json.message.voice.file_id }}",
        "additionalFields": {}
      },
      "type": "n8n-nodes-base.telegram",
      "typeVersion": 1.2,
      "position": [
        208,
        -336
      ],
      "id": "5b85849a-e479-400e-82d3-f1526e934a58",
      "name": "Get a file",
      "webhookId": "48747147-6684-4b3e-9620-c01535a9de51",
      "credentials": {
        "telegramApi": {
          "id": "zkPl4w5XYMD8k3Kk",
          "name": "Google calendar agent"
        }
      }
    },
    {
      "parameters": {
        "resource": "audio",
        "operation": "transcribe",
        "options": {}
      },
      "type": "@n8n/n8n-nodes-langchain.openAi",
      "typeVersion": 2.1,
      "position": [
        416,
        -336
      ],
      "id": "d41443ba-89a0-431e-8bf7-2f3a840b6f76",
      "name": "Transcribe a recording",
      "credentials": {
        "openAiApi": {
          "id": "dBPlamBZ6NHPVTxU",
          "name": "OpenAi Pablin"
        }
      }
    },
    {
      "parameters": {
        "promptType": "define",
        "text": "={{ $json.text }}",
        "options": {
          "systemMessage": "=Eres una secretaria inteligente que gestiona calendario y tareas.\n\nFecha actual: {{ $now }}\nZona horaria: America/Guayaquil\n\nFUNCIONES:\n- Agendar, editar, eliminar y consultar eventos\n- Organizar automáticamente el día del usuario\n- Gestionar tareas desde Google Sheets\n\nREGLAS GENERALES:\n- Sé breve, claro y directo\n- Usa herramientas solo cuando sea necesario\n- No repitas herramientas innecesariamente\n- Máximo 6 acciones por ejecución\n- Si no puedes completar algo, responde al usuario\n\nREGLAS DE AGENDA:\n- Horario permitido: 09:00 AM a 08:00 PM\n- Nunca usar 01:00 PM (almuerzo)\n- No solapar eventos\n- No modificar eventos existentes a menos que el usuario lo pida\n\nREGLAS DE ORGANIZACIÓN AUTOMÁTICA:\n- Consultar calendario antes de agendar\n- Consultar tareas antes de planificar\n- Ignorar tareas completadas\n- Priorizar por fecha límite más cercana\n- Usar \"horas al día\" como tiempo a agendar\n- Agrupar en bloques continuos si es posible\n\nREGLAS ESPECIALES:\n- Si no hay evento \"Gimnasio\", crear uno de 2 horas\n- No crear eventos sin título\n- Si falta hora fin, usar 1 hora por defecto\n\nFORMATO DE RESPUESTA:\n- Resumen claro de acciones realizadas\n- Indicar horarios y tareas programadas"
        }
      },
      "type": "@n8n/n8n-nodes-langchain.agent",
      "typeVersion": 3.1,
      "position": [
        624,
        -336
      ],
      "id": "96fd3eb2-934f-4354-8b5e-671ad1953d2f",
      "name": "AI Agent",
      "alwaysOutputData": true,
      "onError": "continueErrorOutput"
    },
    {
      "parameters": {
        "assignments": {
          "assignments": [
            {
              "id": "139c6037-b0d3-47e6-9b9e-e4ffa3150dac",
              "name": "text",
              "value": "={{ $json.message.text }}",
              "type": "string"
            }
          ]
        },
        "options": {}
      },
      "type": "n8n-nodes-base.set",
      "typeVersion": 3.4,
      "position": [
        288,
        -160
      ],
      "id": "98eef802-0c30-442c-8879-daedba5f0ed6",
      "name": "Edit Fields"
    },
    {
      "parameters": {
        "options": {}
      },
      "type": "@n8n/n8n-nodes-langchain.lmChatDeepSeek",
      "typeVersion": 1,
      "position": [
        512,
        -128
      ],
      "id": "df665639-d88a-4666-a26a-d92300fe5c30",
      "name": "DeepSeek Chat Model",
      "credentials": {
        "deepSeekApi": {
          "id": "miOzEyelkvZ6G51F",
          "name": "DeepSeek account 2"
        }
      }
    },
    {
      "parameters": {
        "sessionIdType": "customKey",
        "sessionKey": "=6199654595"
      },
      "type": "@n8n/n8n-nodes-langchain.memoryBufferWindow",
      "typeVersion": 1.3,
      "position": [
        640,
        -128
      ],
      "id": "ae411503-1c02-477d-b9f6-19c5e823cb7f",
      "name": "Simple Memory"
    },
    {
      "parameters": {
        "chatId": "=6199654595",
        "text": "={{ $json.text }}",
        "additionalFields": {
          "parse_mode": "HTML"
        }
      },
      "id": "00a08c4a-c05c-4434-9b22-5ade496b660b",
      "name": "Enviar mensaje",
      "type": "n8n-nodes-base.telegram",
      "typeVersion": 1,
      "position": [
        1184,
        -336
      ],
      "webhookId": "4cfc3335-b30a-4e5e-8998-15008f862c18",
      "credentials": {
        "telegramApi": {
          "id": "zkPl4w5XYMD8k3Kk",
          "name": "Google calendar agent"
        }
      }
    },
    {
      "parameters": {
        "updates": [
          "message"
        ],
        "additionalFields": {}
      },
      "type": "n8n-nodes-base.telegramTrigger",
      "typeVersion": 1.2,
      "position": [
        -160,
        -224
      ],
      "id": "d8f68970-e7ff-4fd1-8de4-5fd753aa43ae",
      "name": "Telegram Trigger",
      "webhookId": "5b501f49-ff89-4021-9057-fcd4a920f317",
      "credentials": {
        "telegramApi": {
          "id": "zkPl4w5XYMD8k3Kk",
          "name": "Google calendar agent"
        }
      }
    },
    {
      "parameters": {
        "options": {
          "timezone": "America/Guayaquil"
        }
      },
      "type": "n8n-nodes-base.dateTimeTool",
      "typeVersion": 2,
      "position": [
        1168,
        -160
      ],
      "id": "5c38e7d1-602a-4d4b-b69e-cfcef5b96a7f",
      "name": "Date & Time"
    },
    {
      "parameters": {
        "descriptionType": "manual",
        "toolDescription": "Consulta eventos en Google Calendar dentro de un rango de fechas.\n\nUsa esta herramienta cuando necesites:\n- saber si el usuario está ocupado\n- verificar disponibilidad antes de agendar algo\n- revisar la agenda del día o de una fecha específica\n\nDevuelve la lista de eventos existentes para ayudar a decidir horarios disponibles.",
        "operation": "getAll",
        "calendar": {
          "__rl": true,
          "value": "pablojimenezelizalde@gmail.com",
          "mode": "list",
          "cachedResultName": "pablojimenezelizalde@gmail.com"
        },
        "timeMax": "={{ /*n8n-auto-generated-fromAI-override*/ $fromAI('Before', `Obten la semana actual, o hasta la otra semana, o hasta el final del mes según lo conveniente`, 'string') }}",
        "options": {}
      },
      "type": "n8n-nodes-base.googleCalendarTool",
      "typeVersion": 1.3,
      "position": [
        320,
        16
      ],
      "id": "d7ed01a9-ec43-4ee6-b27c-5ccf59f037b2",
      "name": "Get many events",
      "credentials": {
        "googleCalendarOAuth2Api": {
          "id": "3oKsEbHPo02BiXpR",
          "name": "Google Calendar Pablin"
        }
      }
    },
    {
      "parameters": {
        "descriptionType": "manual",
        "toolDescription": "Crea un evento en Google Calendar.\n\nUsa esta herramienta cuando el usuario quiera:\n- agendar una reunión\n- crear un recordatorio\n- programar una actividad\n- añadir un evento al calendario\n\nDebes proporcionar siempre:\n- titulo_evento (nombre del evento)\n- fecha_inicio \n- hora_inicio\n- fecha_fin\n- hora_fin\n\nOpcionalmente puedes incluir:\n- descripcion\n- ubicacion\n- invitados\n- evento_todo_el_dia\n- visibilidad\n- repeticion\n\nReglas importantes:\n- Nunca crees eventos sin titulo_evento.\n- Si el usuario no menciona hora_fin, asume una duración de 1 hora.\n- Si el usuario dice \"todo el día\", usa evento_todo_el_dia = true.\n- Si el usuario menciona repetición (diaria, semanal, mensual), configura el campo de repetición.\n- Usa descripciones claras para el evento.",
        "calendar": {
          "__rl": true,
          "value": "pablojimenezelizalde@gmail.com",
          "mode": "list",
          "cachedResultName": "pablojimenezelizalde@gmail.com"
        },
        "start": "={{ /*n8n-auto-generated-fromAI-override*/ $fromAI('Start', `agregas la fecha de inicio que indica el usuario, con timezone America/Guayaquil`, 'string') }}",
        "end": "={{ /*n8n-auto-generated-fromAI-override*/ $fromAI('End', `agregas la fecha fin que indica el usuario en el mensaje, con timezone America/Guayaquil`, 'string') }}",
        "additionalFields": {
          "color": "2",
          "description": "={{ /*n8n-auto-generated-fromAI-override*/ $fromAI('Description', `Descripción del evento (opcional)`, 'string') }}",
          "location": "={{ /*n8n-auto-generated-fromAI-override*/ $fromAI('Location', `Ubicación del evento (opcional)`, 'string') }}",
          "summary": "={{ /*n8n-auto-generated-fromAI-override*/ $fromAI('Summary', `Agregale un nombre adecuado al evento, adicional agregale un emoji de robot para que se vea que fue generado por AI`, 'string') }}"
        }
      },
      "type": "n8n-nodes-base.googleCalendarTool",
      "typeVersion": 1.3,
      "position": [
        448,
        16
      ],
      "id": "235bcb94-5930-459d-901d-0f4db3e4b85a",
      "name": "Create an event",
      "credentials": {
        "googleCalendarOAuth2Api": {
          "id": "3oKsEbHPo02BiXpR",
          "name": "Google Calendar Pablin"
        }
      }
    },
    {
      "parameters": {
        "descriptionType": "manual",
        "toolDescription": "Actualiza un evento existente en Google Calendar.\n\nUsa esta herramienta cuando el usuario quiera:\n- cambiar fecha\n- cambiar hora\n- cambiar título\n- modificar descripción",
        "operation": "update",
        "calendar": {
          "__rl": true,
          "value": "pablojimenezelizalde@gmail.com",
          "mode": "list",
          "cachedResultName": "pablojimenezelizalde@gmail.com"
        },
        "eventId": "={{ /*n8n-auto-generated-fromAI-override*/ $fromAI('Event_ID', ``, 'string') }}",
        "updateFields": {
          "color": "5",
          "description": "={{ /*n8n-auto-generated-fromAI-override*/ $fromAI('Description', ``, 'string') }}",
          "end": "={{ /*n8n-auto-generated-fromAI-override*/ $fromAI('End', ``, 'string') }}",
          "location": "={{ /*n8n-auto-generated-fromAI-override*/ $fromAI('Location', ``, 'string') }}",
          "start": "={{ /*n8n-auto-generated-fromAI-override*/ $fromAI('Start', ``, 'string') }}",
          "summary": "={{ /*n8n-auto-generated-fromAI-override*/ $fromAI('Summary', ``, 'string') }}"
        }
      },
      "type": "n8n-nodes-base.googleCalendarTool",
      "typeVersion": 1.3,
      "position": [
        592,
        16
      ],
      "id": "d6a52b93-3d49-46fd-9e45-7f6f8b8267fb",
      "name": "Update an event",
      "credentials": {
        "googleCalendarOAuth2Api": {
          "id": "3oKsEbHPo02BiXpR",
          "name": "Google Calendar Pablin"
        }
      }
    },
    {
      "parameters": {
        "descriptionType": "manual",
        "toolDescription": "Elimina un evento del calendario.\n\nUsa esta herramienta cuando el usuario quiera cancelar\no borrar una cita existente.",
        "operation": "delete",
        "calendar": {
          "__rl": true,
          "value": "pablojimenezelizalde@gmail.com",
          "mode": "list",
          "cachedResultName": "pablojimenezelizalde@gmail.com"
        },
        "eventId": "={{ /*n8n-auto-generated-fromAI-override*/ $fromAI('Event_ID', ``, 'string') }}",
        "options": {}
      },
      "type": "n8n-nodes-base.googleCalendarTool",
      "typeVersion": 1.3,
      "position": [
        736,
        16
      ],
      "id": "5b24103b-4fb2-452a-87a3-5dd5536150cb",
      "name": "Delete an event",
      "credentials": {
        "googleCalendarOAuth2Api": {
          "id": "3oKsEbHPo02BiXpR",
          "name": "Google Calendar Pablin"
        }
      }
    },
    {
      "parameters": {
        "jsCode": "let text = $json.output || $json.text || \"\";\n\n// convertir markdown bold a HTML\ntext = text.replace(/\\*\\*(.*?)\\*\\*/g, \"<b>$1</b>\");\n\n// convertir italic\ntext = text.replace(/\\*(.*?)\\*/g, \"<i>$1</i>\");\n\n// eliminar headers markdown\ntext = text.replace(/^#{1,6}\\s*/gm, \"\");\n\n// convertir listas markdown a guiones simples\ntext = text.replace(/^\\s*[-•]\\s+/gm, \"— \");\n\n// eliminar código markdown\ntext = text.replace(/`{1,3}([\\s\\S]*?)`{1,3}/g, \"$1\");\n\n// eliminar links markdown\ntext = text.replace(/\\[(.*?)\\]\\((.*?)\\)/g, \"$1\");\n\n// limpiar caracteres que rompen HTML\ntext = text\n.replace(/</g,\"&lt;\")\n.replace(/>/g,\"&gt;\");\n\n// volver a habilitar nuestras etiquetas\ntext = text\n.replace(/&lt;b&gt;/g,\"<b>\")\n.replace(/&lt;\\/b&gt;/g,\"</b>\")\n.replace(/&lt;i&gt;/g,\"<i>\")\n.replace(/&lt;\\/i&gt;/g,\"</i>\");\n\nreturn [{\n  text: text.trim()\n}];"
      },
      "type": "n8n-nodes-base.code",
      "typeVersion": 2,
      "position": [
        976,
        -432
      ],
      "id": "bebffa42-24d0-4c97-9eba-2a7e592102c8",
      "name": "Code in JavaScript"
    },
    {
      "parameters": {
        "assignments": {
          "assignments": [
            {
              "id": "ca1b257d-e21d-4960-9b05-6f359cd00ae3",
              "name": "text",
              "value": "Lo siento, no pude completar la solicitud.\\n¿Podrías reformular la pregunta?",
              "type": "string"
            }
          ]
        },
        "options": {}
      },
      "type": "n8n-nodes-base.set",
      "typeVersion": 3.4,
      "position": [
        976,
        -304
      ],
      "id": "89f110ea-d803-4df9-b121-d8193e109ccb",
      "name": "Edit Fields1"
    },
    {
      "parameters": {
        "rule": {
          "interval": [
            {
              "triggerAtHour": 7
            }
          ]
        }
      },
      "type": "n8n-nodes-base.scheduleTrigger",
      "typeVersion": 1.3,
      "position": [
        -160,
        -48
      ],
      "id": "3289a805-6c0a-4cc1-9f47-37d74fe541de",
      "name": "Schedule Trigger",
      "disabled": true
    },
    {
      "parameters": {
        "assignments": {
          "assignments": [
            {
              "id": "76b781ef-4527-42e3-b12b-9d330578c079",
              "name": "text",
              "value": "Organiza mi agenda de hoy automáticamente.",
              "type": "string"
            }
          ]
        },
        "options": {}
      },
      "type": "n8n-nodes-base.set",
      "typeVersion": 3.4,
      "position": [
        0,
        -48
      ],
      "id": "dbf78579-e49c-4628-9331-1357f1ee05dd",
      "name": "Edit Fields2"
    },
    {
      "parameters": {
        "descriptionType": "manual",
        "toolDescription": "Get row(s) in sheet in Google Sheets.\nReglas para usar las tareas:\n\nIgnora cualquier tarea donde visto = verdadero.\n\nAnaliza la fecha límite y prioriza las tareas con fechas más cercanas.\n\nUsa el valor de horas al día para saber cuánto tiempo agendar hoy.\n\nDivide el tiempo en bloques continuos cuando sea posible.\n\nTareas especiales:\n\nSi no existe hoy un evento llamado Gimnasio, debes crear uno de 2 horas.",
        "documentId": {
          "__rl": true,
          "value": "1GuAWsnkBXXBKHqPMSrfjDOikFwhXgRPDUdzV0dlNbbc",
          "mode": "list",
          "cachedResultName": "Lista de tareas",
          "cachedResultUrl": "https://docs.google.com/spreadsheets/d/1GuAWsnkBXXBKHqPMSrfjDOikFwhXgRPDUdzV0dlNbbc/edit?usp=drivesdk"
        },
        "sheetName": {
          "__rl": true,
          "value": 1386834576,
          "mode": "list",
          "cachedResultName": "Tareas",
          "cachedResultUrl": "https://docs.google.com/spreadsheets/d/1GuAWsnkBXXBKHqPMSrfjDOikFwhXgRPDUdzV0dlNbbc/edit#gid=1386834576"
        },
        "options": {}
      },
      "type": "n8n-nodes-base.googleSheetsTool",
      "typeVersion": 4.7,
      "position": [
        880,
        16
      ],
      "id": "d3078d35-3f09-4427-a3ca-c75a7b34b757",
      "name": "Get Tareas",
      "credentials": {
        "googleSheetsOAuth2Api": {
          "id": "6WEu40HQCLCgukVJ",
          "name": "Google Sheets Pablin"
        }
      }
    },
    {
      "parameters": {
        "descriptionType": "manual",
        "toolDescription": "Crea una nueva tarea en la hoja de cálculo \"lista de tareas\".\n\nUsa esta herramienta cuando necesites agregar una nueva tarea pendiente.\n\nCampos requeridos:\n- tarea: descripción clara de la tarea\n- fecha: fecha límite en formato YYYY-MM-DD\n- horas_dia: número de horas estimadas por día\n\nReglas:\n- Siempre marcar \"visto\" como false (no completado)\n- No duplicar tareas existentes\n- La tarea debe ser clara y accionable",
        "operation": "append",
        "documentId": {
          "__rl": true,
          "value": "1GuAWsnkBXXBKHqPMSrfjDOikFwhXgRPDUdzV0dlNbbc",
          "mode": "list",
          "cachedResultName": "Lista de tareas",
          "cachedResultUrl": "https://docs.google.com/spreadsheets/d/1GuAWsnkBXXBKHqPMSrfjDOikFwhXgRPDUdzV0dlNbbc/edit?usp=drivesdk"
        },
        "sheetName": {
          "__rl": true,
          "value": 1386834576,
          "mode": "list",
          "cachedResultName": "Tareas",
          "cachedResultUrl": "https://docs.google.com/spreadsheets/d/1GuAWsnkBXXBKHqPMSrfjDOikFwhXgRPDUdzV0dlNbbc/edit#gid=1386834576"
        },
        "columns": {
          "mappingMode": "defineBelow",
          "value": {
            "Tarea": "={{ /*n8n-auto-generated-fromAI-override*/ $fromAI('Tarea__using_to_match_', `Nombre de la tarea, descripción de la tarea`, 'string') }}",
            "✓": "={{ /*n8n-auto-generated-fromAI-override*/ $fromAI('_', `Completado o no (marcar o desmarcar)`, 'string') }}",
            "Fecha": "={{ /*n8n-auto-generated-fromAI-override*/ $fromAI('Fecha', `Fecha de vencimiento de la tarea`, 'string') }}",
            "Horas al día": "={{ /*n8n-auto-generated-fromAI-override*/ $fromAI('Horas_al_d_a', `Horas estimadas que se dedicaran por día`, 'string') }}",
            "ID": "={{ /*n8n-auto-generated-fromAI-override*/ $fromAI('ID__using_to_match_', ``, 'string') }}"
          },
          "matchingColumns": [
            "ID"
          ],
          "schema": [
            {
              "id": "✓",
              "displayName": "✓",
              "required": false,
              "defaultMatch": false,
              "display": true,
              "type": "string",
              "canBeUsedToMatch": true
            },
            {
              "id": "ID",
              "displayName": "ID",
              "required": false,
              "defaultMatch": false,
              "display": true,
              "type": "string",
              "canBeUsedToMatch": true,
              "removed": false
            },
            {
              "id": "Fecha",
              "displayName": "Fecha",
              "required": false,
              "defaultMatch": false,
              "display": true,
              "type": "string",
              "canBeUsedToMatch": true
            },
            {
              "id": "Tarea",
              "displayName": "Tarea",
              "required": false,
              "defaultMatch": false,
              "display": true,
              "type": "string",
              "canBeUsedToMatch": true,
              "removed": false
            },
            {
              "id": "Horas al día",
              "displayName": "Horas al día",
              "required": false,
              "defaultMatch": false,
              "display": true,
              "type": "string",
              "canBeUsedToMatch": true
            }
          ],
          "attemptToConvertTypes": false,
          "convertFieldsToString": false
        },
        "options": {}
      },
      "type": "n8n-nodes-base.googleSheetsTool",
      "typeVersion": 4.7,
      "position": [
        1008,
        16
      ],
      "id": "e3ec9028-00f1-4ea0-b0f6-d62ec2a3fb48",
      "name": "Crear o editar una tarea",
      "credentials": {
        "googleSheetsOAuth2Api": {
          "id": "6WEu40HQCLCgukVJ",
          "name": "Google Sheets Pablin"
        }
      }
    },
    {
      "parameters": {
        "descriptionType": "manual",
        "toolDescription": "Edita una tarea existente en la hoja \"lista de tareas\".\n\nUsa esta herramienta cuando necesites modificar:\n- descripción\n- fecha\n- horas al día\n\nCampos:\n- tarea_id o índice (fila)\n- nuevos valores a actualizar\n\nReglas:\n- No modificar tareas completadas (visto = true)\n- Mantener coherencia en fechas\n- No dejar campos vacíos",
        "operation": "update",
        "documentId": {
          "__rl": true,
          "value": "1GuAWsnkBXXBKHqPMSrfjDOikFwhXgRPDUdzV0dlNbbc",
          "mode": "list",
          "cachedResultName": "Lista de tareas",
          "cachedResultUrl": "https://docs.google.com/spreadsheets/d/1GuAWsnkBXXBKHqPMSrfjDOikFwhXgRPDUdzV0dlNbbc/edit?usp=drivesdk"
        },
        "sheetName": {
          "__rl": true,
          "value": 1386834576,
          "mode": "list",
          "cachedResultName": "Tareas",
          "cachedResultUrl": "https://docs.google.com/spreadsheets/d/1GuAWsnkBXXBKHqPMSrfjDOikFwhXgRPDUdzV0dlNbbc/edit#gid=1386834576"
        },
        "columns": {
          "mappingMode": "defineBelow",
          "value": {
            "ID": "={{ /*n8n-auto-generated-fromAI-override*/ $fromAI('ID__using_to_match_', ``, 'string') }}",
            "✓": "={{ /*n8n-auto-generated-fromAI-override*/ $fromAI('_', ``, 'string') }}",
            "Fecha": "={{ /*n8n-auto-generated-fromAI-override*/ $fromAI('Fecha', `este campo es para la fecha de vencimiento, y puede ser vacía si no tiene una fecha fin`, 'string') }}",
            "Tarea": "={{ /*n8n-auto-generated-fromAI-override*/ $fromAI('Tarea', `nombre o descripción de la tarea`, 'string') }}",
            "Horas al día": "={{ /*n8n-auto-generated-fromAI-override*/ $fromAI('Horas_al_d_a', ``, 'string') }}"
          },
          "matchingColumns": [
            "ID"
          ],
          "schema": [
            {
              "id": "✓",
              "displayName": "✓",
              "required": false,
              "defaultMatch": false,
              "display": true,
              "type": "string",
              "canBeUsedToMatch": true
            },
            {
              "id": "ID",
              "displayName": "ID",
              "required": false,
              "defaultMatch": false,
              "display": true,
              "type": "string",
              "canBeUsedToMatch": true,
              "removed": false
            },
            {
              "id": "Fecha",
              "displayName": "Fecha",
              "required": false,
              "defaultMatch": false,
              "display": true,
              "type": "string",
              "canBeUsedToMatch": true
            },
            {
              "id": "Tarea",
              "displayName": "Tarea",
              "required": false,
              "defaultMatch": false,
              "display": true,
              "type": "string",
              "canBeUsedToMatch": true
            },
            {
              "id": "Horas al día",
              "displayName": "Horas al día",
              "required": false,
              "defaultMatch": false,
              "display": true,
              "type": "string",
              "canBeUsedToMatch": true
            },
            {
              "id": "row_number",
              "displayName": "row_number",
              "required": false,
              "defaultMatch": false,
              "display": true,
              "type": "number",
              "canBeUsedToMatch": true,
              "readOnly": true,
              "removed": true
            }
          ],
          "attemptToConvertTypes": false,
          "convertFieldsToString": false
        },
        "options": {}
      },
      "type": "n8n-nodes-base.googleSheetsTool",
      "typeVersion": 4.7,
      "position": [
        1136,
        16
      ],
      "id": "e624bd02-a3ab-4526-807d-ab2eb49a0330",
      "name": "Update row",
      "credentials": {
        "googleSheetsOAuth2Api": {
          "id": "6WEu40HQCLCgukVJ",
          "name": "Google Sheets Pablin"
        }
      }
    },
    {
      "parameters": {
        "chatId": "=6199654595",
        "text": "=Pensando...",
        "additionalFields": {
          "parse_mode": "HTML"
        }
      },
      "id": "d9a2088a-4ff2-49f9-991f-82b6cfd39316",
      "name": "Mensaje de espera",
      "type": "n8n-nodes-base.telegram",
      "typeVersion": 1,
      "position": [
        304,
        -480
      ],
      "webhookId": "4cfc3335-b30a-4e5e-8998-15008f862c18",
      "credentials": {
        "telegramApi": {
          "id": "zkPl4w5XYMD8k3Kk",
          "name": "Google calendar agent"
        }
      }
    }
  ],
  "connections": {
    "Switch": {
      "main": [
        [
          {
            "node": "Get a file",
            "type": "main",
            "index": 0
          },
          {
            "node": "Mensaje de espera",
            "type": "main",
            "index": 0
          }
        ],
        [
          {
            "node": "Edit Fields",
            "type": "main",
            "index": 0
          },
          {
            "node": "Mensaje de espera",
            "type": "main",
            "index": 0
          }
        ]
      ]
    },
    "Get a file": {
      "main": [
        [
          {
            "node": "Transcribe a recording",
            "type": "main",
            "index": 0
          }
        ]
      ]
    },
    "Transcribe a recording": {
      "main": [
        [
          {
            "node": "AI Agent",
            "type": "main",
            "index": 0
          }
        ]
      ]
    },
    "AI Agent": {
      "main": [
        [
          {
            "node": "Code in JavaScript",
            "type": "main",
            "index": 0
          }
        ],
        [
          {
            "node": "Edit Fields1",
            "type": "main",
            "index": 0
          }
        ]
      ]
    },
    "Edit Fields": {
      "main": [
        [
          {
            "node": "AI Agent",
            "type": "main",
            "index": 0
          }
        ]
      ]
    },
    "DeepSeek Chat Model": {
      "ai_languageModel": [
        [
          {
            "node": "AI Agent",
            "type": "ai_languageModel",
            "index": 0
          }
        ]
      ]
    },
    "Simple Memory": {
      "ai_memory": [
        [
          {
            "node": "AI Agent",
            "type": "ai_memory",
            "index": 0
          }
        ]
      ]
    },
    "Telegram Trigger": {
      "main": [
        [
          {
            "node": "Switch",
            "type": "main",
            "index": 0
          }
        ]
      ]
    },
    "Date & Time": {
      "ai_tool": [
        [
          {
            "node": "AI Agent",
            "type": "ai_tool",
            "index": 0
          }
        ]
      ]
    },
    "Get many events": {
      "ai_tool": [
        [
          {
            "node": "AI Agent",
            "type": "ai_tool",
            "index": 0
          }
        ]
      ]
    },
    "Create an event": {
      "ai_tool": [
        [
          {
            "node": "AI Agent",
            "type": "ai_tool",
            "index": 0
          }
        ]
      ]
    },
    "Update an event": {
      "ai_tool": [
        [
          {
            "node": "AI Agent",
            "type": "ai_tool",
            "index": 0
          }
        ]
      ]
    },
    "Delete an event": {
      "ai_tool": [
        [
          {
            "node": "AI Agent",
            "type": "ai_tool",
            "index": 0
          }
        ]
      ]
    },
    "Code in JavaScript": {
      "main": [
        [
          {
            "node": "Enviar mensaje",
            "type": "main",
            "index": 0
          }
        ]
      ]
    },
    "Edit Fields1": {
      "main": [
        [
          {
            "node": "Enviar mensaje",
            "type": "main",
            "index": 0
          }
        ]
      ]
    },
    "Schedule Trigger": {
      "main": [
        [
          {
            "node": "Edit Fields2",
            "type": "main",
            "index": 0
          }
        ]
      ]
    },
    "Edit Fields2": {
      "main": [
        [
          {
            "node": "AI Agent",
            "type": "main",
            "index": 0
          }
        ]
      ]
    },
    "Get Tareas": {
      "ai_tool": [
        [
          {
            "node": "AI Agent",
            "type": "ai_tool",
            "index": 0
          }
        ]
      ]
    },
    "Crear o editar una tarea": {
      "ai_tool": [
        [
          {
            "node": "AI Agent",
            "type": "ai_tool",
            "index": 0
          }
        ]
      ]
    },
    "Update row": {
      "ai_tool": [
        [
          {
            "node": "AI Agent",
            "type": "ai_tool",
            "index": 0
          }
        ]
      ]
    }
  },
  "pinData": {
    "Telegram Trigger": [
      {
        "update_id": 663464285,
        "message": {
          "message_id": 105,
          "from": {
            "id": 6199654595,
            "is_bot": false,
            "first_name": "Streamify",
            "username": "Streamifyhq",
            "language_code": "es"
          },
          "chat": {
            "id": 6199654595,
            "first_name": "Streamify",
            "username": "Streamifyhq",
            "type": "private"
          },
          "date": 1774322047,
          "voice": {
            "duration": 19,
            "mime_type": "audio/ogg",
            "file_id": "AwACAgEAAxkBAANpacIBfw5GvbaO7fgHIrgeiSuDsocAAg0HAAJgIRFGul7uQQABT2m-OgQ",
            "file_unique_id": "AgADDQcAAmAhEUY",
            "file_size": 142072
          }
        }
      }
    ]
  },
  "meta": {
    "templateCredsSetupCompleted": true,
    "instanceId": "2a4787fedcd3a9fda6d63f2231359e551e48f7e0d6a6b433946467fe82f7e7a4"
  }
}
