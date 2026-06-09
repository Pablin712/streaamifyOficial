no q# 🤖 GUÍA COMPLETA: INTEGRACIÓN N8N + DEEPSEEK + STREAMIFY API

## 📋 ÍNDICE
1. [Sistema de Respuestas Rápidas (Sin IA)](#1-sistema-de-respuestas-rápidas)
2. [Asistente IA para Empleados](#2-asistente-ia-para-empleados)
3. [Asistente IA para Clientes](#3-asistente-ia-para-clientes)
4. [Configuración de n8n](#4-configuración-de-n8n)
5. [Ejemplos de Flujos](#5-ejemplos-de-flujos)

---

## 1. SISTEMA DE RESPUESTAS RÁPIDAS

### 🎯 Para Empleados

**Listar comandos disponibles**
```bash
GET /api/v1/quick-responses?tipo=empleado
X-API-Key: tu-api-key
```

Respuesta:
```json
{
  "success": true,
  "data": [
    {"comando": "bancos", "titulo": "Métodos de Pago", "tipo": "empleado"},
    {"comando": "precios", "titulo": "Lista de Precios", "tipo": "empleado"},
    {"comando": "politicas", "titulo": "Políticas Internas", "tipo": "empleado"}
  ],
  "count": 3
}
```

**Obtener respuesta por comando**
```bash
GET /api/v1/quick-responses/comando/bancos
X-API-Key: tu-api-key
```

Respuesta:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "comando": "bancos",
    "titulo": "Métodos de Pago Disponibles",
    "contenido": "💳 **MÉTODOS DE PAGO ACEPTADOS**\n\n📱 Transferencias...",
    "tipo": "empleado",
    "tags": ["pago", "transferencia", "banco"]
  }
}
```

**Buscar comandos**
```bash
GET /api/v1/quick-responses/search?q=pago&tipo=empleado
X-API-Key: tu-api-key
```

### 🎯 Para Clientes (Sin Autenticación)

**Listar comandos públicos**
```bash
GET /api/v1/public/quick-responses?tipo=cliente
```

**Obtener respuesta pública**
```bash
GET /api/v1/public/quick-responses/comando/pagos
```

---

## 2. ASISTENTE IA PARA EMPLEADOS

### 🔑 Autenticación
Todos estos endpoints requieren `X-API-Key` en el header.

### 📊 Endpoints Disponibles

#### 2.1 Perfiles Disponibles de un Servicio

```bash
GET /api/v1/ai/perfiles-disponibles?servicio=SPOTIFY&limit=5
X-API-Key: tu-api-key
```

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "idper": 10,
      "numero_perfil": 1,
      "pin": "1234",
      "cuenta": {
        "correo": "spotify@example.com",
        "servicio": "SPOTIFY",
        "plan": "Premium Individual"
      },
      "usuarios_activos": 0,
      "disponible": true
    }
  ],
  "count": 1,
  "servicio": "SPOTIFY",
  "message": "Se encontraron 1 perfiles disponibles de SPOTIFY"
}
```

**Uso en DeepSeek:**
```json
{
  "name": "consultar_perfiles_disponibles",
  "description": "Consulta perfiles disponibles de un servicio (SPOTIFY, NETFLIX, etc.)",
  "parameters": {
    "type": "object",
    "properties": {
      "servicio": {"type": "string", "enum": ["SPOTIFY", "NETFLIX", "DISNEY", "HBO"]},
      "limit": {"type": "integer", "default": 10}
    },
    "required": ["servicio"]
  }
}
```

#### 2.2 Servicios Disponibles

```bash
GET /api/v1/ai/servicios
X-API-Key: tu-api-key
```

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {"idser": 1, "nombreser": "SPOTIFY", "descripcionser": "Música en streaming"},
    {"idser": 2, "nombreser": "NETFLIX", "descripcionser": "Películas y series"}
  ],
  "count": 2
}
```

#### 2.3 Precios de Servicios

```bash
GET /api/v1/ai/precios
GET /api/v1/ai/precios?servicio=NETFLIX
X-API-Key: tu-api-key
```

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "servicio": "NETFLIX",
      "plan": "Premium",
      "precio_mensual": 25.00,
      "descripcion": "4K + 4 pantallas",
      "tipo_producto": "Streaming"
    }
  ],
  "count": 1
}
```

#### 2.4 Buscar Cliente

```bash
GET /api/v1/ai/buscar-cliente?q=Juan
X-API-Key: tu-api-key
```

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "idcli": 1,
      "nombrecli": "Juan Pérez",
      "email": "juan@example.com",
      "telefonocli": "0999123456",
      "created_at": "2025-01-01"
    }
  ],
  "count": 1
}
```

#### 2.5 Ventas de un Cliente

```bash
GET /api/v1/ai/cliente/1/ventas
X-API-Key: tu-api-key
```

**Respuesta:**
```json
{
  "success": true,
  "cliente": {
    "id": 1,
    "nombre": "Juan Pérez",
    "email": "juan@example.com"
  },
  "ventas": [
    {
      "idven": "001-001-000000001",
      "fecha": "2025-01-01",
      "servicios": [
        {
          "descripcion": "Netflix Premium",
          "monto": 25.00,
          "vencimiento": "2025-02-01",
          "activo": true
        }
      ]
    }
  ],
  "total_ventas": 1
}
```

#### 2.6 Estadísticas Generales

```bash
GET /api/v1/ai/estadisticas
X-API-Key: tu-api-key
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "clientes_totales": 150,
    "ventas_mes_actual": 45,
    "servicios_activos": 8,
    "perfiles_disponibles": 23
  }
}
```

---

## 3. ASISTENTE IA PARA CLIENTES

### 🌐 Endpoints Públicos (Sin Autenticación)

#### 3.1 Base de Conocimientos

```bash
GET /api/v1/public/ai/knowledge-base
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "empresa": {
      "nombre": "Streamify",
      "descripcion": "Plataforma de servicios de streaming compartidos",
      "whatsapp": "+593 99 123 4567",
      "email": "soporte@streamify.com",
      "horarios": "Lun-Vie 8AM-10PM, Sáb-Dom 9AM-6PM"
    },
    "servicios": [...],
    "planes": [...],
    "politicas": {
      "garantia": "99% uptime garantizado...",
      "reembolso": "Reembolso completo si...",
      "soporte": "Soporte técnico 24/7...",
      "renovacion": "Descuentos: 10% en..."
    }
  }
}
```

#### 3.2 Servicios Disponibles (Público)

```bash
GET /api/v1/public/ai/servicios
```

#### 3.3 Precios (Público)

```bash
GET /api/v1/public/ai/precios
GET /api/v1/public/ai/precios?servicio=NETFLIX
```

---

## 4. CONFIGURACIÓN DE N8N

### 4.1 Nodo HTTP Request - Configuración Base

**Para Empleados (Con API Key):**
```
URL: http://localhost/streaamifyOficial/public/api/v1/ai/perfiles-disponibles
Method: GET
Authentication: Generic Credential Type
Header Parameters:
  - Name: X-API-Key
  - Value: sk_X23MK7KzyvLe7XdbYSlYjSIPTd3eQFlSvW5SmPBrsk5WKpNrfdRUexhEtN2X
