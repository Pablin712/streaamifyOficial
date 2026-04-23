# WhatsApp Meta Help Desk - Guía de Configuración

## FLUJO COMPLETO

```
Cliente WhatsApp → Meta Cloud API → n8n Webhook → Laravel → Base de Datos
                                                                          ↓
Cliente WhatsApp ← Meta Cloud API ← n8n Webhook ← Laravel ← Operador
```

---

## PARTE 1:安装 y Requisitos

### Requisitos Previos

- Laravel corriendo en `http://127.0.0.1:8000`
- n8n corriendo en `http://localhost:5678`
- ngrok para exponer localhost a internet

### Instalar n8n (Docker)

```bash
docker run -it --rm \
  --name n8n \
  -p 5678:5678 \
  -v n8n_data:/home/node/.n8n \
  -e N8N_BASIC_AUTH_ACTIVE=false \
  -e N8N_HOST=0.0.0.0 \
  -e N8N_PORT=5678 \
  n8nio/n8n
```

### Instalar ngrok

```bash
brew install ngrok
ngrok config add-authtoken TU_TOKEN  # Tu token de ngrok.io
```

---

## PARTE 2: Meta WhatsApp Cloud API

### Paso 1: Crear App en Meta Developers

1. Ve a https://developers.facebook.com/
2. Inicia sesión
3. Click **"My Apps"** → **"Create App"**
4. Selecciona **"Other"** → **"Business"**
5. Nombre: `WhatsApp Help Desk`
6. Click **"Create App"**

### Paso 2: Configurar WhatsApp

1. En el menú lateral busca **"WhatsApp"**
2. Click **"Configuration"**
3. Anota:
   - `PHONE_NUMBER_ID` ( está en API credentials )
   - `ACCESS_TOKEN` ( temporal, dura 24h )

### Paso 3: Agregar Teléfono

1. **WhatsApp** → **"Phone Numbers"**
2. Click **"Add Phone Number"**
3. Ingresa tu número y confirma con SMS

### Paso 4: Configurar Webhook

1. **WhatsApp** → **"Webhooks"** → **"Configuration"**
2. Click **"Edit"**
3. Callback URL: `https://TU-URL-NGROK.io/webhook/meta`
4. Verify Token: `mi_verify_token_seguro`
5. Click **"Verify and Save"**
6. Suscríbete al campo `messages`

---

## PARTE 3: ngrok

### Ejecutar ngrok

```bash
ngrok http 5678
```

### DEBERÍAS VER

```
Session Status        online
Forwarding          https://abc123.ngrok.io -> http://localhost:5678
```

**Copia la URL** `https://abc123.ngrok.io` (la tuya)

---

## PARTE 4: n8n Workflows

### Workflow 1: WhatsApp Inbound

**JSON para importar:**

```json
{
  "name": "WhatsApp Inbound",
  "nodes": [
    {
      "parameters": {
        "httpMethod": "POST",
        "path": "webhook-meta",
        "responseMode": "onReceived",
        "responseData": "allEntries"
      },
      "id": "webhook-inbound",
      "name": "Webhook Meta",
      "type": "n8n-nodes-base.webhook",
      "typeVersion": 1,
      "position": [250, 300]
    },
    {
      "parameters": {
        "values": {
          "telefono": {
            "value": "={{ $json.entry[0].changes[0].value.messages[0].from }}",
            "mode": "expression"
          },
          "nombre": {
            "value": "={{ $json.entry[0].changes[0].value.metadata.display_phone_number }}",
            "mode": "expression"
          },
          "mensaje": {
            "value": "={{ $json.entry[0].changes[0].value.messages[0].text.body }}",
            "mode": "expression"
          },
          "tipo": {
            "value": "={{ $json.entry[0].changes[0].value.messages[0].type }}",
            "mode": "expression"
          },
          "message_id": {
            "value": "={{ $json.entry[0].changes[0].value.messages[0].id }}",
            "mode": "expression"
          },
          "timestamp": {
            "value": "={{ $json.entry[0].changes[0].value.messages[0].timestamp }}",
            "mode": "expression"
          }
        },
        "options": {}
      },
      "id": "set-datos",
      "name": "Set Datos",
      "type": "n8n-nodes-base.set",
      "typeVersion": 3.4,
      "position": [450, 300]
    },
    {
      "parameters": {
        "method": "POST",
        "url": "http://127.0.0.1:8000/api/chat/whatsapp/inbound",
        "bodyParameters": {
          "parameters": [
            {
              "name": "token",
              "value": "test_token_123",
              "type": "string"
            },
            {
              "name": "telefono",
              "value": "={{ $json.telefono }}",
              "type": "string"
            },
            {
              "name": "nombre",
              "value": "={{ $json.nombre }}",
              "type": "string"
            },
            {
              "name": "mensaje",
              "value": "={{ $json.mensaje }}",
              "type": "string"
            },
            {
              "name": "tipo",
              "value": "={{ $json.tipo }}",
              "type": "string"
            },
            {
              "name": "external_message_id",
              "value": "={{ $json.message_id }}",
              "type": "string"
            }
          ]
        },
        "options": {}
      },
      "id": "http-laravel",
      "name": "Enviar a Laravel",
      "type": "n8n-nodes-base.httpRequest",
      "typeVersion": 4.1,
      "position": [650, 300]
    }
  ],
  "connections": {
    "Webhook Meta": {
      "main": [[
        { "node": "Set Datos", "type": "main", "index": 0 }
      ]]
    },
    "Set Datos": {
      "main": [[
        { "node": "Enviar a Laravel", "type": "main", "index": 0 }
      ]]
    }
  }
}
```

