# 🧪 EJEMPLOS cURL - API VENTAS

## Variables de entorno (configurar primero)

```bash
# Windows (PowerShell)
$API_KEY = "tu-api-key-aqui"
$BASE_URL = "http://localhost/api/v1"

# Linux/Mac (Bash)
export API_KEY="tu-api-key-aqui"
export BASE_URL="http://localhost/api/v1"
```

---

## 1️⃣ LISTAR VENTAS

### Listar todas (paginado)
```bash
curl -X GET "$BASE_URL/ventas" \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json"
```

### Con filtros
```bash
curl -X GET "$BASE_URL/ventas?per_page=10&idcli=1&sort_order=desc" \
  -H "X-API-Key: $API_KEY"
```

### Por rango de fechas
```bash
curl -X GET "$BASE_URL/ventas?fecha_inicio=2025-12-01&fecha_fin=2025-12-31" \
  -H "X-API-Key: $API_KEY"
```

### Buscar por cliente
```bash
curl -X GET "$BASE_URL/ventas?search=Juan" \
  -H "X-API-Key: $API_KEY"
```

---

## 2️⃣ VER VENTA ESPECÍFICA

```bash
curl -X GET "$BASE_URL/ventas/1" \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json"
```

---

## 3️⃣ CREAR VENTA

### Venta simple (1 detalle)
```bash
curl -X POST "$BASE_URL/ventas" \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
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
  }'
```

### Venta múltiple (varios detalles)
```bash
curl -X POST "$BASE_URL/ventas" \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
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
      },
      {
        "idper": 2,
        "descripciondet": "Spotify Premium",
        "fechavendet": "2026-01-04",
        "montodet": 15.00,
        "activodet": true
      },
      {
        "idper": 3,
        "descripciondet": "Disney+ Anual",
        "fechavendet": "2026-12-04",
        "montodet": 80.00,
        "activodet": true
      }
    ]
  }'
```

### Venta sin fecha (usa fecha actual)
```bash
curl -X POST "$BASE_URL/ventas" \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "idemp": 1,
    "idcli": 2,
    "detalles": [
      {
        "idper": 5,
        "montodet": 30.00,
        "fechavendet": "2026-01-04"
      }
    ]
  }'
```

---

## 4️⃣ ACTUALIZAR VENTA

```bash
curl -X PUT "$BASE_URL/ventas/1" \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "idemp": 2,
    "idcli": 3,
    "fechaven": "2025-12-05"
  }'
```

### Actualización parcial (solo empleado)
```bash
curl -X PATCH "$BASE_URL/ventas/1" \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "idemp": 3
  }'
```

---

## 5️⃣ ELIMINAR VENTA

```bash
curl -X DELETE "$BASE_URL/ventas/5" \
  -H "X-API-Key: $API_KEY"
```

---

## 6️⃣ RENOVAR VENTA

### Renovación por 1 mes (default)
```bash
curl -X POST "$BASE_URL/ventas/1/renovar" \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "idemp": 1
  }'
```

### Renovación por 3 meses
```bash
curl -X POST "$BASE_URL/ventas/1/renovar" \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "idemp": 1,
    "meses_duracion": 3
  }'
```

### Renovación con fecha específica
```bash
curl -X POST "$BASE_URL/ventas/1/renovar" \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "idemp": 1,
    "fechaven": "2025-12-10",
    "meses_duracion": 6
  }'
```

---

## 7️⃣ VER DETALLES DE VENTA

```bash
curl -X GET "$BASE_URL/ventas/1/detalles" \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json"
```

---

## 8️⃣ ESTADÍSTICAS DE VENTAS

### Estadísticas del mes actual
```bash
curl -X GET "$BASE_URL/ventas-estadisticas" \
  -H "X-API-Key: $API_KEY"
```

### Estadísticas de diciembre 2025
```bash
curl -X GET "$BASE_URL/ventas-estadisticas?fecha_inicio=2025-12-01&fecha_fin=2025-12-31" \
  -H "X-API-Key: $API_KEY"
```

### Estadísticas del año completo
```bash
curl -X GET "$BASE_URL/ventas-estadisticas?fecha_inicio=2025-01-01&fecha_fin=2025-12-31" \
  -H "X-API-Key: $API_KEY"
```

---

## 🔧 SCRIPTS DE PRUEBA COMPLETA

### Script 1: Flujo completo de venta

```bash
#!/bin/bash

# Configuración
API_KEY="tu-api-key-aqui"
BASE_URL="http://localhost/api/v1"

# 1. Crear venta
echo "1. Creando venta..."
VENTA_ID=$(curl -s -X POST "$BASE_URL/ventas" \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "idemp": 1,
    "idcli": 1,
    "detalles": [
      {"idper": 1, "montodet": 25.00, "fechavendet": "2026-01-04"}
    ]
  }' | jq -r '.data.idven')

echo "Venta creada con ID: $VENTA_ID"

# 2. Ver venta creada
echo -e "\n2. Viendo venta..."
curl -s -X GET "$BASE_URL/ventas/$VENTA_ID" \
  -H "X-API-Key: $API_KEY" | jq

# 3. Ver detalles
echo -e "\n3. Viendo detalles..."
curl -s -X GET "$BASE_URL/ventas/$VENTA_ID/detalles" \
  -H "X-API-Key: $API_KEY" | jq

# 4. Renovar venta
echo -e "\n4. Renovando venta..."
curl -s -X POST "$BASE_URL/ventas/$VENTA_ID/renovar" \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "idemp": 1,
    "meses_duracion": 3
  }' | jq

# 5. Ver estadísticas
echo -e "\n5. Viendo estadísticas..."
curl -s -X GET "$BASE_URL/ventas-estadisticas" \
  -H "X-API-Key: $API_KEY" | jq '.data.resumen'
```

