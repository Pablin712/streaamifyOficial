# Sistema de Autenticación de Telegram - Streamify

## Descripción General

Sistema completo de autenticación y registro de clientes a través de Telegram Bot, usando N8N como orquestador. El sistema mantiene el estado de la conversación en una base de datos para soportar flujos multi-paso.

## Estructura del Sistema

### Base de Datos

#### Tabla: `telegram_auth_sessions`
Almacena el estado de las conversaciones de autenticación.

```sql
CREATE TABLE telegram_auth_sessions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    chat_id BIGINT UNIQUE NOT NULL,
    step VARCHAR(50) DEFAULT 'inicio',
    proceso ENUM('login', 'registro') NULL,
    datos JSON NULL,
    intentos TINYINT UNSIGNED DEFAULT 0,
    last_activity TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_chat_id (chat_id),
    INDEX idx_step (step),
    INDEX idx_last_activity (last_activity)
);
```

#### Tabla: `clientes` (actualizada)
Campo agregado para vincular con Telegram:

```sql
ALTER TABLE clientes 
ADD COLUMN telegram_chat_id BIGINT NULL UNIQUE AFTER email;
```

### Pasos del Flujo de Autenticación

#### Flujo de Login
1. **inicio** → Usuario indica que tiene cuenta (SI)
2. **login_email** → Usuario proporciona email
3. **login_password** → Usuario proporciona contraseña
4. **completado** → Autenticación exitosa, sesión eliminada

#### Flujo de Registro
1. **inicio** → Usuario indica que NO tiene cuenta
2. **registro_nombre** → Usuario proporciona nombre completo
3. **registro_email** → Usuario proporciona email
4. **registro_telefono** → Usuario proporciona teléfono
5. **registro_password** → Usuario crea contraseña
6. **registro_confirmar** → Usuario confirma datos (SI/NO)
7. **completado** → Registro exitoso, sesión eliminada

## Configuración del Agente IA con Tools MySQL

### Descripción General

El agente de IA en N8N tiene acceso **DIRECTO** a la base de datos mediante **tools MySQL**. La tabla `telegram_auth_sessions` actúa como su memoria conversacional persistente.

### Estructura de la Memoria (Tabla telegram_auth_sessions)

```sql
CREATE TABLE telegram_auth_sessions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    chat_id BIGINT UNIQUE NOT NULL COMMENT 'ID del chat de Telegram',
    step VARCHAR(50) DEFAULT 'inicio' COMMENT 'Paso actual: inicio, login_email, registro_nombre, etc',
    proceso ENUM('login', 'registro') NULL COMMENT 'Tipo de proceso en curso',
    datos JSON NULL COMMENT 'Datos recolectados: {email, nombre, telefono, password}',
    intentos TINYINT UNSIGNED DEFAULT 0 COMMENT 'Intentos fallidos de login',
    last_activity TIMESTAMP NULL COMMENT 'Última interacción del usuario',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Tools MySQL para el Agente IA

#### Tool 0: get_step_instructions (Obtener Instrucciones del Paso) - NUEVO

```sql
-- Query para obtener las instrucciones detalladas del paso actual
SELECT 
    name,
    description,
    next_step
FROM steps
WHERE name = :step_name
LIMIT 1;
```

**Configuración en N8N:**
```json
{
  "name": "get_step_instructions",
  "description": "Obtiene las instrucciones detalladas para un paso específico del flujo. El campo 'description' contiene un subprompt completo con todas las instrucciones, validaciones y mensajes para ese paso. USA ESTE TOOL para saber qué hacer en cada paso.",
  "type": "mysql",
  "query": "SELECT name, description, next_step FROM steps WHERE name = :step_name LIMIT 1",
  "parameters": {
    "step_name": "string"
  }
}
```

**Ejemplo de Uso:**
Cuando get_memory devuelve `step: 'login_email'`, llama `get_step_instructions('login_email')` para obtener las instrucciones completas de ese paso.

#### Tool 1: get_memory (Leer Memoria)

```sql
-- Query para obtener el estado actual de la conversación
SELECT 
    chat_id,
    step,
    proceso,
    datos,
    intentos,
    last_activity,
    CASE 
        WHEN last_activity < NOW() - INTERVAL 10 MINUTE THEN true 
        ELSE false 
    END as expirada