### Workflow 2: WhatsApp Outbound

**JSON para importar:**

```json
{
  "name": "WhatsApp Outbound",
  "nodes": [
    {
      "parameters": {
        "httpMethod": "POST",
        "path": "chat-outbound",
        "responseMode": "onReceived",
        "responseData": "allEntries"
      },
      "id": "webhook-outbound",
      "name": "Webhook Outbound",
      "type": "n8n-nodes-base.webhook",
      "typeVersion": 1,
      "position": [250, 300]
    },
    {
      "parameters": {
        "method": "POST",
        "url": "https://graph.facebook.com/v23.0/{{ $json.phone_number_id }}/messages",
        "sendHeaders": true,
        "headerParameters": {
          "parameters": [
            {
              "name": "Authorization",
              "value": "Bearer {{ $json.access_token }}",
              "type": "string"
            },
            {
              "name": "Content-Type",
              "value": "application/json",
              "type": "string"
            }
          ]
        },
        "bodyParameters": {
          "parameters": [
            {
              "name": "messaging_product",
              "value": "whatsapp",
              "type": "string"
            },
            {
              "name": "to",
              "value": "={{ $json.telefono }}",
              "type": "string"
            },
            {
              "name": "type",
              "value": "text",
              "type": "string"
            },
            {
              "name": "text",
              "value": "{\"body\": \"{{ $json.mensaje }}\"}",
              "type": "json"
            }
          ]
        },
        "options": {}
      },
      "id": "http-meta",
      "name": "Enviar a Meta",
      "type": "n8n-nodes-base.httpRequest",
      "typeVersion": 4.1,
      "position": [450, 300]
    }
  ],
  "connections": {
    "Webhook Outbound": {
      "main": [[
        { "node": "Enviar a Meta", "type": "main", "index": 0 }
      ]]
    }
  }
}
```

### Importar Workflow en n8n

1. Ve a http://localhost:5678
2. Copia el JSON de arriba
3. Click **"Import from Clipboard"**
4. Click **"Save"** → **"Activate"**

---

## PARTE 5: Laravel .env

Agrega en `.env`:

```env
# Meta WhatsApp
META_WHATSAPP_PHONE_NUMBER_ID=tu_phone_number_id
META_WHATSAPP_ACCESS_TOKEN=tu_access_token
META_WHATSAPP_WEBHOOK_VERIFY_TOKEN=mi_verify_token_seguro
```

---

## URLs IMPORTANTES

| Servicio | URL |
|----------|-----|
| Laravel | http://127.0.0.1:8000 |
| n8n | http://localhost:5678 |
| ngrok UI | http://127.0.0.1:4040 |
| Panel Chat | http://127.0.0.1:8000/chat/panel |

---

## CHECKLIST

```
☐ Meta App creada en developers.facebook.com
☐ Phone Number verificado
☐ ngrok ejecutándose
☐ n8n ejecutándose  
☐ Workflow inbound creado y activo
☐ Workflow outbound creado y activo
☐ Webhook configurado en Meta con URL ngrok
☐ .env configurado con tokens
☐ Laravel corriendo
☐ Probando mensaje: cliente → WhatsApp → Laravel
☐ Probando respuesta: operador → panel → WhatsApp cliente
```

---

## RESOLUCIÓN DE PROBLEMAS

### Meta no puede alcanzar ngrok

- Verifica ngrok está corriendo: `ngrok status`
- Verifica el webhook en Meta usa HTTPS

### n8n no recibe mensajes

- Verifica workflow está activo (toggle verde)
- Revisa logs en n8n: click en el nodo → **"Execution"**

### Laravel no guarda mensajes

- Revisa logs: `tail -f storage/logs/laravel.log`
- Verifica token en .env coincide con el enviado por n8n

### No llegan mensajes de respuesta

- Verifica PHONE_NUMBER_ID y ACCESS_TOKEN en .env
- Revisa Graph API en https://developers.facebook.com/