# 🛒 GUÍA PRÁCTICA: CREAR Y RENOVAR VENTAS - API

## 📋 ÍNDICE
1. [Crear Nueva Venta](#-1-crear-nueva-venta)
2. [Renovar Venta Existente](#-2-renovar-venta-existente)
3. [Casos de Uso Reales](#-3-casos-de-uso-reales)
4. [Errores Comunes](#-4-errores-comunes)
5. [Testing con cURL y Postman](#-5-testing-con-curl-y-postman)

---

## 🆕 1. CREAR NUEVA VENTA

### Endpoint
```
POST /api/v1/ventas
```

### Headers Requeridos
```
X-API-Key: tu-api-key-aqui
Content-Type: application/json
```

### Body (JSON)

#### Estructura Básica
```json
{
  "idemp": 1,
  "idcli": 1,
  "fechaven": "2025-12-04",
  "detalles": [
    {
      "idper": 1,
      "descripciondet": "Netflix Premium - Perfil 1",
      "fechavendet": "2026-01-04",
      "montodet": 25.00,
      "activodet": true
    }
  ]
}
```

#### Campos Explicados

| Campo | Tipo | Requerido | Descripción | Ejemplo |
|-------|------|-----------|-------------|---------|
| `idemp` | int | ✅ Sí | ID del empleado que realiza la venta | `1` |
| `idcli` | int | ✅ Sí | ID del cliente que compra | `1` |
| `fechaven` | date | ❌ No | Fecha de la venta (default: hoy) | `"2025-12-04"` |
| `detalles` | array | ✅ Sí | Array con mínimo 1 detalle | `[...]` |

**Campos de cada detalle**:

| Campo | Tipo | Requerido | Descripción | Ejemplo |
|-------|------|-----------|-------------|---------|
| `idper` | int | ✅ Sí | ID del perfil (cuenta streaming) | `1` |
| `descripciondet` | string | ❌ No | Descripción del servicio | `"Netflix Premium"` |
| `fechavendet` | date | ❌ No | Fecha de vencimiento (default: hoy) | `"2026-01-04"` |
| `montodet` | decimal | ✅ Sí | Precio del servicio | `25.00` |
| `activodet` | boolean | ❌ No | Si está activo (default: true) | `true` |

---

### 📝 Ejemplo 1: Venta Simple (1 servicio)

**Cliente compra Netflix por 1 mes**

```json
{
  "idemp": 1,
  "idcli": 5,
  "fechaven": "2025-12-04",
  "detalles": [
    {
      "idper": 10,
      "descripciondet": "Netflix Premium - Perfil Compartido",
      "fechavendet": "2026-01-04",
      "montodet": 25.00,
      "activodet": true
    }
  ]
}
```

**cURL**:
```bash
curl -X POST "http://localhost/api/v1/ventas" \
  -H "X-API-Key: tu-api-key" \
  -H "Content-Type: application/json" \
  -d '{
    "idemp": 1,
    "idcli": 5,
    "detalles": [
      {
        "idper": 10,
        "descripciondet": "Netflix Premium - Perfil Compartido",
        "fechavendet": "2026-01-04",
        "montodet": 25.00
      }
    ]
  }'
```

**PowerShell**:
```powershell
$body = @{
    idemp = 1
    idcli = 5
    detalles = @(
        @{
            idper = 10
            descripciondet = "Netflix Premium - Perfil Compartido"
            fechavendet = "2026-01-04"
            montodet = 25.00
        }
    )
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost/api/v1/ventas" `
    -Method Post `
    -Headers @{"X-API-Key"="tu-api-key"; "Content-Type"="application/json"} `
    -Body $body
```

**Respuesta Exitosa (201)**:
```json
{
  "success": true,
  "message": "Venta creada exitosamente",
  "data": {
    "idven": 15,
    "idemp": 1,
    "idcli": 5,
    "fechaven": "2025-12-04T00:00:00.000000Z",
    "created_at": "2025-12-04T10:30:00.000000Z",
    "updated_at": "2025-12-04T10:30:00.000000Z",
    "monto_total": 25.00,
    "cliente": {
      "idcli": 5,
      "nombrecli": "Juan Pérez",
      "telefonocli": "1234567890",
      "email": "juan@example.com"
    },
    "empleado": {
      "idemp": 1,
      "nombre1emp": "María",
      "apellido1emp": "González"
    },
    "detalles_venta": [
      {
        "iddet": 50,
        "idven": 15,
        "idper": 10,
        "descripciondet": "Netflix Premium - Perfil Compartido",
        "fechavendet": "2026-01-04T00:00:00.000000Z",
        "montodet": 25.00,
        "activodet": true,
        "perfil": {
          "idper": 10,
          "numeroper": 1,
          "pinper": "1234",
          "cuenta": {
            "idcue": 5,
            "correocue": "netflix@example.com"
          }
        }
      }
    ]
  }
}
```

---

### 📝 Ejemplo 2: Venta Combo (múltiples servicios)

**Cliente compra Netflix + Spotify + Disney+**

```json
{
  "idemp": 1,
  "idcli": 3,
  "fechaven": "2025-12-04",
  "detalles": [
    {
      "idper": 1,
      "descripciondet": "Netflix Premium - 1 mes",
      "fechavendet": "2026-01-04",
      "montodet": 25.00
    },
    {
      "idper": 2,
      "descripciondet": "Spotify Premium - 1 mes",
      "fechavendet": "2026-01-04",
      "montodet": 15.00
    },
    {
      "idper": 3,
      "descripciondet": "Disney+ - 1 mes",
      "fechavendet": "2026-01-04",
      "montodet": 20.00
    }
  ]
}
```

**cURL**:
```bash
curl -X POST "http://localhost/api/v1/ventas" \
  -H "X-API-Key: tu-api-key" \
  -H "Content-Type: application/json" \
  -d '{
    "idemp": 1,
    "idcli": 3,
    "detalles": [
      {"idper": 1, "descripciondet": "Netflix Premium - 1 mes", "fechavendet": "2026-01-04", "montodet": 25.00},
      {"idper": 2, "descripciondet": "Spotify Premium - 1 mes", "fechavendet": "2026-01-04", "montodet": 15.00},
      {"idper": 3, "descripciondet": "Disney+ - 1 mes", "fechavendet": "2026-01-04", "montodet": 20.00}
    ]
  }'
```

**Respuesta**:
```json
{
  "success": true,
  "message": "Venta creada exitosamente",
  "data": {
    "idven": 16,
    "monto_total": 60.00,
    "detalles_venta": [
      {
        "iddet": 51,
        "descripciondet": "Netflix Premium - 1 mes",
        "montodet": 25.00
      },
      {
        "iddet": 52,
        "descripciondet": "Spotify Premium - 1 mes",
        "montodet": 15.00
      },
      {
        "iddet": 53,
        "descripciondet": "Disney+ - 1 mes",
        "montodet": 20.00
      }
    ]
  }
}
```

---

### 📝 Ejemplo 3: Venta Simplificada (campos mínimos)

```json
{
  "idemp": 1,
  "idcli": 2,
  "detalles": [
    {
      "idper": 5,
      "montodet": 30.00
    }
  ]
}
```

**Nota**: Si omites campos opcionales:
- `fechaven` → se usa la fecha actual
- `descripciondet` → será `null`
- `fechavendet` → se usa la fecha actual
- `activodet` → será `true` por defecto

---

## 🔄 2. RENOVAR VENTA EXISTENTE

### Endpoint
```
POST /api/v1/ventas/{id}/renovar
```

### ¿Qué hace la renovación?

✅ Crea una **nueva venta** copiando todos los detalles de una venta anterior  
✅ Mantiene el **mismo cliente** y **mismos perfiles**  
✅ Mantiene los **mismos montos**  
✅ Extiende las **fechas de vencimiento**  
✅ Marca todos los detalles como **activos**

### Headers Requeridos
```
X-API-Key: tu-api-key-aqui
Content-Type: application/json
```

### Body (JSON)

```json
{
  "idemp": 1,
  "fechaven": "2025-12-04",
  "meses_duracion": 3
}
```

#### Campos

| Campo | Tipo | Requerido | Descripción | Ejemplo | Default |
|-------|------|-----------|-------------|---------|---------|
| `idemp` | int | ✅ Sí | ID del empleado que renueva | `1` | - |
| `fechaven` | date | ❌ No | Fecha de la nueva venta | `"2025-12-04"` | Hoy |
| `meses_duracion` | int | ❌ No | Meses a extender (1-12) | `3` | 1 |

---

### 📝 Ejemplo 1: Renovación Simple (1 mes)

**Renovar venta ID 10 por 1 mes más**

```bash
curl -X POST "http://localhost/api/v1/ventas/10/renovar" \
  -H "X-API-Key: tu-api-key" \
  -H "Content-Type: application/json" \
  -d '{
    "idemp": 1
  }'
```

**PowerShell**:
```powershell
$body = @{
    idemp = 1
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost/api/v1/ventas/10/renovar" `
    -Method Post `
    -Headers @{"X-API-Key"="tu-api-key"; "Content-Type"="application/json"} `
    -Body $body
```

**Respuesta (201)**:
```json
{
  "success": true,
  "message": "Venta renovada exitosamente",
  "data": {
    "idven": 25,
    "idemp": 1,
    "idcli": 5,
    "fechaven": "2025-12-04T00:00:00.000000Z",
    "monto_total": 25.00,
    "venta_original_id": 10,
    "cliente": {...},
    "empleado": {...},
    "detalles_venta": [
      {
        "iddet": 80,
        "descripciondet": "Renovación - Netflix Premium",
        "fechavendet": "2026-01-04T10:30:00.000000Z",
        "montodet": 25.00,
        "activodet": true
      }
    ]
  }
}
```

---

### 📝 Ejemplo 2: Renovación por 3 meses

```bash
curl -X POST "http://localhost/api/v1/ventas/10/renovar" \
  -H "X-API-Key: tu-api-key" \
  -H "Content-Type: application/json" \
  -d '{
    "idemp": 1,
    "meses_duracion": 3
  }'
```

**JSON**:
```json
{
  "idemp": 1,
  "meses_duracion": 3
}
```

**Resultado**: 
- Vencimiento original: 2025-12-04
- Nuevo vencimiento: **2026-03-04** (3 meses después de HOY)

---

### 📝 Ejemplo 3: Renovación con fecha específica

```json
{
  "idemp": 2,
  "fechaven": "2025-12-10",
  "meses_duracion": 6
}
```

**Resultado**:
- Fecha de la venta: 2025-12-10
- Vencimiento de detalles: 2026-06-10 (6 meses después)

---

### 📝 Ejemplo 4: Renovación por 1 año

```bash
curl -X POST "http://localhost/api/v1/ventas/10/renovar" \
  -H "X-API-Key: tu-api-key" \
  -H "Content-Type: application/json" \
  -d '{
    "idemp": 1,
    "meses_duracion": 12
  }'
```

**Nota**: El máximo permitido es **12 meses**

---

## 🎯 3. CASOS DE USO REALES

### Caso 1: Cliente Nuevo - Primera Compra

**Escenario**: Juan Pérez (idcli=5) compra Netflix por primera vez

**Paso 1**: Verificar que el cliente existe
```bash
curl -H "X-API-Key: tu-api-key" "http://localhost/api/v1/clientes/5"
```

**Paso 2**: Crear la venta
```bash
curl -X POST "http://localhost/api/v1/ventas" \
  -H "X-API-Key: tu-api-key" \
  -H "Content-Type: application/json" \
  -d '{
    "idemp": 1,
    "idcli": 5,
    "detalles": [
      {
        "idper": 10,
        "descripciondet": "Netflix Premium - Primera compra",
        "fechavendet": "2026-01-04",
        "montodet": 25.00
      }
    ]
  }'
```

**Paso 3**: Guardar el ID de venta retornado (ej: 15) para futuras renovaciones

---

### Caso 2: Cliente Existente - Renovación Mensual

**Escenario**: Juan renueva su Netflix cada mes

**Paso 1**: Buscar su última venta
```bash
curl -H "X-API-Key: tu-api-key" "http://localhost/api/v1/ventas?idcli=5&sort_order=desc&per_page=1"
```

**Paso 2**: Renovar la venta (suponiendo ID 15)
```bash
curl -X POST "http://localhost/api/v1/ventas/15/renovar" \
  -H "X-API-Key: tu-api-key" \
  -H "Content-Type: application/json" \
  -d '{
    "idemp": 1,
    "meses_duracion": 1
  }'