FROM telegram_auth_sessions
WHERE chat_id = {{ $json.chat_id }}
LIMIT 1;
```

**Configuración en N8N:**
```json
{
  "name": "get_memory",
  "description": "Lee el estado actual de la conversación del usuario desde la memoria. Devuelve: step (paso actual), proceso (login/registro), datos (info recolectada), intentos, expirada. ÚSALA SIEMPRE al inicio de cada conversación.",
  "type": "mysql",
  "query": "SELECT chat_id, step, proceso, datos, intentos, last_activity, CASE WHEN last_activity < NOW() - INTERVAL 10 MINUTE THEN true ELSE false END as expirada FROM telegram_auth_sessions WHERE chat_id = :chat_id LIMIT 1",
  "parameters": {
    "chat_id": "number"
  }
}
```

#### Tool 2: update_memory (Actualizar Memoria)

```sql
-- Query para actualizar el estado de la conversación
INSERT INTO telegram_auth_sessions 
    (chat_id, step, proceso, datos, intentos, last_activity, created_at, updated_at)
VALUES 
    (:chat_id, :step, :proceso, :datos, :intentos, NOW(), NOW(), NOW())
ON DUPLICATE KEY UPDATE
    step = VALUES(step),
    proceso = VALUES(proceso),
    datos = VALUES(datos),
    intentos = VALUES(intentos),
    last_activity = VALUES(last_activity),
    updated_at = VALUES(updated_at);
```

**Configuración en N8N:**
```json
{
  "name": "update_memory",
  "description": "Actualiza o crea la memoria del usuario. El campo datos debe ser un JSON STRING (usa JSON.stringify). Ejemplo: datos = '{\"email\":\"test@test.com\",\"nombre\":\"Test\"}'. Parámetros: chat_id (número), step (string), proceso (string: 'login' o 'registro' o null), datos (JSON como string), intentos (número).",
  "type": "mysql",
  "query": "INSERT INTO telegram_auth_sessions (chat_id, step, proceso, datos, intentos, last_activity, created_at, updated_at) VALUES (:chat_id, :step, :proceso, :datos, :intentos, NOW(), NOW(), NOW()) ON DUPLICATE KEY UPDATE step = VALUES(step), proceso = VALUES(proceso), datos = VALUES(datos), intentos = VALUES(intentos), last_activity = VALUES(last_activity), updated_at = VALUES(updated_at)",
  "parameters": {
    "chat_id": "number",
    "step": "string",
    "proceso": "string",
    "datos": "string",
    "intentos": "number"
  }
}
```

**Ejemplo de Uso en N8N:**

Para actualizar memoria con datos, en el query parameter `datos` debes usar:
```javascript
{{ JSON.stringify({email: "juan@test.com", nombre: "Juan"}) }}
```

O si quieres vacío:
```javascript
{{ JSON.stringify({}) }}
```

O directamente como string:
```
"{\"email\":\"juan@test.com\"}"
```

#### Tool 3: delete_memory (Eliminar Memoria)

```sql
-- Query para eliminar la sesión cuando auth completa
DELETE FROM telegram_auth_sessions 
WHERE chat_id = {{ $json.chat_id }};
```

**Configuración en N8N:**
```json
{
  "name": "delete_memory",
  "description": "Elimina la memoria/sesión del usuario. ÚSALA SOLO cuando el usuario complete exitosamente el login o registro.",
  "type": "mysql",
  "query": "DELETE FROM telegram_auth_sessions WHERE chat_id = :chat_id",
  "parameters": {
    "chat_id": "number"
  }
}
```

#### Tool 4: check_registered (Verificar Cliente)

```sql
-- Query para verificar si el usuario ya tiene cuenta vinculada
SELECT 
    idcli,
    nombrecli,
    email,
    telefonocli