### Script 2: Pruebas de validación

```bash
#!/bin/bash

API_KEY="tu-api-key-aqui"
BASE_URL="http://localhost/api/v1"

# Test 1: Crear venta sin empleado (debe fallar)
echo "Test 1: Venta sin empleado (esperando error 422)..."
curl -s -X POST "$BASE_URL/ventas" \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "idcli": 1,
    "detalles": [{"idper": 1, "montodet": 25}]
  }' | jq

# Test 2: Crear venta sin detalles (debe fallar)
echo -e "\nTest 2: Venta sin detalles (esperando error 422)..."
curl -s -X POST "$BASE_URL/ventas" \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "idemp": 1,
    "idcli": 1,
    "detalles": []
  }' | jq

# Test 3: Ver venta inexistente (debe fallar)
echo -e "\nTest 3: Venta inexistente (esperando error 404)..."
curl -s -X GET "$BASE_URL/ventas/99999" \
  -H "X-API-Key: $API_KEY" | jq

# Test 4: Monto negativo (debe fallar)
echo -e "\nTest 4: Monto negativo (esperando error 422)..."
curl -s -X POST "$BASE_URL/ventas" \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "idemp": 1,
    "idcli": 1,
    "detalles": [{"idper": 1, "montodet": -10}]
  }' | jq
```

---

## 🪟 VERSIÓN POWERSHELL (Windows)

### Listar ventas
```powershell
$headers = @{
    "X-API-Key" = "tu-api-key-aqui"
    "Content-Type" = "application/json"
}

Invoke-RestMethod -Uri "http://localhost/api/v1/ventas" `
    -Method Get `
    -Headers $headers | ConvertTo-Json -Depth 10
```

### Crear venta
```powershell
$headers = @{
    "X-API-Key" = "tu-api-key-aqui"
    "Content-Type" = "application/json"
}

$body = @{
    idemp = 1
    idcli = 1
    fechaven = "2025-12-04"
    detalles = @(
        @{
            idper = 1
            descripciondet = "Netflix Premium"
            fechavendet = "2026-01-04"
            montodet = 25.00
            activodet = $true
        }
    )
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost/api/v1/ventas" `
    -Method Post `
    -Headers $headers `
    -Body $body | ConvertTo-Json -Depth 10
```

### Ver detalles
```powershell
$headers = @{
    "X-API-Key" = "tu-api-key-aqui"
}

Invoke-RestMethod -Uri "http://localhost/api/v1/ventas/1/detalles" `
    -Method Get `
    -Headers $headers | ConvertTo-Json -Depth 10
```

### Estadísticas
```powershell
$headers = @{
    "X-API-Key" = "tu-api-key-aqui"
}

$params = @{
    fecha_inicio = "2025-12-01"
    fecha_fin = "2025-12-31"
}

$uri = "http://localhost/api/v1/ventas-estadisticas?" + ($params.GetEnumerator() | 
    ForEach-Object { "$($_.Key)=$($_.Value)" }) -join "&"

Invoke-RestMethod -Uri $uri `
    -Method Get `
    -Headers $headers | ConvertTo-Json -Depth 10
```

---

## 🎯 CASOS DE USO COMPLETOS

### Caso 1: Cliente compra Netflix + Spotify
```bash
curl -X POST "$BASE_URL/ventas" \
  -H "X-API-Key: $API_KEY" \
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
      },
      {
        "idper": 15,
        "descripciondet": "Spotify Premium - Individual",
        "fechavendet": "2026-01-04",
        "montodet": 15.00
      }
    ]
  }'
```

### Caso 2: Renovar todas las cuentas de un cliente
```bash
# 1. Buscar ventas del cliente
curl -X GET "$BASE_URL/ventas?idcli=5" \
  -H "X-API-Key: $API_KEY"

# 2. Renovar cada venta
curl -X POST "$BASE_URL/ventas/10/renovar" \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"idemp": 1, "meses_duracion": 1}'
```

### Caso 3: Reporte mensual de ventas
```bash
# Estadísticas completas del mes
curl -X GET "$BASE_URL/ventas-estadisticas?fecha_inicio=2025-12-01&fecha_fin=2025-12-31" \
  -H "X-API-Key: $API_KEY" | jq '{
    total_ventas: .data.resumen.total_ventas,
    total_ingresos: .data.resumen.total_ingresos,
    promedio_venta: .data.resumen.promedio_venta,
    top_cliente: .data.top_clientes[0].cliente.nombrecli,
    mejor_empleado: .data.ventas_por_empleado[0].empleado.nombre1emp
  }'
```

---

## 📝 NOTAS

- Reemplaza `$API_KEY` con tu API Key real
- Reemplaza `$BASE_URL` según tu configuración
- Los IDs (idemp, idcli, idper) deben existir en la BD
- Usa `jq` para formatear JSON en Linux/Mac
- En Windows, usa `| ConvertTo-Json` en PowerShell

---

**Última actualización**: Diciembre 4, 2025  
**Versión**: 1.0
