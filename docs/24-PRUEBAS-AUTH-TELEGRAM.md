# Script de Prueba - Sistema Auth Telegram

## 🧪 Pruebas con cURL

Este documento contiene scripts de prueba para verificar el funcionamiento del sistema de autenticación de Telegram.

---

## 📋 Variables de Entorno

Configura estas variables antes de ejecutar los scripts:

```bash
# PowerShell
$BASE_URL = "https://streamify.aaronsoft.es/api/telegram"
$TEST_CHAT_ID = 123456789

# Bash/Linux
export BASE_URL="https://streamify.aaronsoft.es/api/telegram"
export TEST_CHAT_ID=123456789
```

---

## 🔄 Flujo de Prueba Completo

### 1. Verificar si el chat_id está registrado

```bash
# PowerShell
$response = Invoke-RestMethod -Uri "$BASE_URL/check-registered" `
  -Method Post `
  -ContentType "application/json" `
  -Body (@{chat_id = $TEST_CHAT_ID} | ConvertTo-Json)

Write-Host "Registrado: $($response.registrado)" -ForegroundColor Yellow

# cURL (Linux/Git Bash)
curl -X POST "$BASE_URL/check-registered" \
  -H "Content-Type: application/json" \
  -d "{\"chat_id\": $TEST_CHAT_ID}"
```

**Respuesta esperada:**
```json
{
  "exito": true,
  "registrado": false,
  "cliente": null
}
```

---

### 2. Iniciar conversación - "hola"

```bash
# PowerShell
$response = Invoke-RestMethod -Uri "$BASE_URL/process-input" `
  -Method Post `
  -ContentType "application/json" `
  -Body (@{
    chat_id = $TEST_CHAT_ID
    message = "hola"
  } | ConvertTo-Json)

Write-Host $response.mensaje -ForegroundColor Green

# cURL
curl -X POST "$BASE_URL/process-input" \
  -H "Content-Type: application/json" \
  -d "{
    \"chat_id\": $TEST_CHAT_ID,
    \"message\": \"hola\"
  }"
```

**Respuesta esperada:**
```json
{
  "exito": true,
  "mensaje": "👋 ¡Hola! Para usar Streamify Bot...",
  "paso_siguiente": "inicio"
}
```

---

### 3. Ver estado de sesión actual

```bash
# PowerShell
$response = Invoke-RestMethod -Uri "$BASE_URL/get-session" `
  -Method Post `
  -ContentType "application/json" `
  -Body (@{chat_id = $TEST_CHAT_ID} | ConvertTo-Json)

Write-Host "Step actual: $($response.sesion.step)" -ForegroundColor Cyan
Write-Host "Proceso: $($response.sesion.proceso)" -ForegroundColor Cyan

# cURL
curl -X POST "$BASE_URL/get-session" \
  -H "Content-Type: application/json" \
  -d "{\"chat_id\": $TEST_CHAT_ID}"
```

**Respuesta esperada:**
```json
{
  "exito": true,
  "sesion": {
    "chat_id": 123456789,
    "step": "inicio",
    "proceso": null,
    "datos": {},
    "intentos": 0,
    "expirada": false
  }
}
```

---

## 🔐 Prueba de Login Completo

### Script PowerShell

```powershell
# Variables
$BASE_URL = "https://streamify.aaronsoft.es/api/telegram"
$TEST_CHAT_ID = 999999999  # Usa un chat_id de prueba
$TEST_EMAIL = "test@ejemplo.com"
$TEST_PASSWORD = "test123"

Write-Host "=== PRUEBA DE LOGIN ===" -ForegroundColor Yellow

# 1. Iniciar: "hola"
Write-Host "`n1. Enviando 'hola'..." -ForegroundColor Cyan
$r1 = Invoke-RestMethod -Uri "$BASE_URL/process-input" -Method Post `
  -ContentType "application/json" `
  -Body (@{chat_id = $TEST_CHAT_ID; message = "hola"} | ConvertTo-Json)