FROM clientes
WHERE telegram_chat_id = {{ $json.chat_id }}
LIMIT 1;
```

**Configuración en N8N:**
```json
{
  "name": "check_registered",
  "description": "Verifica si el usuario ya tiene una cuenta de Streamify vinculada a su Telegram. Devuelve los datos del cliente si existe. ÚSALA al inicio de la conversación.",
  "type": "mysql",
  "query": "SELECT idcli, nombrecli, email, telefonocli FROM clientes WHERE telegram_chat_id = :chat_id LIMIT 1",
  "parameters": {
    "chat_id": "number"
  }
}
```

#### Tool 5: validate_credentials (Validar Login)

```sql
-- Query para validar email y contraseña
SELECT 
    idcli,
    nombrecli,
    email,
    password
FROM clientes
WHERE email = '{{ $json.email }}'
LIMIT 1;
```

**Configuración en N8N:**
```json
{
  "name": "validate_credentials",
  "description": "Obtiene los datos del cliente por email para validar contraseña. Devuelve: idcli, nombrecli, email, password (hash bcrypt). Después debes validar el password con bcrypt en un Code node.",
  "type": "mysql",
  "query": "SELECT idcli, nombrecli, email, password FROM clientes WHERE email = :email LIMIT 1",
  "parameters": {
    "email": "string"
  }
}
```

#### Tool 6: check_email_exists (Verificar Email)

```sql
-- Query para verificar si un email ya existe
SELECT COUNT(*) as existe
FROM clientes
WHERE email = '{{ $json.email }}'
LIMIT 1;
```

**Configuración en N8N:**
```json
{
  "name": "check_email_exists",
  "description": "Verifica si un email ya está registrado. Devuelve existe: 1 (sí existe) o 0 (no existe).",
  "type": "mysql",
  "query": "SELECT COUNT(*) as existe FROM clientes WHERE email = :email LIMIT 1",
  "parameters": {
    "email": "string"
  }
}
```

#### Tool 7: create_cliente (Crear Cliente)

```sql
-- Query para crear nuevo cliente y vincular Telegram
INSERT INTO clientes (
    nombrecli,
    email,
    password,
    telefonocli,
    telegram_chat_id,
    pais,
    saldo,
    created_at,
    updated_at
)
VALUES (
    '{{ $json.nombre }}',
    '{{ $json.email }}',
    '{{ $json.password_hash }}',
    '{{ $json.telefono }}',
    {{ $json.chat_id }},
    'Ecuador',
    0.00,
    NOW(),
    NOW()
);

SELECT LAST_INSERT_ID() as idcli;
```

**Configuración en N8N:**
```json
{
  "name": "create_cliente",
  "description": "Crea un nuevo cliente en la base de datos. Parámetros: nombre, email, password_hash (ya encriptado con bcrypt), telefono, chat_id. IMPORTANTE: El password debe estar encriptado ANTES de llamar esta tool.",
  "type": "mysql",
  "query": "INSERT INTO clientes (nombrecli, email, password, telefonocli, telegram_chat_id, pais, saldo, created_at, updated_at) VALUES (:nombre, :email, :password_hash, :telefono, :chat_id, 'Ecuador', 0.00, NOW(), NOW()); SELECT LAST_INSERT_ID() as idcli",
  "parameters": {
    "nombre": "string",
    "email": "string",
    "password_hash": "string",
    "telefono": "string",
    "chat_id": "number"
  }
}
```

#### Tool 8: link_telegram (Vincular Telegram a Cliente Existente)

```sql
-- Query para vincular Telegram a un cliente existente
UPDATE clientes
SET telegram_chat_id = {{ $json.chat_id }},
    updated_at = NOW()
WHERE idcli = {{ $json.cliente_id }}
AND telegram_chat_id IS NULL;
```

**Configuración en N8N:**
```json
{
  "name": "link_telegram",
  "description": "Vincula un chat_id de Telegram a un cliente existente después de login exitoso. Parámetros: cliente_id, chat_id.",
  "type": "mysql",
  "query": "UPDATE clientes SET telegram_chat_id = :chat_id, updated_at = NOW() WHERE idcli = :cliente_id AND telegram_chat_id IS NULL",
  "parameters": {
    "cliente_id": "number",
    "chat_id": "number"
  }
}
```

### Prompt del Sistema para el Agente IA (VERSIÓN ENRIQUECIDA)

```
Eres el asistente de AUTENTICACIÓN de Streamify Bot.