```

---

### Caso 3: Combo Familiar - Múltiples Servicios

**Escenario**: María compra paquete familiar (Netflix + Spotify + Disney+)

```bash
curl -X POST "http://localhost/api/v1/ventas" \
  -H "X-API-Key: tu-api-key" \
  -H "Content-Type: application/json" \
  -d '{
    "idemp": 1,
    "idcli": 8,
    "detalles": [
      {
        "idper": 1,
        "descripciondet": "Netflix Premium - Familia",
        "fechavendet": "2026-01-04",
        "montodet": 25.00
      },
      {
        "idper": 2,
        "descripciondet": "Spotify Familiar - 6 perfiles",
        "fechavendet": "2026-01-04",
        "montodet": 18.00
      },
      {
        "idper": 3,
        "descripciondet": "Disney+ Bundle",
        "fechavendet": "2026-01-04",
        "montodet": 22.00
      }
    ]
  }'
```

**Total**: $65.00

---

### Caso 4: Renovación Semestral con Descuento

**Escenario**: Cliente fiel renueva por 6 meses

```bash
# Renovar venta 20 por 6 meses
curl -X POST "http://localhost/api/v1/ventas/20/renovar" \
  -H "X-API-Key: tu-api-key" \
  -H "Content-Type: application/json" \
  -d '{
    "idemp": 1,
    "meses_duracion": 6
  }'