Write-Host "Bot: $($r1.mensaje)" -ForegroundColor Green

# 2. Responder: "SI"
Write-Host "`n2. Enviando 'SI' (tengo cuenta)..." -ForegroundColor Cyan
$r2 = Invoke-RestMethod -Uri "$BASE_URL/process-input" -Method Post `
  -ContentType "application/json" `
  -Body (@{chat_id = $TEST_CHAT_ID; message = "SI"} | ConvertTo-Json)
Write-Host "Bot: $($r2.mensaje)" -ForegroundColor Green

# 3. Ingresar email
Write-Host "`n3. Enviando email..." -ForegroundColor Cyan
$r3 = Invoke-RestMethod -Uri "$BASE_URL/process-input" -Method Post `
  -ContentType "application/json" `
  -Body (@{chat_id = $TEST_CHAT_ID; message = $TEST_EMAIL} | ConvertTo-Json)
Write-Host "Bot: $($r3.mensaje)" -ForegroundColor Green

# 4. Ingresar password
Write-Host "`n4. Enviando password..." -ForegroundColor Cyan
$r4 = Invoke-RestMethod -Uri "$BASE_URL/process-input" -Method Post `
  -ContentType "application/json" `
  -Body (@{chat_id = $TEST_CHAT_ID; message = $TEST_PASSWORD} | ConvertTo-Json)
Write-Host "Bot: $($r4.mensaje)" -ForegroundColor Green

# Verificar resultado
if ($r4.auth_complete -eq $true) {
    Write-Host "`n✅ LOGIN EXITOSO!" -ForegroundColor Green
    Write-Host "Cliente: $($r4.cliente.nombre)" -ForegroundColor Yellow
} else {
    Write-Host "`n❌ Login falló" -ForegroundColor Red
}
```

---

## 📝 Prueba de Registro Completo

### Script PowerShell

```powershell
# Variables
$BASE_URL = "https://streamify.aaronsoft.es/api/telegram"
$TEST_CHAT_ID = 888888888  # Usa otro chat_id
$TEST_NOMBRE = "Usuario Prueba"
$TEST_EMAIL = "nuevousuario@ejemplo.com"
$TEST_TELEFONO = "+593 987654321"
$TEST_PASSWORD = "secure123"

Write-Host "=== PRUEBA DE REGISTRO ===" -ForegroundColor Yellow

# 1. Iniciar
$r1 = Invoke-RestMethod -Uri "$BASE_URL/process-input" -Method Post `
  -ContentType "application/json" `
  -Body (@{chat_id = $TEST_CHAT_ID; message = "hola"} | ConvertTo-Json)
Write-Host "`n1. Bot: $($r1.mensaje)" -ForegroundColor Green

# 2. NO tengo cuenta
$r2 = Invoke-RestMethod -Uri "$BASE_URL/process-input" -Method Post `
  -ContentType "application/json" `
  -Body (@{chat_id = $TEST_CHAT_ID; message = "NO"} | ConvertTo-Json)
Write-Host "`n2. Bot: $($r2.mensaje)" -ForegroundColor Green

# 3. Nombre
$r3 = Invoke-RestMethod -Uri "$BASE_URL/process-input" -Method Post `
  -ContentType "application/json" `
  -Body (@{chat_id = $TEST_CHAT_ID; message = $TEST_NOMBRE} | ConvertTo-Json)
Write-Host "`n3. Bot: $($r3.mensaje)" -ForegroundColor Green

# 4. Email
$r4 = Invoke-RestMethod -Uri "$BASE_URL/process-input" -Method Post `
  -ContentType "application/json" `
  -Body (@{chat_id = $TEST_CHAT_ID; message = $TEST_EMAIL} | ConvertTo-Json)
Write-Host "`n4. Bot: $($r4.mensaje)" -ForegroundColor Green

# 5. Teléfono
$r5 = Invoke-RestMethod -Uri "$BASE_URL/process-input" -Method Post `
  -ContentType "application/json" `
  -Body (@{chat_id = $TEST_CHAT_ID; message = $TEST_TELEFONO} | ConvertTo-Json)