Tienes acceso a una MEMORIA PERSISTENTE en MySQL (tabla telegram_auth_sessions) y a una GUÍA DE PASOS (tabla steps).
La memoria guarda el estado de la conversación. La guía de pasos te dice exactamente qué hacer en cada paso.

=== TOOLS MYSQL DISPONIBLES ===

MEMORIA:
0. get_step_instructions - Lee las instrucciones detalladas del paso actual (¡ÚSALO SIEMPRE!)
1. get_memory - Lee el estado actual del usuario (step, proceso, datos, intentos)
2. update_memory - Guarda/actualiza el estado después de cada interacción (OBLIGATORIO)
3. delete_memory - Elimina la sesión cuando auth completa

VALIDACIONES Y DATOS:
4. validar_credenciales - Obtiene datos del cliente por email (para login)
5. check_email_exists - Verifica si un email ya existe (para registro)

OPERACIONES:
6. Registrar_cliente - Crea nuevo cliente en la BD
7. Update_telegram_chat_id - Vincula telegram_chat_id a cliente existente

UTILIDADES:
8. Think - Te ayuda a pensar y planear
9. Calculator - Realiza cálculos si es necesario
10. Date - Obtiene la fecha actual

=== FLUJO DE TRABAJO ===

PARA CADA MENSAJE DEL USUARIO:

1. 🔍 get_memory(chat_id) 
   - Obtiene: step actual, proceso, datos, intentos

2. 📖 get_step_instructions(step_actual)
   - Lee las instrucciones COMPLETAS del paso
   - La columna "description" es tu SUBPROMPT con todo lo que necesitas:
     * Qué pregunta hacer
     * Cómo validar la respuesta
     * Qué datos guardar
     * A qué paso ir siguiente

3. 🤔 Think (opcional)
   - Usa este tool si necesitas planear tu respuesta

4. ✅ Ejecuta las validaciones según las instrucciones del paso
   - Usa tools como validar_credenciales, check_email_exists, etc.

5. 💾 update_memory(chat_id, nuevo_step, proceso, datos, intentos)
   - SIEMPRE antes de responder al usuario
   - Guarda: nuevo paso, datos actualizados, mensaje bot, mensaje usuario

6. 💬 Responde al usuario
   - Usa el mensaje especificado en las instrucciones del paso
   - Agrega emojis para ser amigable

7. 🗑️ delete_memory (SOLO si auth completada)
   - Cuando step = 'completado' y autenticación exitosa

=== ESTRUCTURA DE PASOS ===

FLUJO LOGIN:
inicio → login_email → login_password → completado

FLUJO REGISTRO:
inicio → registro_nombre → registro_email → registro_telefono 
→ registro_password → registro_confirmar → completado

=== EJEMPLO DE USO ===

Usuario: "hola"

1. get_memory(123456789) → No existe o expirada
2. get_step_instructions('inicio') → Devuelve description completo con:
   - "Pregunta al usuario si ya tiene cuenta"
   - "Mensaje: 👋 ¡Hola! ¿Ya tienes cuenta? (SI/NO)"
   - "Si SI → step=login_email, proceso=login"
   - "Si NO → step=registro_nombre, proceso=registro"
3. update_memory(123456789, 'inicio', null, '{}', 0)
4. Responder: "👋 ¡Hola! ¿Ya tienes cuenta? (SI/NO)"

Usuario: "SI"

1. get_memory(123456789) → {step: 'inicio', proceso: null}
2. get_step_instructions('inicio') → Lee que SI significa ir a login
3. update_memory(123456789, 'login_email', 'login', '{}', 0)
4. Responder: "📧 Perfecto. Ingresa tu email:"

Usuario: "juan@test.com"

1. get_memory(123456789) → {step: 'login_email', proceso: 'login'}
2. get_step_instructions('login_email') → Lee validaciones de email
3. Validar formato email (contiene @)
4. update_memory(123456789, 'login_password', 'login', '{"email":"juan@test.com"}', 0)
5. Responder: "🔐 Ahora ingresa tu contraseña:"

... y así sucesivamente siguiendo las instrucciones de cada paso.

=== REGLAS CRÍTICAS ===

