# Sistema de Autenticación Telegram - Lógica Iterativa

## 📋 Resumen

Este documento describe el sistema de autenticación de Telegram implementado con **lógica iterativa automática** (sin agente IA). El sistema maneja el flujo de registro/login mediante estados y respuestas automáticas.

---

## 🏗️ Arquitectura

### Componentes

1. **Tabla:** `telegram_auth_sessions`
2. **Model:** `TelegramAuthSession`
3. **Service:** `TelegramAuthService`
4. **Controller:** `TelegramAuthController`
5. **Rutas API:** `/api/telegram/*`

### Flujo General

```
Usuario Telegram → N8N Webhook → API Laravel → Respuesta automática
                                      ↓
                              telegram_auth_sessions
                                      ↓
                              Validación según step
                                      ↓
                              Vincular con clientes
```

---

## 📊 Tabla: telegram_auth_sessions

### Estructura

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigint | ID autoincremental |
| `chat_id` | bigint | ID único de chat de Telegram |
| `step` | varchar(50) | Paso actual del flujo |
| `proceso` | enum | 'login' o 'registro' |
| `datos` | json | Datos recolectados (email, nombre, telefono, password) |
| `intentos` | tinyint | Contador de intentos fallidos |
| `last_activity` | timestamp | Última actividad (para expiración) |

### Índices

- `UNIQUE(chat_id)` - Un chat_id solo puede tener una sesión activa
- `INDEX(last_activity)` - Para limpiar sesiones expiradas

---

## 🔄 Flujos de Autenticación

### Flujo de Login

```
1. inicio (¿Tienes cuenta?)
   → Usuario: SI
   
2. login_email (Ingresa email)
   → Usuario: juan@ejemplo.com
   → Validación: formato email válido
   
3. login_password (Ingresa contraseña)
   → Usuario: password123
   → Validación: credenciales correctas
   → Máximo 3 intentos
   
4. completado
   → Vincular telegram_chat_id al cliente
   → Eliminar sesión
   → ¡Bienvenido!
```

### Flujo de Registro

```
1. inicio (¿Tienes cuenta?)
   → Usuario: NO
   
2. registro_nombre (¿Tu nombre?)
   → Usuario: Juan Pérez
   → Validación: mínimo 3 caracteres
   
3. registro_email (¿Tu email?)
   → Usuario: juan@ejemplo.com
   → Validación: formato email + email único
   
4. registro_telefono (¿Tu teléfono?)
   → Usuario: +593 987654321
   → Validación: formato teléfono (7-20 caracteres)
   
5. registro_password (Crea contraseña)
   → Usuario: password123
   → Validación: mínimo 6 caracteres
   
6. registro_confirmar (¿Confirmar datos?)
   → Usuario: SI
   → Crear cliente + vincular telegram
   → Eliminar sesión
   → ¡Cuenta creada!
```

---

## 🔌 API Endpoints

### Base URL
```
https://streamify.aaronsoft.es/api/telegram
```

### 1. Verificar si está registrado

**Endpoint:** `POST /check-registered`

**Request:**
```json
{
  "chat_id": 123456789
}
```

**Response:**
```json
{
  "exito": true,
  "registrado": false,
  "cliente": null
}
```

### 2. Obtener sesión actual

**Endpoint:** `POST /get-session`

**Request:**
```json
{
  "chat_id": 123456789
}
```

**Response:**
```json
{
  "exito": true,
  "sesion": {
    "chat_id": 123456789,
    "step": "login_email",
    "proceso": "login",
    "datos": {
      "email": "juan@ejemplo.com"
    },
    "intentos": 0,
    "expirada": false
  }
}
```

### 3. Procesar entrada del usuario ⭐ (Principal)

**Endpoint:** `POST /process-input`

**Request:**
```json
{
  "chat_id": 123456789,
  "message": "Hola"
}
```

