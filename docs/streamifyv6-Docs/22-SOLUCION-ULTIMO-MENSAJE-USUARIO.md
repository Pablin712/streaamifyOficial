# 🚨 SOLUCIÓN: Problema con ultimo_mensaje_usuario y ultimo_mensaje_bot

## 📋 Problema Detectado

En la base de datos se está guardando **incorrectamente**:

```
❌ ultimo_mensaje_usuario: "{}"          // Incorrecto (objeto vacío)
✅ ultimo_mensaje_usuario: "Hola pibe"   // Correcto (texto del mensaje)
```

## 🔧 Causa del Problema

El agente IA en N8N está llamando `update_memory` con el parámetro `ultimo_mensaje_usuario` como **objeto vacío** `{}` en lugar de extraer el **texto del mensaje** de Telegram.

## ✅ Solución: Configuración Correcta en N8N

### 1. En el AI Agent Node

El prompt del agente debe especificar **EXACTAMENTE** cómo obtener el texto:

```
Cuando llames a update_memory, debes obtener el texto del mensaje así:

❌ NO USAR:
- ultimo_mensaje_usuario: {{$json}}
- ultimo_mensaje_usuario: {{$json.message}}
- ultimo_mensaje_usuario: {}

✅ SÍ USAR:
- ultimo_mensaje_usuario: {{$json.message.text}}

O en JavaScript:
- ultimo_mensaje_usuario: item.message.text
```

### 2. En el Tool "update_memory"

Configurar los parámetros así:

```javascript
{
  "chat_id": "{{ $json.chat_id }}",              // Número
  "step": "inicio",                               // String
  "proceso": null,                                // null (no "null")
  "datos": "{}",                                  // String JSON vacío
  "intentos": 0,                                  // Número
  
  // ⚠️ CRÍTICO - Obtener el TEXTO del mensaje:
  "ultimo_mensaje_bot": "👋 ¡Hola! Para usar...", // String del mensaje
  "ultimo_mensaje_usuario": "{{ $json.message.text }}" // ✅ TEXTO del mensaje
}
```

### 3. Estructura del Mensaje de Telegram

El mensaje que llega del Telegram Trigger tiene esta estructura:

```json
{
  "message": {
    "message_id": 123,
    "from": {
      "id": 6199654595,
      "first_name": "Usuario"
    },
    "chat": {
      "id": 6199654595
    },
    "date": 1736366928,
    "text": "Hola pibe"  // ← ESTE es el texto que necesitas
  }
}
```

## 📝 Instrucciones Actualizadas en StepSeeder

He actualizado el paso "inicio" con instrucciones **EXPLÍCITAS**:

```php
⚠️ IMPORTANTE - FORMATO DE ACTUALIZACIÓN DE MEMORIA:

update_memory debe recibir EXACTAMENTE estos valores:

- chat_id: (número del chat de Telegram)
- step: "inicio" (string)
- proceso: null (null, no string "null")
- ultimo_mensaje_bot: "👋 ¡Hola! Para usar Streamify Bot..." (string)
- ultimo_mensaje_usuario: obtener el TEXTO usando: item.message.text o $json.message.text
- datos: JSON.stringify({}) (string JSON: "{}")
- intentos: 0 (número)

❌ NO HACER:
- NO guardar ultimo_mensaje_usuario como objeto: {}
- NO guardar ultimo_mensaje_usuario como JSON string: "{}"
- NO guardar ultimo_mensaje_usuario como undefined o null

✅ SÍ HACER:
- Guardar ultimo_mensaje_usuario como STRING con el texto: "Hola pibe"
- Obtener texto desde: $json.message.text o item.message.text
```

## 🎯 Ejemplo Correcto en N8N

### Opción A: En el AI Agent Tool Configuration

```json
{
  "name": "update_memory",
  "parameters": {
    "chat_id": {
      "type": "number",
      "value": "={{ $json.message.chat.id }}"
    },
    "step": {
      "type": "string",
      "value": "inicio"
    },
    "proceso": {
      "type": "string",
      "value": null
    },
    "ultimo_mensaje_bot": {
      "type": "string",
      "value": "👋 ¡Hola! Para usar Streamify Bot necesito vincular tu cuenta.\n\n¿Ya tienes una cuenta en https://streamify.aaronsoft.es?\n\nPor favor responde:\n✅ SI (si ya tienes cuenta)\n❌ NO (si necesitas crear cuenta)"
    },
    "ultimo_mensaje_usuario": {
      "type": "string",
      "value": "={{ $json.message.text }}"  // ✅ ESTO es lo correcto
    },
    "datos": {
      "type": "string",
      "value": "{}"
    },
    "intentos": {
      "type": "number",
      "value": 0
    }
  }
}
```

### Opción B: Si el AI Agent llama directamente

El AI Agent debe generar la llamada así:

```javascript
// ✅ CORRECTO
update_memory({
  chat_id: 6199654595,
  step: "inicio",
  proceso: null,
  ultimo_mensaje_bot: "👋 ¡Hola! Para usar Streamify Bot...",
  ultimo_mensaje_usuario: "Hola pibe",  // ← Texto extraído del mensaje
  datos: "{}",
  intentos: 0
})

// ❌ INCORRECTO
update_memory({
  chat_id: 6199654595,
  step: "inicio",
  proceso: null,
  ultimo_mensaje_bot: "👋 ¡Hola! Para usar Streamify Bot...",
  ultimo_mensaje_usuario: {},  // ← NO! Esto es un objeto vacío
  datos: "{}",
  intentos: 0
})
```

## 🔍 Verificación en la Base de Datos

Después de enviar "Hola pibe", deberías ver:

```
✅ CORRECTO:
+---------------+--------+---------+--------------------+------------------------+-------+
| chat_id       | step   | proceso | ultimo_mensaje_bot | ultimo_mensaje_usuario | datos |
+---------------+--------+---------+--------------------+------------------------+-------+
| 6199654595    | inicio | null    | 👋 ¡Hola! Para... | Hola pibe              | {}    |
+---------------+--------+---------+--------------------+------------------------+-------+

❌ INCORRECTO (lo que está pasando ahora):
+---------------+--------+---------+--------------------+------------------------+-------+
| chat_id       | step   | proceso | ultimo_mensaje_bot | ultimo_mensaje_usuario | datos |
+---------------+--------+---------+--------------------+------------------------+-------+
| 6199654595    | inicio | null    | ¡Hola! 👋 ¡Bien...| {}                     | {}    |
+---------------+--------+---------+--------------------+------------------------+-------+
```

## 🛠️ Debugging en N8N

Para verificar qué está recibiendo el agente, agrega un **Code Node** antes del AI Agent:

```javascript
// Code Node - Debug Telegram Message
const message = $input.all()[0].json.message;

return [{
  json: {
    chat_id: message.chat.id,
    message_text: message.text,  // ← Este es el valor que necesitas
    full_message: message,
    debug_info: {
      tiene_texto: !!message.text,
      tipo_texto: typeof message.text,
      valor_texto: message.text
    }
  }
}];
```

Deberías ver:
```json
{
  "chat_id": 6199654595,
  "message_text": "Hola pibe",
  "debug_info": {
    "tiene_texto": true,
    "tipo_texto": "string",
    "valor_texto": "Hola pibe"
  }
}
```

## 📊 Flujo Correcto Completo

```
┌─ Usuario envía "Hola pibe" ─────────────────────────────────┐
│ Telegram Webhook recibe:                                    │
│ {                                                            │
│   "message": {                                               │
│     "chat": { "id": 6199654595 },                           │
│     "text": "Hola pibe"  ← EXTRAER ESTO                     │
│   }                                                          │
│ }                                                            │
└──────────────────────────────────────────────────────────────┘
                          ↓
┌─ AI Agent procesa ──────────────────────────────────────────┐
│ get_memory(6199654595) → no existe                          │
│ get_step_instructions("inicio")                             │
│                                                              │
│ Llamar update_memory con:                                   │
│   chat_id: 6199654595                                       │
│   step: "inicio"                                            │
│   proceso: null                                             │
│   ultimo_mensaje_bot: "👋 ¡Hola! Para usar..."             │
│   ultimo_mensaje_usuario: "Hola pibe" ← ✅ TEXTO           │
│   datos: "{}"                                               │
│   intentos: 0                                               │
└──────────────────────────────────────────────────────────────┘
                          ↓
┌─ Base de Datos guarda ──────────────────────────────────────┐
│ INSERT INTO telegram_auth_sessions                          │
│ (chat_id, step, proceso, ultimo_mensaje_bot,               │
│  ultimo_mensaje_usuario, datos, intentos)                   │
│ VALUES                                                       │
│ (6199654595, 'inicio', null,                                │
│  '👋 ¡Hola! Para usar...',                                 │
│  'Hola pibe', '{}', 0)  ← ✅ CORRECTO                      │
└──────────────────────────────────────────────────────────────┘
                          ↓
┌─ Bot envía mensaje ─────────────────────────────────────────┐
│ Send Telegram Message:                                      │
│ "👋 ¡Hola! Para usar Streamify Bot necesito vincular tu    │
│  cuenta. ¿Ya tienes una cuenta? (SI/NO)"                   │
└──────────────────────────────────────────────────────────────┘
```

## 🎯 Resumen de la Solución

1. ✅ **StepSeeder actualizado** con instrucciones explícitas
2. ⚠️ **Configurar N8N** para extraer `$json.message.text`
3. 🔍 **Verificar** que el tool `update_memory` recibe el texto como string
4. 🧪 **Probar** enviando mensaje y verificando en BD

---

**Fecha**: 8 de enero de 2026
**Problema**: ultimo_mensaje_usuario guardaba "{}" en lugar del texto del mensaje
**Solución**: Instrucciones explícitas en StepSeeder + configuración correcta en N8N