✅ SIEMPRE usa get_step_instructions para cada paso - contiene TODO lo que necesitas
✅ SIEMPRE usa get_memory al inicio para saber dónde está el usuario
✅ SIEMPRE usa update_memory antes de responder al usuario
✅ SIGUE las instrucciones del paso al pie de la letra
✅ Valida formatos y datos según las instrucciones
✅ Maneja intentos fallidos (máximo 3 en login)
✅ Usa emojis para respuestas amigables
✅ Elimina memoria con delete_memory cuando auth completa

❌ NO inventes instrucciones - siempre consulta get_step_instructions
❌ NO pierdas el contexto - guarda todo en datos JSON
❌ NO olvides actualizar memoria antes de responder
❌ NO permitas más de 3 intentos de login
❌ NO saltes pasos - sigue el flujo definido

=== TIPS IMPORTANTES ===

- El campo "description" en steps es tu BIBLIA para cada paso
- Contiene mensajes exactos, validaciones y lógica completa
- Si tienes dudas sobre qué hacer, consulta get_step_instructions
- La tabla steps hace que el flujo sea mantenible y claro
- Puedes usar Think para planear respuestas complejas
```

### Ventajas de Usar MySQL Directamente

✅ **Control Total**: El agente tiene acceso directo a la memoria  
✅ **Sin Intermediarios**: No necesita API REST, más rápido  
✅ **Flexibilidad**: Puede hacer queries complejas si es necesario  
✅ **Memoria Persistente**: Sobrevive a reinicios de N8N  
✅ **Escalable**: Base de datos optimizada para múltiples usuarios  
✅ **Debugging Fácil**: Puedes consultar la tabla directamente en MySQL  

## API Endpoints

Base URL: `https://streamify.aaronsoft.es/api/v1/telegram`

### 1. Verificar si Cliente está Registrado

**Endpoint:** `POST /check-registered`

**Body:**
```json
{
  "chat_id": 123456789
}
```

**Respuesta:**
```json
{
  "exito": true,
  "registrado": true,
  "cliente": {
    "id": 1,
    "nombre": "Juan Pérez",
    "email": "juan@example.com"
  }
}
```

### 2. Obtener Sesión Actual

**Endpoint:** `POST /get-session`

**Body:**
```json
{
  "chat_id": 123456789
}
```

**Respuesta:**
```json
{
  "exito": true,
  "sesion": {
    "chat_id": 123456789,
    "step": "login_email",
    "proceso": "login",
    "datos": {
      "email": "juan@example.com"
    },
    "intentos": 0,
    "expirada": false
  }
}
```

### 3. Procesar Entrada del Usuario (PRINCIPAL)

**Endpoint:** `POST /process-input`

Este es el endpoint principal que debes usar en N8N. Procesa automáticamente el paso actual y devuelve la respuesta apropiada.

**Body:**
```json
{
  "chat_id": 123456789,
  "message": "SI"
}
```

**Respuesta:**
```json
{
  "exito": true,
  "mensaje": "📧 Perfecto. Por favor ingresa tu email de registro:",
  "paso_siguiente": "login_email"
}
```

**Respuesta cuando auth completa:**
```json
{
  "exito": true,
  "mensaje": "✅ ¡Perfecto! Tu cuenta ha sido vinculada exitosamente.\n\nBienvenido/a Juan Pérez 🎉",
  "paso_siguiente": "completado",
  "auth_complete": true,
  "cliente": {
    "id": 1,
    "nombre": "Juan Pérez",
    "email": "juan@example.com"
  }
}
```

### 4. Validar Credenciales

**Endpoint:** `POST /validate-credentials`

**Body:**
```json
{
  "email": "juan@example.com",
  "password": "password123"
}
```

**Respuesta:**
```json
{
  "exito": true,
  "mensaje": "Credenciales válidas",
  "cliente": {
    "id": 1,
    "nombre": "Juan Pérez",
    "email": "juan@example.com"
  }
}
```

### 5. Verificar Email Existente

**Endpoint:** `POST /check-email`

**Body:**
```json
{
  "email": "juan@example.com"
}
```

**Respuesta:**
```json
{
  "exito": true,
  "existe": true
}
```

### 6. Crear Cliente

**Endpoint:** `POST /create-cliente`