**Response (paso inicio):**
```json
{
  "exito": true,
  "mensaje": "👋 ¡Hola! Para usar Streamify Bot...\n\n¿Ya tienes cuenta?...",
  "paso_siguiente": "inicio"
}
```

**Response (login exitoso):**
```json
{
  "exito": true,
  "mensaje": "✅ ¡Perfecto! Tu cuenta ha sido vinculada...",
  "paso_siguiente": "completado",
  "auth_complete": true,
  "cliente": {
    "id": 123,
    "nombre": "Juan Pérez",
    "email": "juan@ejemplo.com"
  }
}
```

### 4. Validar credenciales

**Endpoint:** `POST /validate-credentials`

**Request:**
```json
{
  "email": "juan@ejemplo.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "exito": true,
  "mensaje": "Credenciales válidas",
  "cliente": {
    "id": 123,
    "nombre": "Juan Pérez",
    "email": "juan@ejemplo.com"
  }
}
```

### 5. Verificar email

**Endpoint:** `POST /check-email`

**Request:**
```json
{
  "email": "juan@ejemplo.com"
}
```

**Response:**
```json
{
  "exito": true,
  "existe": true
}
```

### 6. Crear cliente

**Endpoint:** `POST /create-cliente`

**Request:**
```json
{
  "chat_id": 123456789,
  "nombre": "Juan Pérez",
  "email": "juan@ejemplo.com",
  "telefono": "+593 987654321",
  "password": "password123"
}
```

**Response:**
```json
{
  "exito": true,
  "mensaje": "Cliente creado exitosamente",
  "cliente": {
    "id": 123,
    "nombre": "Juan Pérez",
    "email": "juan@ejemplo.com"
  }
}
```

### 7. Vincular Telegram a cliente existente

**Endpoint:** `POST /link-telegram`

**Request:**
```json
{
  "cliente_id": 123,
  "chat_id": 123456789
}
```

**Response:**
```json
{
  "exito": true,
  "mensaje": "Telegram vinculado exitosamente"
}
```

### 8. Reiniciar sesión

**Endpoint:** `POST /reset-session`

**Request:**
```json
{
  "chat_id": 123456789
}
```

**Response:**
```json
{
  "exito": true,
  "mensaje": "Sesión reiniciada"
}
```

### 9. Eliminar sesión

**Endpoint:** `DELETE /delete-session`

**Request:**
```json
{
  "chat_id": 123456789
}
```

**Response:**
```json
{
  "exito": true,
  "mensaje": "Sesión eliminada"
}
```

### 10. Limpiar sesiones expiradas

**Endpoint:** `POST /clean-sessions`

**Request:**
```
No requiere body
```

**Response:**
```json
{
  "exito": true,
  "mensaje": "Se eliminaron 5 sesiones expiradas",
  "eliminadas": 5
}
```

---

## 🤖 Integración con N8N

### Workflow Básico

```
┌─────────────────────┐
│ Webhook de Telegram │
└──────────┬──────────┘
           │
           ▼
    ┌──────────────┐
    │ Extraer datos│
    │ - chat_id    │
    │ - message    │
    └──────┬───────┘
           │
           ▼
┌──────────────────────┐
│ HTTP Request         │
│ POST /process-input  │
│ {                    │
│   chat_id: {{$json}} │
│   message: {{$json}} │
│ }                    │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Enviar respuesta     │
│ via Telegram API     │
└──────────────────────┘
```

### Nodo N8N: HTTP Request

**URL:**
```
https://streamify.aaronsoft.es/api/telegram/process-input
```

**Method:** `POST`

**Body:**
```json
{
  "chat_id": {{ $json.message.chat.id }},
  "message": {{ $json.message.text }}
}
```

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

### Nodo N8N: Telegram Send Message

**Chat ID:**
```
{{ $json.message.chat.id }}
```

**Text:**
```
{{ $node["HTTP Request"].json.mensaje }}
```

---