```

**Nota**: Si quieres aplicar descuento, deberías:
1. Crear una venta nueva con `store()` especificando el monto con descuento
2. No usar `renovar()` porque copia los montos originales

---

### Caso 5: Plan Anual - Prepago

**Escenario**: Cliente paga por todo el año anticipado

```bash
curl -X POST "http://localhost/api/v1/ventas" \
  -H "X-API-Key: tu-api-key" \
  -H "Content-Type: application/json" \
  -d '{
    "idemp": 1,
    "idcli": 12,
    "detalles": [
      {
        "idper": 5,
        "descripciondet": "Netflix Premium - Plan Anual Prepago",
        "fechavendet": "2026-12-04",
        "montodet": 240.00
      }
    ]
  }'
```

---

## ❌ 4. ERRORES COMUNES

### ⚠️ Error CRÍTICO: idven NULL - Trigger no ejecutado

**Error completo**:
```json
{
  "success": false,
  "error": "Error al crear venta",
  "message": "SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'idven' cannot be null (Connection: mysql, SQL: insert into `detalles_venta` (`idven`, `idper`, ...) values (?, ...))"
}
```

**Causa**: El modelo `Venta` insertaba el registro pero NO estaba refrescando para obtener el `idven` generado por el trigger MySQL `trg_generar_idventa`.

**Solución**: ✅ **YA CORREGIDO** - Agregado `$venta->refresh()` después del `create()`:

```php
// ❌ ANTES (causaba idven = NULL):
$venta = Venta::create([...]);
// Inmediatamente usar $venta->idven (aún es NULL)