**Body:**
```json
{
  "chat_id": 123456789,
  "nombre": "Juan Pérez",
  "email": "juan@example.com",
  "telefono": "0987654321",
  "password": "password123"
}
```

**Respuesta:**
```json
{
  "exito": true,
  "mensaje": "Cliente creado exitosamente",
  "cliente": {
    "id": 1,
    "nombre": "Juan Pérez",
    "email": "juan@example.com"
  }
}
```

### 7. Vincular Telegram a Cliente

**Endpoint:** `POST /link-telegram`

**Body:**
```json
{
  "cliente_id": 1,
  "chat_id": 123456789
}
```

**Respuesta:**
```json
{
  "exito": true,
  "mensaje": "Telegram vinculado exitosamente"
}
```

### 8. Reiniciar Sesión

**Endpoint:** `POST /reset-session`

**Body:**
```json
{
  "chat_id": 123456789
}
```

**Respuesta:**
```json
{
  "exito": true,
  "mensaje": "Sesión reiniciada"
}
```

### 9. Eliminar Sesión

**Endpoint:** `DELETE /delete-session`

**Body:**
```json
{
  "chat_id": 123456789
}
```

**Respuesta:**
```json
{
  "exito": true,
  "mensaje": "Sesión eliminada"
}
```

### 10. Limpiar Sesiones Expiradas

**Endpoint:** `POST /clean-sessions`

**Body:** Ninguno

**Respuesta:**
```json
{
  "exito": true,
  "mensaje": "Se eliminaron 5 sesiones expiradas",
  "eliminadas": 5
}
```

## Flujo N8N con Agente IA

### Opción 1: Con AI Agent (Recomendado)

```
1. Telegram Webhook Trigger
   ↓
2. AI Agent Node
   - System Message: [Ver prompt arriba]
   - Tools: [check_registered, process_input, etc.]
   - Input: {{ $json.message.text }}
   - Variables: { chat_id: {{ $json.message.chat.id }} }
   ↓
3. IF Node: ¿auth_complete en respuesta?
   ├─ SI → Continuar al flujo principal
   │
   └─ NO → 
      ↓
      4. Send Telegram Message
         Text: {{ $json.output }}
         ↓
      END
```

**Ventajas:**
- El agente gestiona automáticamente el flujo
- Puede manejar conversaciones complejas
- Responde de forma natural
- Usa las tools cuando necesita acceder/actualizar memoria

### Opción 2: Sin AI Agent (Directo)

```Implementación Detallada en N8N

#### Configuración del AI Agent Node

```javascript
// System Message
const systemMessage = `Eres el asistente de autenticación de Streamify Bot.

Tu trabajo es ayudar a los usuarios a vincular su cuenta de Telegram con Streamify.

FLUJO:
1. Al recibir el primer mensaje, usa check_registered
2. Si NO está registrado, usa process_input con cada mensaje
3. Cuando recibas auth_complete: true, el usuario está autenticado

IMPORTANTE:
- USA process_input para CADA mensaje del usuario durante auth
- NO inventes respuestas, siempre usa las tools
- El sistema gestiona el estado automáticamente
- Responde de forma amigable usando emojis`;

// Memory Configuration
const memoryConfig = {
  type: 'none', // No usar memoria de N8N
  reason: 'Usamos base de datos para memoria persistente'
};

// Chat ID como variable del agente
const chatId = $input.first().json.message.chat.id;
```

#### Code Node Alternativo (Sin AI Agent)

```javascript
// Obtener datos del webhook de Telegram
const chatId = $input.first().json.message.chat.id;
const messageText = $input.first().json.message.text;

// Verificar si está registrado
const checkRegisteredUrl = 'https://streamify.aaronsoft.es/api/v1/telegram/check-registered';
const checkResponse = await $http.post(checkRegisteredUrl, {
  chat_id: chatId
});

if (checkResponse.registrado) {
  // Usuario ya registrado, pasar al flujo principal
  return [{
    json: {
      auth_required: false,
      cliente: checkResponse.cliente,
      message_text: messageText,
      chat_id: chatId
    }
  }];
}

// Usuario no registrado, procesar autenticación
const processUrl = 'https://streamify.aaronsoft.es/api/v1/telegram/process-input';
const processResponse = await $http.post(processUrl, {
  chat_id: chatId,
  message: messageText
});