## 📝 Ejemplos de Conversación

### Ejemplo 1: Login Exitoso

```
Usuario: hola
Bot: 👋 ¡Hola! Para usar Streamify Bot...
     ¿Ya tienes cuenta?
     ✅ SI (si ya tienes cuenta)
     ❌ NO (si necesitas crear cuenta)

Usuario: si
Bot: 📧 Perfecto. Por favor ingresa tu email de registro:

Usuario: juan@ejemplo.com
Bot: 🔐 Ahora ingresa tu contraseña:

Usuario: password123
Bot: ✅ ¡Perfecto! Tu cuenta ha sido vinculada exitosamente.
     Bienvenido/a Juan Pérez 🎉
```

### Ejemplo 2: Login con Intentos Fallidos

```
Usuario: si
Bot: 📧 Perfecto. Por favor ingresa tu email de registro:

Usuario: juan@ejemplo.com
Bot: 🔐 Ahora ingresa tu contraseña:

Usuario: incorrecta
Bot: ❌ Contraseña incorrecta. Intento 1/3.
     🔐 Por favor ingresa tu contraseña nuevamente:

Usuario: otra_incorrecta
Bot: ❌ Contraseña incorrecta. Intento 2/3.
     🔐 Por favor ingresa tu contraseña nuevamente:

Usuario: password123
Bot: ✅ ¡Perfecto! Tu cuenta ha sido vinculada exitosamente.
```

### Ejemplo 3: Registro Completo

```
Usuario: hola
Bot: ¿Ya tienes cuenta?

Usuario: no
Bot: 📝 ¡Perfecto! Vamos a crear tu cuenta.
     ¿Cuál es tu nombre completo?

Usuario: María García
Bot: 📧 Gracias María García. Ahora, ¿cuál es tu email?

Usuario: maria@ejemplo.com
Bot: 📱 ¿Cuál es tu número de teléfono?

Usuario: +593 987654321
Bot: 🔐 Perfecto. Ahora crea una contraseña (mínimo 6 caracteres):

Usuario: secure123
Bot: 📋 Por favor confirma tus datos:
     👤 Nombre: María García
     📧 Email: maria@ejemplo.com
     📱 Teléfono: +593 987654321
     🔐 Contraseña: *** (oculta)
     ¿Todo está correcto? Responde SI o NO

Usuario: si
Bot: 🎉 ¡Cuenta creada y vinculada exitosamente!
     ¡Bienvenido/a María García a Streamify! 🎉
```

---

## ✅ Validaciones Implementadas

### Validación de Email
- Formato válido: `filter_var($email, FILTER_VALIDATE_EMAIL)`
- Email único: verifica que no exista en BD

### Validación de Contraseña
- Mínimo 6 caracteres
- Se hashea con `Hash::make()` antes de guardar

### Validación de Teléfono
- Formato: 7-20 caracteres
- Permite: números, +, -, espacios, paréntesis

### Validación de Nombre
- Mínimo 3 caracteres

### Control de Intentos
- Máximo 3 intentos de login
- Después de 3 fallos: reinicia sesión

---

## 🔐 Seguridad

### Contraseñas
- Se hashean con `bcrypt` vía `Hash::make()`
- Nunca se muestran en respuestas API
- Solo se verifica con `Hash::check()`

### Sesiones
- Expiración automática después de inactividad
- Se eliminan automáticamente al completar auth
- Limpieza periódica de sesiones expiradas

### Validaciones
- Todos los inputs se validan con Laravel Validator
- Errores 400 para datos inválidos
- Sin exposición de información sensible

---

## 🧹 Mantenimiento

### Limpiar Sesiones Expiradas

**Opción 1: Manual via API**
```bash
curl -X POST https://streamify.aaronsoft.es/api/telegram/clean-sessions
```

