<#
.SYNOPSIS
Script de prueba rápida para el sistema de autenticación de Telegram

.DESCRIPTION
Ejecuta una prueba completa del flujo de registro verificando todos los endpoints

.EXAMPLE
.\test-auth-quick.ps1
#>

param(
    [string]$BaseUrl = "http://localhost/api/telegram",
    [int]$ChatId = (Get-Random -Minimum 100000000 -Maximum 999999999)
)

$ErrorActionPreference = "Stop"

Write-Host "`n╔═══════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║   PRUEBA RÁPIDA - Sistema Auth Telegram Streamify        ║" -ForegroundColor Cyan
Write-Host "╚═══════════════════════════════════════════════════════════╝" -ForegroundColor Cyan

Write-Host "`n📋 Configuración:" -ForegroundColor Yellow
Write-Host "   Base URL: $BaseUrl" -ForegroundColor Gray
Write-Host "   Chat ID: $ChatId" -ForegroundColor Gray
Write-Host ""

# Función para enviar mensajes
function Send-TelegramMessage {
    param(
        [string]$Message,
        [switch]$Silent
    )

    try {
        $body = @{
            chat_id = $ChatId
            message = $Message
        } | ConvertTo-Json

        $response = Invoke-RestMethod -Uri "$BaseUrl/process-input" `
            -Method Post `
            -ContentType "application/json" `
            -Body $body `
            -ErrorAction Stop

        if (-not $Silent) {
            Write-Host "👤 Usuario: " -NoNewline -ForegroundColor Blue
            Write-Host $Message -ForegroundColor White
            Write-Host "🤖 Bot: " -NoNewline -ForegroundColor Green
            Write-Host $response.mensaje.Substring(0, [Math]::Min(100, $response.mensaje.Length))... -ForegroundColor Gray
            Write-Host ""
        }

        return $response
    }
    catch {
        Write-Host "❌ Error al enviar mensaje: $_" -ForegroundColor Red
        throw
    }
}

# TEST 1: Verificar estado inicial
Write-Host "🧪 TEST 1: Verificar que no está registrado..." -ForegroundColor Yellow
try {
    $checkBody = @{ chat_id = $ChatId } | ConvertTo-Json
    $checkResponse = Invoke-RestMethod -Uri "$BaseUrl/check-registered" `
        -Method Post `
        -ContentType "application/json" `
        -Body $checkBody

    if ($checkResponse.registrado -eq $false) {
        Write-Host "   ✅ PASS - Chat ID no registrado" -ForegroundColor Green
    } else {
        Write-Host "   ❌ FAIL - Chat ID ya registrado" -ForegroundColor Red
        exit 1
    }
}
catch {
    Write-Host "   ❌ ERROR - No se pudo verificar: $_" -ForegroundColor Red
    exit 1
}

# TEST 2: Flujo de registro completo
Write-Host "`n🧪 TEST 2: Flujo de registro completo..." -ForegroundColor Yellow
Write-Host ""

try {
    # Paso 1: Inicio
    $r1 = Send-TelegramMessage "hola"
    Start-Sleep -Milliseconds 200

    # Paso 2: No tengo cuenta
    $r2 = Send-TelegramMessage "NO"
    Start-Sleep -Milliseconds 200

    # Paso 3: Nombre
    $r3 = Send-TelegramMessage "Test User $ChatId"
    Start-Sleep -Milliseconds 200

    # Paso 4: Email
    $testEmail = "test$ChatId@ejemplo.com"
    $r4 = Send-TelegramMessage $testEmail
    Start-Sleep -Milliseconds 200

    # Paso 5: Teléfono
    $r5 = Send-TelegramMessage "+593 987654321"
    Start-Sleep -Milliseconds 200

    # Paso 6: Password
    $r6 = Send-TelegramMessage "password123"
    Start-Sleep -Milliseconds 200

    # Paso 7: Confirmar
    $r7 = Send-TelegramMessage "SI"

    if ($r7.auth_complete -eq $true) {
        Write-Host "   ✅ PASS - Registro completado" -ForegroundColor Green
        Write-Host "   📧 Email: $testEmail" -ForegroundColor Gray
        Write-Host "   🆔 Cliente ID: $($r7.cliente.id)" -ForegroundColor Gray
    } else {
        Write-Host "   ❌ FAIL - Registro no completado" -ForegroundColor Red
        exit 1
    }
}
catch {
    Write-Host "   ❌ ERROR durante registro: $_" -ForegroundColor Red
    exit 1
}

# TEST 3: Verificar que ahora está registrado
Write-Host "`n🧪 TEST 3: Verificar que ahora está registrado..." -ForegroundColor Yellow
try {
    $checkBody2 = @{ chat_id = $ChatId } | ConvertTo-Json
    $checkResponse2 = Invoke-RestMethod -Uri "$BaseUrl/check-registered" `
        -Method Post `
        -ContentType "application/json" `
        -Body $checkBody2

    if ($checkResponse2.registrado -eq $true) {
        Write-Host "   ✅ PASS - Chat ID ahora registrado" -ForegroundColor Green
        Write-Host "   👤 Cliente: $($checkResponse2.cliente.nombre)" -ForegroundColor Gray
    } else {
        Write-Host "   ❌ FAIL - Chat ID no registrado después del proceso" -ForegroundColor Red
        exit 1
    }
}
catch {
    Write-Host "   ❌ ERROR - No se pudo verificar: $_" -ForegroundColor Red
    exit 1
}

# TEST 4: Verificar que no hay sesión activa
Write-Host "`n🧪 TEST 4: Verificar limpieza de sesión..." -ForegroundColor Yellow
try {
    $sessionBody = @{ chat_id = $ChatId } | ConvertTo-Json
    $sessionResponse = Invoke-RestMethod -Uri "$BaseUrl/get-session" `
        -Method Post `
        -ContentType "application/json" `
        -Body $sessionBody

    if ($sessionResponse.sesion.step -eq "inicio" -and $sessionResponse.sesion.proceso -eq $null) {
        Write-Host "   ✅ PASS - Sesión reiniciada correctamente" -ForegroundColor Green
    } else {
        Write-Host "   ⚠️  WARN - Sesión no reiniciada (step: $($sessionResponse.sesion.step))" -ForegroundColor Yellow
    }
}
catch {
    Write-Host "   ❌ ERROR - No se pudo verificar sesión: $_" -ForegroundColor Red
}

# RESUMEN
Write-Host "`n╔═══════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║                    RESUMEN DE PRUEBAS                     ║" -ForegroundColor Cyan
Write-Host "╚═══════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""
Write-Host "✅ Todas las pruebas pasaron exitosamente!" -ForegroundColor Green
Write-Host ""
Write-Host "📊 Datos de prueba creados:" -ForegroundColor Yellow
Write-Host "   Chat ID: $ChatId" -ForegroundColor Gray
Write-Host "   Email: test$ChatId@ejemplo.com" -ForegroundColor Gray
Write-Host "   Password: password123" -ForegroundColor Gray
Write-Host ""
Write-Host "🧹 Para limpiar los datos de prueba ejecuta:" -ForegroundColor Yellow
Write-Host "   DELETE FROM clientes WHERE telegram_chat_id = $ChatId;" -ForegroundColor Gray
Write-Host "   DELETE FROM telegram_auth_sessions WHERE chat_id = $ChatId;" -ForegroundColor Gray
Write-Host ""