// ✅ DESPUÉS (correcto):
$venta = Venta::create([...]);
$venta->refresh(); // Recargar desde DB para obtener idven del trigger
// Ahora $venta->idven tiene el valor correcto: "001-001-000000001"
```

**Verificar que el trigger existe**:
```sql
SHOW TRIGGERS LIKE 'ventas';
-- Debe mostrar: trg_generar_idventa
```

---

### ⚠️ Error CRÍTICO: Foreign Key Constraint (idven = 0)

**Error completo**:
```json
{
  "success": false,
  "error": "Error al crear venta",
  "message": "SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: a foreign key constraint fails (`streamify`.`detalles_venta`, CONSTRAINT `detalles_venta_idven_foreign` FOREIGN KEY (`idven`) REFERENCES `ventas` (`idven`)) (Connection: mysql, SQL: insert into `detalles_venta` (`idven`, `idper`, ...) values (0, ...))"
}
```

**Causa**: El modelo `Venta` tenía `public $incrementing = false;` comentado, causando que Laravel intente insertar `idven = 0` en lugar de dejar que el trigger MySQL genere el ID en formato "001-001-000000001".

**Solución**: ✅ **YA CORREGIDO** - Descomentada la línea en `app/Models/Venta.php`:

```php
// ❌ ANTES (causaba el error):
//public $incrementing = false;
protected $keyType = 'string';