Write-Host "`n5. Bot: $($r5.mensaje)" -ForegroundColor Green

# 6. Password
$r6 = Invoke-RestMethod -Uri "$BASE_URL/process-input" -Method Post `
  -ContentType "application/json" `
  -Body (@{chat_id = $TEST_CHAT_ID; message = $TEST_PASSWORD} | ConvertTo-Json)
Write-Host "`n6. Bot: $($r6.mensaje)" -ForegroundColor Green

# 7. Confirmar
$r7 = Invoke-RestMethod -Uri "$BASE_URL/process-input" -Method Post `
  -ContentType "application/json" `
  -Body (@{chat_id = $TEST_CHAT_ID; message = "SI"} | ConvertTo-Json)
Write-Host "`n7. Bot: $($r7.mensaje)" -ForegroundColor Green

if ($r7.auth_complete -eq $true) {
    Write-Host "`n✅ REGISTRO EXITOSO!" -ForegroundColor Green
    Write-Host "Cliente ID: $($r7.cliente.id)" -ForegroundColor Yellow
    Write-Host "Nombre: $($r7.cliente.nombre)" -ForegroundColor Yellow
}
```

---

## 🧹 Limpieza de Pruebas

### Eliminar sesión de prueba

```bash
# PowerShell
Invoke-RestMethod -Uri "$BASE_URL/delete-session" `
  -Method Delete `
  -ContentType "application/json" `
  -Body (@{chat_id = $TEST_CHAT_ID} | ConvertTo-Json)

# cURL
curl -X DELETE "$BASE_URL/delete-session" \
  -H "Content-Type: application/json" \
  -d "{\"chat_id\": $TEST_CHAT_ID}"
```

### Reiniciar sesión (sin eliminar)

```bash
# PowerShell
Invoke-RestMethod -Uri "$BASE_URL/reset-session" `
  -Method Post `
  -ContentType "application/json" `
  -Body (@{chat_id = $TEST_CHAT_ID} | ConvertTo-Json)

# cURL
curl -X POST "$BASE_URL/reset-session" \
  -H "Content-Type: application/json" \
  -d "{\"chat_id\": $TEST_CHAT_ID}"
```

---

## 🔍 Verificaciones en Base de Datos

### Ver sesiones activas

```sql
SELECT 
    chat_id,
    step,
    proceso,
    JSON_EXTRACT(datos, '$.email') as email,
    intentos,
    last_activity
FROM telegram_auth_sessions
ORDER BY last_activity DESC;
```

### Ver clientes con Telegram vinculado

```sql
SELECT 
    idcli,
    nombrecli,
    email,
    telegram_chat_id,
    created_at
FROM clientes
WHERE telegram_chat_id IS NOT NULL
ORDER BY created_at DESC
LIMIT 10;
```

### Desvincular Telegram de un cliente (pruebas)

```sql
-- Para volver a probar el login
UPDATE clientes 
SET telegram_chat_id = NULL 
WHERE telegram_chat_id = 123456789;
```

---

## 📊 Pruebas de Validación

### Email inválido

```bash
# PowerShell
$r = Invoke-RestMethod -Uri "$BASE_URL/process-input" -Method Post `
  -ContentType "application/json" `
  -Body (@{chat_id = $TEST_CHAT_ID; message = "emailinvalido"} | ConvertTo-Json)

Write-Host $r.mensaje  # Debe mostrar error de formato
```

### Contraseña muy corta

```bash
# En step registro_password, enviar password de < 6 caracteres
$r = Invoke-RestMethod -Uri "$BASE_URL/process-input" -Method Post `
  -ContentType "application/json" `
  -Body (@{chat_id = $TEST_CHAT_ID; message = "123"} | ConvertTo-Json)