return [{
  json: {
    auth_required: true,
    auth_complete: processResponse.auth_complete || false,
    response_message: processResponse.mensaje,
    cliente: processResponse.cliente || null,
    chat_id: chatId
  }
}];
```

#### Configuración de Tools en N8N (JSON Export)

```json
{
  "tools": [
    {
      "name": "check_registered",
      "description": "Verifica si un usuario ya tiene cuenta vinculada",
      "schema": {
        "type": "object",
        "properties": {
          "chat_id": { "type": "number" }
        },
        "required": ["chat_id"]
      }
    },
    {
      "name": "process_input",
      "description": "Procesa entrada del usuario (USAR SIEMPRE durante auth)",
      "schema": {
        "type": "object",
        "properties": {
          "chat_id": { "type": "number" },
          "message": { "type": "string" }
        },
        "required": ["chat_id", "message"]
      }
    },
    {
      "name": "get_session",
      "description": "Obtiene estado actual de la sesión",
      "schema": {
        "type": "object",
        "properties": {
          "chat_id": { "type": "number" }
        },
        "required": ["chat_id"]
      }
    },
    {
      "name": "reset_session",
      "description": "Reinicia la sesión al inicio",
      "schema": {
        "type": "object",
        "properties": {
          "chat_id": { "type": "number" }
        },
        "required": ["chat_id"]
      }
    }
  ]
}
// Verificar si está registrado
const checkRegisteredUrl = 'https://streamify.aaronsoft.es/api/v1/telegram/check-registered';
const checkResponse = await $http.post(checkRegisteredUrl, {
  chat_id: chatId
});

if (checkResponse.registrado) {
  // Usuario ya registrado, pasar al flujo principal
  return [{
    json: {
      auth_required: false,
      cliente: checkResponse.cliente,
      message_text: messageText,
      chat_id: chatId
    }
  }];
}

// Usuario no registrado, procesar autenticación
const processUrl = 'https://streamify.aaronsoft.es/api/v1/telegram/process-input';
const processResponse = await $http.post(processUrl, {
  chat_id: chatId,
  message: messageText
});

return [{
  json: {
    auth_required: true,
    auth_complete: processResponse.auth_complete || false,
    response_message: processResponse.mensaje,
    cliente: processResponse.cliente || null,
    chat_id: chatId
  }
}];
```

## Ejemplos de Conversación

### Ejemplo 1: Login Exitoso

```
Usuario: hola
Bot: 👋 ¡Hola! Para usar Streamify Bot necesito vincular tu cuenta.

¿Ya tienes una cuenta en https://streamify.aaronsoft.es?

Por favor responde:
✅ SI (si ya tienes cuenta)
❌ NO (si necesitas crear cuenta)

Usuario: SI
Bot: 📧 Perfecto. Por favor ingresa tu email de registro:

Usuario: juan@example.com
Bot: 🔐 Ahora ingresa tu contraseña:

Usuario: password123
Bot: ✅ ¡Perfecto! Tu cuenta ha sido vinculada exitosamente.

Bienvenido/a Juan Pérez 🎉
```

### Ejemplo 2: Registro Nuevo Cliente

```
Usuario: hola
Bot: 👋 ¡Hola! ¿Ya tienes cuenta? (SI/NO)

Usuario: NO
Bot: 📝 ¡Perfecto! Vamos a crear tu cuenta. ¿Cuál es tu nombre completo?

Usuario: María González
Bot: 📧 Gracias. Ahora, ¿cuál es tu email?

Usuario: maria@example.com
Bot: 📱 ¿Cuál es tu número de teléfono?

Usuario: 0987654321
Bot: 🔐 Perfecto. Ahora crea una contraseña (mínimo 6 caracteres):

Usuario: miPass123
Bot: 📋 Por favor confirma tus datos:

👤 Nombre: María González
📧 Email: maria@example.com
📱 Teléfono: 0987654321
🔐 Contraseña: *** (oculta)

¿Todo está correcto? Responde SI o NO

Usuario: SI
Bot: 🎉 ¡Cuenta creada y vinculada exitosamente!