// ✅ DESPUÉS (correcto):
public $incrementing = false;  // No es auto-incremental, lo genera trigger MySQL
protected $keyType = 'string'; // El idven es VARCHAR(20) formato: 001-001-000000001
```

**Cómo verificar**: El trigger `trg_generar_idventa` debe generar automáticamente el `idven`:
```sql
-- Formato generado: establecimiento-facturero-secuencia
-- Ejemplo: 001-001-000000001, 001-001-000000002, etc.
```

---

### Error 1: Cliente no existe (404)

**Request**:
```json
{
  "idemp": 1,
  "idcli": 999,
  "detalles": [...]
}
```

**Response (404)**:
```json
{
  "success": false,
  "error": "Cliente no encontrado"
}
```

**Solución**: Verificar que `idcli` exista en la tabla clientes

---

### Error 2: Perfil no existe (404)

**Request**:
```json
{
  "idemp": 1,
  "idcli": 5,
  "detalles": [
    {"idper": 999, "montodet": 25.00}
  ]
}
```

**Response (404)**:
```json
{
  "success": false,
  "error": "Uno o más perfiles no existen"
}
```

**Solución**: Usar `idper` válidos de la tabla perfiles

---

### Error 3: Sin detalles (422)

**Request**:
```json
{
  "idemp": 1,
  "idcli": 5,
  "detalles": []
}
```

**Response (422)**:
```json
{
  "success": false,
  "error": "Errores de validación",
  "errors": {
    "detalles": ["El campo detalles debe contener al menos 1 elemento."]
  }
}
```

**Solución**: Enviar al menos 1 detalle

---

### Error 4: Monto negativo (422)

**Request**:
```json
{
  "idemp": 1,
  "idcli": 5,
  "detalles": [
    {"idper": 1, "montodet": -10.00}
  ]
}
```

**Response (422)**:
```json
{
  "success": false,
  "error": "Errores de validación",
  "errors": {
    "detalles.0.montodet": ["El campo montodet debe ser mayor o igual a 0."]
  }
}
```

**Solución**: Usar montos >= 0

---

### Error 5: Venta no encontrada para renovar (404)

**Request**:
```
POST /api/v1/ventas/999/renovar
```

**Response (404)**:
```json
{
  "success": false,
  "error": "Venta no encontrada",
  "message": "No existe una venta con ID 999"
}
```

**Solución**: Verificar que la venta existe antes de renovar

---

### Error 6: Meses de duración fuera de rango (422)

**Request**:
```json
{
  "idemp": 1,
  "meses_duracion": 15
}
```

**Response (422)**:
```json
{
  "success": false,
  "error": "Errores de validación",
  "errors": {
    "meses_duracion": ["El campo meses_duracion debe ser como máximo 12."]
  }
}
```

**Solución**: Usar entre 1 y 12 meses

---

## 🧪 5. TESTING CON cURL Y POSTMAN

### Script Bash Completo - Crear y Renovar

```bash
#!/bin/bash

API_KEY="tu-api-key-aqui"
BASE_URL="http://localhost/api/v1"

echo "=== PRUEBA 1: Crear Venta ==="
VENTA_RESPONSE=$(curl -s -X POST "$BASE_URL/ventas" \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "idemp": 1,
    "idcli": 1,
    "detalles": [
      {
        "idper": 1,
        "descripciondet": "Netflix Premium - Test",
        "fechavendet": "2026-01-04",
        "montodet": 25.00
      }
    ]
  }')

echo "$VENTA_RESPONSE" | jq

# Extraer ID de venta
VENTA_ID=$(echo "$VENTA_RESPONSE" | jq -r '.data.idven')
echo -e "\n✅ Venta creada con ID: $VENTA_ID"

echo -e "\n=== PRUEBA 2: Ver Venta Creada ==="
curl -s -X GET "$BASE_URL/ventas/$VENTA_ID" \
  -H "X-API-Key: $API_KEY" | jq

echo -e "\n=== PRUEBA 3: Renovar Venta por 3 meses ==="
curl -s -X POST "$BASE_URL/ventas/$VENTA_ID/renovar" \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "idemp": 1,
    "meses_duracion": 3
  }' | jq

echo -e "\n✅ Pruebas completadas"
```

---

### PowerShell Script Completo

```powershell
$API_KEY = "tu-api-key-aqui"
$BASE_URL = "http://localhost/api/v1"
$headers = @{
    "X-API-Key" = $API_KEY
    "Content-Type" = "application/json"
}

Write-Host "=== PRUEBA 1: Crear Venta ===" -ForegroundColor Green