# Debe rechazar y pedir mínimo 6 caracteres
```

### Intentos fallidos de login

```bash
# Enviar 3 passwords incorrectas seguidas
# La 3ra debe reiniciar la sesión
```

---

## ⚡ Script de Prueba Completa

### test-auth.ps1

```powershell
param(
    [string]$BaseUrl = "https://streamify.aaronsoft.es/api/telegram",
    [int]$ChatId = (Get-Random -Minimum 100000000 -Maximum 999999999)
)

Write-Host "=== TEST SISTEMA AUTH TELEGRAM ===" -ForegroundColor Yellow
Write-Host "Chat ID: $ChatId" -ForegroundColor Cyan
Write-Host "Base URL: $BaseUrl`n" -ForegroundColor Cyan

function Send-Message {
    param([string]$Message)
    
    $body = @{
        chat_id = $ChatId
        message = $Message
    } | ConvertTo-Json
    
    $response = Invoke-RestMethod -Uri "$BaseUrl/process-input" `
        -Method Post `
        -ContentType "application/json" `
        -Body $body
    
    Write-Host "Usuario: $Message" -ForegroundColor Blue
    Write-Host "Bot: $($response.mensaje)" -ForegroundColor Green
    Write-Host ""
    
    return $response
}

# Test 1: Verificar estado inicial
Write-Host "TEST 1: Verificar no registrado" -ForegroundColor Yellow
$check = Invoke-RestMethod -Uri "$BaseUrl/check-registered" `
    -Method Post `
    -ContentType "application/json" `
    -Body (@{chat_id = $ChatId} | ConvertTo-Json)

if ($check.registrado -eq $false) {
    Write-Host "✅ Chat ID no registrado (correcto)" -ForegroundColor Green
} else {
    Write-Host "❌ Chat ID ya registrado" -ForegroundColor Red
}
Write-Host ""

# Test 2: Flujo de registro
Write-Host "TEST 2: Flujo de registro completo" -ForegroundColor Yellow
$r1 = Send-Message "hola"
$r2 = Send-Message "NO"
$r3 = Send-Message "Test User $ChatId"
$r4 = Send-Message "test$ChatId@ejemplo.com"
$r5 = Send-Message "+593 987654321"
$r6 = Send-Message "password123"
$r7 = Send-Message "SI"

if ($r7.auth_complete -eq $true) {
    Write-Host "✅ Registro completado exitosamente" -ForegroundColor Green
    Write-Host "Cliente ID: $($r7.cliente.id)" -ForegroundColor Yellow
} else {
    Write-Host "❌ Registro falló" -ForegroundColor Red
}
Write-Host ""

# Test 3: Verificar ahora registrado
Write-Host "TEST 3: Verificar ahora registrado" -ForegroundColor Yellow
$check2 = Invoke-RestMethod -Uri "$BaseUrl/check-registered" `
    -Method Post `
    -ContentType "application/json" `
    -Body (@{chat_id = $ChatId} | ConvertTo-Json)

if ($check2.registrado -eq $true) {
    Write-Host "✅ Chat ID ahora registrado (correcto)" -ForegroundColor Green
} else {
    Write-Host "❌ Chat ID sigue sin registrar" -ForegroundColor Red
}

Write-Host "`n=== FIN DE PRUEBAS ===" -ForegroundColor Yellow
```

**Uso:**
```powershell
# Ejecutar con chat_id aleatorio
.\test-auth.ps1

# Ejecutar con chat_id específico
.\test-auth.ps1 -ChatId 123456789

# Ejecutar en URL diferente
.\test-auth.ps1 -BaseUrl "http://localhost/api/telegram"
```

---

## 📈 Métricas de Éxito

Una prueba exitosa debe cumplir:

✅ API responde en < 500ms  
✅ Validaciones de formato funcionan  
✅ Flujo de login completo exitoso  
✅ Flujo de registro completo exitoso  
✅ Sesión se elimina al completar auth  
✅ telegram_chat_id se vincula correctamente  
✅ Cliente puede ser consultado con chat_id  
✅ Intentos fallidos se controlan (máx 3)  
✅ Sesiones expiradas se limpian

---

**Última actualización:** 2026-01-08