**Opción 2: Comando Artisan (crear)**
```php
// php artisan make:command CleanTelegramSessions

protected $signature = 'telegram:clean-sessions';
protected $description = 'Limpiar sesiones de Telegram expiradas';

public function handle()
{
    $service = app(TelegramAuthService::class);
    $eliminadas = $service->limpiarSesionesExpiradas();
    $this->info("Se eliminaron {$eliminadas} sesiones expiradas");
}
```

**Opción 3: Cron Job**
```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    $schedule->command('telegram:clean-sessions')
             ->hourly();
}
```

---

## 🐛 Troubleshooting

### Cliente no puede hacer login

1. Verificar email existe: `POST /check-email`
2. Verificar password correcto en BD
3. Verificar intentos no hayan superado el límite
4. Resetear sesión: `POST /reset-session`

### Email ya registrado durante registro

- El sistema pregunta automáticamente si quiere hacer login
- Usuario debe responder SI/NO
- Si responde SI: cambia a flujo de login
- Si responde NO: puede ingresar otro email

### Sesión expirada

- Las sesiones se expiran automáticamente después de inactividad
- El sistema reinicia automáticamente sesiones expiradas
- Usuario debe comenzar de nuevo con "hola"

### Usuario no recibe respuestas

1. Verificar N8N webhook activo
2. Verificar extracción correcta de `chat_id` y `message`
3. Verificar API responde: test con curl
4. Verificar logs de Laravel: `storage/logs/laravel.log`

---

## 📦 Testing con cURL

### Verificar cliente registrado
```bash
curl -X POST https://streamify.aaronsoft.es/api/telegram/check-registered \
  -H "Content-Type: application/json" \
  -d '{"chat_id": 123456789}'
```

### Simular entrada de usuario
```bash
curl -X POST https://streamify.aaronsoft.es/api/telegram/process-input \
  -H "Content-Type: application/json" \
  -d '{
    "chat_id": 123456789,
    "message": "hola"
  }'
```

### Obtener sesión actual
```bash
curl -X POST https://streamify.aaronsoft.es/api/telegram/get-session \
  -H "Content-Type: application/json" \
  -d '{"chat_id": 123456789}'
```

---

## 📊 Monitoreo

### Métricas Importantes

- **Sesiones activas:** `SELECT COUNT(*) FROM telegram_auth_sessions`
- **Sesiones expiradas:** `SELECT COUNT(*) FROM telegram_auth_sessions WHERE last_activity < NOW() - INTERVAL 1 HOUR`
- **Clientes con Telegram:** `SELECT COUNT(*) FROM clientes WHERE telegram_chat_id IS NOT NULL`

### Queries de Diagnóstico

```sql
-- Ver sesiones activas
SELECT chat_id, step, proceso, intentos, last_activity
FROM telegram_auth_sessions
ORDER BY last_activity DESC;

-- Ver clientes vinculados
SELECT idcli, nombrecli, email, telegram_chat_id
FROM clientes
WHERE telegram_chat_id IS NOT NULL;

-- Ver sesiones con muchos intentos
SELECT *
FROM telegram_auth_sessions
WHERE intentos >= 2;
```

---

## 🎯 Conclusión

Este sistema proporciona un flujo de autenticación **robusto, automático y sin IA** para vincular cuentas de Telegram con clientes de Streamify.

### Ventajas del Enfoque Iterativo

✅ **Determinista:** Respuestas predecibles
✅ **Rápido:** Sin procesamiento IA
✅ **Confiable:** Sin dependencia de modelos externos
✅ **Fácil debug:** Logs claros y estados definidos
✅ **Bajo costo:** Sin llamadas a APIs de IA

### Próximos Pasos (Opcional)

- Implementar comandos avanzados (opción 2)
- Agregar confirmación por código SMS/Email
- Implementar 2FA para login
- Agregar métricas de uso
- Dashboard de sesiones activas

---

**Documentación generada:** 2026-01-08  
**Versión:** 1.0  
**Estado:** ✅ Implementado y funcional