Query Parameters:
  - servicio: {{ $json.parametros.servicio }}
  - limit: 10
```

**Para Clientes (Sin Auth):**
```
URL: http://localhost/streaamifyOficial/public/api/v1/public/ai/knowledge-base
Method: GET
Authentication: None
```

### 4.2 Configuración de DeepSeek

**System Prompt para Empleados:**
```
Eres un asistente de Streamify para empleados. Tienes acceso a herramientas para:

1. consultar_perfiles_disponibles(servicio): Ver perfiles disponibles de SPOTIFY, NETFLIX, etc.
2. buscar_cliente(nombre): Buscar clientes por nombre/email/teléfono
3. obtener_ventas_cliente(id_cliente): Ver historial de compras
4. obtener_precios(): Ver precios actuales
5. obtener_servicios(): Listar servicios disponibles

IMPORTANTE:
- Siempre verifica permisos del empleado antes de mostrar información sensible
- No compartas contraseñas maestras, solo datos de perfiles específicos
- Sé profesional y directo

Ejemplo de conversación:
Usuario: "¿Hay perfiles de Spotify disponibles?"
Asistente: [Usa consultar_perfiles_disponibles con servicio="SPOTIFY"]
"Sí, hay 3 perfiles disponibles de Spotify Premium..."
```

**System Prompt para Clientes:**
```
Eres un asistente de soporte de Streamify para clientes. Tienes acceso a:

1. Base de conocimientos con información de la empresa
2. Servicios y precios actuales
3. Políticas de garantía y soporte

IMPORTANTE:
- Sé amable y profesional
- Si no sabes algo, di "Déjame consultar con un agente"
- No inventes información, usa solo los datos de la API
- Ofrece contacto directo si el cliente lo necesita

Información de contacto:
- WhatsApp: +593 99 123 4567
- Email: soporte@streamify.com
- Web: www.streamify.com
```

---

## 5. EJEMPLOS DE FLUJOS

### 5.1 Flujo: Empleado Consulta Perfiles Disponibles

**Nodos:**
```
[Webhook Trigger]
    ↓
[DeepSeek AI]
    ↓
[HTTP Request - Perfiles Disponibles] (si DeepSeek llama la función)
    ↓
[DeepSeek AI] (procesa respuesta)
    ↓
[Respond to Webhook]
```

**Configuración Webhook:**
```
URL: https://tu-n8n.com/webhook/empleado-chat
Method: POST
Response: Immediately
Body:
{
  "mensaje": "{{ $json.mensaje }}",
  "empleado_id": "{{ $json.empleado_id }}"
}
```

**Configuración DeepSeek (Function Calling):**
```json
{
  "model": "deepseek-chat",
  "messages": [
    {"role": "system", "content": "..."},
    {"role": "user", "content": "{{ $json.mensaje }}"}
  ],
  "tools": [
    {
      "type": "function",
      "function": {
        "name": "consultar_perfiles_disponibles",
        "description": "Consulta perfiles disponibles de un servicio",
        "parameters": {
          "type": "object",
          "properties": {
            "servicio": {
              "type": "string",
              "enum": ["SPOTIFY", "NETFLIX", "DISNEY", "HBO", "PRIME"],
              "description": "Nombre del servicio a consultar"
            },
            "limit": {
              "type": "integer",
              "default": 10,
              "description": "Cantidad máxima de resultados"
            }
          },
          "required": ["servicio"]
        }
      }
    }
  ]
}
```

**HTTP Request cuando DeepSeek llama la función:**
```
URL: http://localhost/streaamifyOficial/public/api/v1/ai/perfiles-disponibles
Method: GET
Headers:
  X-API-Key: {{ $credentials.streamify_api_key }}
Query:
  servicio: {{ $json.tool_calls[0].function.arguments.servicio }}
  limit: {{ $json.tool_calls[0].function.arguments.limit }}
```

### 5.2 Flujo: Cliente Pregunta sobre Precios

**Nodos:**
```
[Chat Trigger / Webhook]
    ↓
[HTTP Request - Knowledge Base] (carga contexto inicial)
    ↓
[HTTP Request - Precios] (obtiene precios actuales)
    ↓
[DeepSeek AI] (con contexto + precios)
    ↓
[Respond]
```

**HTTP Request 1 - Knowledge Base:**
```
URL: /api/v1/public/ai/knowledge-base
Method: GET
Variable: {{ $json.kb }}
```

**HTTP Request 2 - Precios:**
```
URL: /api/v1/public/ai/precios
Method: GET
Variable: {{ $json.precios }}
```

**DeepSeek Prompt:**
```
Context (Knowledge Base): {{ $node["Knowledge Base"].json }}
Context (Precios): {{ $node["Precios"].json }}

Usuario pregunta: {{ $json.mensaje }}