Bienvenido/a María González 🎉
```

## Comando Artisan

### Limpiar Sesiones Expiradas

Ejecutar manualmente:
```bash
php artisan telegram:clean-sessions
```

Programar en cron (ejecutar cada hora):
```php
// En app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('telegram:clean-sessions')->hourly();
}
```

## Modelo Eloquent

### TelegramAuthSession

```php
use App\Models\TelegramAuthSession;

// Obtener o crear sesión
$session = TelegramAuthSession::obtenerOCrear($chatId);

// Actualizar estado
$session->actualizarEstado('login_email', 'login');

// Actualizar datos
$session->actualizarDatos(['email' => 'user@example.com']);

// Verificar si está expirada
if ($session->estaExpirada()) {
    $session->reiniciar();
}

// Eliminar sesión
$session->delete();
```

### Cliente

```php
use App\Models\Cliente;

// Buscar por telegram_chat_id
$cliente = Cliente::buscarPorTelegram($chatId);

// Vincular Telegram
$cliente->vincularTelegram($chatId);

// Verificar vinculación
if ($cliente->tieneTelegramVinculado()) {
    // Cliente tiene Telegram vinculado
}
```

## Servicio TelegramAuthService

```php
use App\Services\TelegramAuthService;

$authService = new TelegramAuthService();

// Verificar si está registrado
$cliente = $authService->clienteEstaRegistrado($chatId);

// Procesar paso actual
$resultado = $authService->procesarPaso($chatId, $paso, $entrada);

// Validar credenciales
$resultado = $authService->validarCredenciales($email, $password);

// Crear cliente
$resultado = $authService->crearCliente([
    'nombre' => 'Juan Pérez',
    'email' => 'juan@example.com',
    'telefono' => '0987654321',
    'password' => 'password123',
], $chatId);
```

## Seguridad

- Las contraseñas se encriptan automáticamente con bcrypt
- Las sesiones expiran después de 10 minutos de inactividad
- Se limitan los intentos de login a 3 por sesión
- El campo `telegram_chat_id` es único en la tabla clientes

## Mantenimiento

### Limpieza Automática
Las sesiones expiradas se pueden limpiar automáticamente ejecutando el comando:

```bash
php artisan telegram:clean-sessions
```

Recomendado: Programar este comando para ejecutarse cada hora en el cron.

### Monitoreo
Puedes consultar las sesiones activas con:

```php
use App\Models\TelegramAuthSession;

// Sesiones activas
$activas = TelegramAuthSession::activas()->count();

// Sesiones expiradas
$expiradas = TelegramAuthSession::expiradas()->count();
```

## Testing

### Prueba con cURL

```bash
# Verificar si está registrado
curl -X POST https://streamify.aaronsoft.es/api/v1/telegram/check-registered \
  -H "Content-Type: application/json" \
  -d '{"chat_id": 123456789}'

# Procesar entrada
curl -X POST https://streamify.aaronsoft.es/api/v1/telegram/process-input \
  -H "Content-Type: application/json" \
  -d '{"chat_id": 123456789, "message": "SI"}'
```

## Migraciones

### Ejecutar Migraciones

```bash
# Ejecutar migración de telegram_auth_sessions
php artisan migrate

# Rollback si es necesario
php artisan migrate:rollback
```

### Orden de Ejecución
1. `2026_01_07_093645_update_clientes_table.php` - Agrega telegram_chat_id a clientes
2. `2026_01_07_171447_create_telegram_auth_sessions_table.php` - Crea tabla de sesiones

## Notas Importantes

1. **La sesión se elimina automáticamente** cuando la autenticación se completa exitosamente
2. **Las sesiones expiran** después de 10 minutos sin actividad
3. **El endpoint /process-input es inteligente** - procesa automáticamente el paso actual basándose en el estado de la sesión
4. **No necesitas gestionar el estado manualmente** - el sistema lo hace automáticamente
5. **El chat_id de Telegram se vincula automáticamente** al cliente durante el registro o login

## Soporte

Para más información o problemas, revisar:
- Modelo: `App\Models\TelegramAuthSession`
- Servicio: `App\Services\TelegramAuthService`
- Controlador: `App\Http\Controllers\Api\TelegramAuthController`
- Documentación del chat: `docs/18 - Agente IA auth.md`