$ventaBody = @{
    idemp = 1
    idcli = 1
    detalles = @(
        @{
            idper = 1
            descripciondet = "Netflix Premium - Test"
            fechavendet = "2026-01-04"
            montodet = 25.00
        }
    )
} | ConvertTo-Json

$venta = Invoke-RestMethod -Uri "$BASE_URL/ventas" `
    -Method Post `
    -Headers $headers `
    -Body $ventaBody

Write-Host "✅ Venta creada con ID: $($venta.data.idven)" -ForegroundColor Yellow
$ventaId = $venta.data.idven

Write-Host "`n=== PRUEBA 2: Ver Venta ===" -ForegroundColor Green
$ventaDetalle = Invoke-RestMethod -Uri "$BASE_URL/ventas/$ventaId" `
    -Method Get `
    -Headers $headers

$ventaDetalle.data | ConvertTo-Json -Depth 10

Write-Host "`n=== PRUEBA 3: Renovar por 3 meses ===" -ForegroundColor Green
$renovarBody = @{
    idemp = 1
    meses_duracion = 3
} | ConvertTo-Json

$renovacion = Invoke-RestMethod -Uri "$BASE_URL/ventas/$ventaId/renovar" `
    -Method Post `
    -Headers $headers `
    -Body $renovarBody

Write-Host "✅ Nueva venta ID: $($renovacion.data.idven)" -ForegroundColor Yellow
Write-Host "✅ Vencimiento: $($renovacion.data.detalles_venta[0].fechavendet)" -ForegroundColor Yellow
```

---

### Colección Postman

**Carpeta**: `Ventas - Crear y Renovar`

#### Request 1: Crear Venta Simple
```
POST {{base_url}}/ventas
Headers: X-API-Key: {{api_key}}
Body (JSON):
{
  "idemp": {{empleado_id}},
  "idcli": {{cliente_id}},
  "detalles": [
    {
      "idper": {{perfil_id}},
      "descripciondet": "Test desde Postman",
      "fechavendet": "2026-01-04",
      "montodet": 25.00
    }
  ]
}

Tests:
pm.test("Venta creada exitosamente", function () {
    pm.response.to.have.status(201);
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.eql(true);
    pm.environment.set("venta_id", jsonData.data.idven);
});
```

#### Request 2: Renovar Venta
```
POST {{base_url}}/ventas/{{venta_id}}/renovar
Headers: X-API-Key: {{api_key}}
Body (JSON):
{
  "idemp": {{empleado_id}},
  "meses_duracion": 3
}

Tests:
pm.test("Venta renovada exitosamente", function () {
    pm.response.to.have.status(201);
    var jsonData = pm.response.json();
    pm.expect(jsonData.success).to.eql(true);
    pm.expect(jsonData.data.venta_original_id).to.eql(parseInt(pm.environment.get("venta_id")));
});
```

---

## 📊 FLUJO COMPLETO RECOMENDADO

```
1. Cliente llama/WhatsApp solicitando servicio
   ↓
2. Empleado verifica disponibilidad de perfiles
   GET /api/v1/perfiles?disponibles=true
   ↓
3. Empleado crea la venta
   POST /api/v1/ventas
   ↓
4. Sistema genera factura/comprobante
   ↓
5. Cliente paga
   ↓
6. Empleado entrega credenciales del perfil
   ↓
7. 1 mes después, cliente renueva
   POST /api/v1/ventas/{id}/renovar
   ↓
8. Se extiende el vencimiento automáticamente
```

---

## ✅ CHECKLIST ANTES DE CREAR VENTA

- [ ] Verificar que el cliente existe (`GET /api/v1/clientes/{id}`)
- [ ] Verificar que los perfiles existen y están disponibles
- [ ] Confirmar el monto con el cliente
- [ ] Establecer fecha de vencimiento correcta
- [ ] Usar el `idemp` del empleado autenticado
- [ ] Guardar el ID de venta para futuras renovaciones

## ✅ CHECKLIST ANTES DE RENOVAR

- [ ] Verificar que la venta original existe
- [ ] Confirmar con el cliente que quiere renovar
- [ ] Decidir cuántos meses renovar (1, 3, 6, 12)
- [ ] Verificar que el cliente no tiene saldo pendiente
- [ ] Confirmar el pago antes de renovar

---

**Fecha**: Diciembre 4, 2025  
**API Version**: v1  
**Endpoints**: `/api/v1/ventas` (POST), `/api/v1/ventas/{id}/renovar` (POST)