Responde de forma amigable usando la información del contexto.
```

### 5.3 Ejemplo Completo: JSON del Flujo n8n

```json
{
  "name": "Streamify AI Assistant - Empleados",
  "nodes": [
    {
      "name": "Webhook",
      "type": "n8n-nodes-base.webhook",
      "parameters": {
        "path": "streamify-empleado-chat",
        "responseMode": "responseNode",
        "options": {}
      }
    },
    {
      "name": "DeepSeek",
      "type": "@n8n/n8n-nodes-deepseek.deepseek",
      "parameters": {
        "model": "deepseek-chat",
        "messages": [
          {
            "role": "system",
            "content": "Eres asistente de Streamify para empleados..."
          },
          {
            "role": "user",
            "content": "={{ $json.mensaje }}"
          }
        ],
        "tools": [
          {
            "type": "function",
            "function": {
              "name": "consultar_perfiles_disponibles",
              "description": "Consulta perfiles disponibles",
              "parameters": {
                "type": "object",
                "properties": {
                  "servicio": {"type": "string"}
                },
                "required": ["servicio"]
              }
            }
          }
        ]
      }
    },
    {
      "name": "API Perfiles",
      "type": "n8n-nodes-base.httpRequest",
      "parameters": {
        "url": "http://localhost/streaamifyOficial/public/api/v1/ai/perfiles-disponibles",
        "authentication": "genericCredentialType",
        "genericAuthType": "httpHeaderAuth",
        "queryParameters": {
          "parameters": [
            {
              "name": "servicio",
              "value": "={{ $json.tool_calls[0].function.arguments.servicio }}"
            }
          ]
        }
      },
      "executeOnce": true,
      "credentials": {
        "httpHeaderAuth": {
          "id": "1",
          "name": "Streamify API Key"
        }
      }
    },
    {
      "name": "Respond",
      "type": "n8n-nodes-base.respondToWebhook",
      "parameters": {
        "respondWith": "text",
        "responseBody": "={{ $json.respuesta }}"
      }
    }
  ]
}
```

---

## 6. TESTING

### 6.1 Test con cURL - Perfiles Disponibles

```bash
curl -X GET "http://localhost/streaamifyOficial/public/api/v1/ai/perfiles-disponibles?servicio=SPOTIFY&limit=5" \
  -H "X-API-Key: sk_X23MK7KzyvLe7XdbYSlYjSIPTd3eQFlSvW5SmPBrsk5WKpNrfdRUexhEtN2X"
```

### 6.2 Test con cURL - Knowledge Base

```bash
curl -X GET "http://localhost/streaamifyOficial/public/api/v1/public/ai/knowledge-base"
```

### 6.3 Test con Postman

**Collection: Streamify AI Endpoints**

1. **Perfiles Disponibles**
   - GET `{{base_url}}/api/v1/ai/perfiles-disponibles?servicio=SPOTIFY`
   - Headers: `X-API-Key: {{api_key}}`

2. **Buscar Cliente**
   - GET `{{base_url}}/api/v1/ai/buscar-cliente?q=Juan`
   - Headers: `X-API-Key: {{api_key}}`

3. **Knowledge Base (Público)**
   - GET `{{base_url}}/api/v1/public/ai/knowledge-base`
   - Sin headers

---

## 7. CHECKLIST DE IMPLEMENTACIÓN

### ✅ Backend Laravel (COMPLETADO)

- [x] Tabla `quick_responses` creada
- [x] Modelo `QuickResponse` con scopes
- [x] Controller `QuickResponseController`
- [x] Controller `AIAssistantController`
- [x] Rutas protegidas (empleados)
- [x] Rutas públicas (clientes)
- [x] Seeder con 12 respuestas de ejemplo

### 📝 Por Hacer en n8n

- [ ] Crear credencial para API Key de Streamify
- [ ] Crear flujo para empleados con DeepSeek
- [ ] Crear flujo para clientes con DeepSeek
- [ ] Configurar webhooks
- [ ] Probar function calling
- [ ] Integrar con WhatsApp/Telegram (opcional)

---

**Fecha**: Diciembre 4, 2025  
**API Base URL**: `http://localhost/streaamifyOficial/public/api/v1`  
**Documentación Completa**: ✅
