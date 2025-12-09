# 🧪 GUÍA DE PRUEBAS - API VENTAS

## 📌 Información General

**Base URL**: `http://localhost/api/v1`  
**Autenticación**: API Key en header `X-API-Key`  
**Formato**: JSON

---

## 🔑 Obtener API Key

Antes de probar los endpoints, necesitas generar una API Key. Ejecuta en MySQL:

```sql
-- Ver API Keys existentes
SELECT * FROM api_keys WHERE activa = 1;

-- O crear una nueva desde Laravel Tinker
php artisan tinker
>>> $key = \App\Models\ApiKey::generate('Test Ventas API', 1);
>>> $key->key
```

---

## 📋 ENDPOINTS DE VENTAS

### 1. Listar Ventas

**GET** `/ventas`

**Headers**:
```
X-API-Key: tu-api-key-aqui
Content-Type: application/json
```

**Query Parameters** (opcionales):
```
per_page=15              // Resultados por página
idcli=1                  // Filtrar por cliente
idemp=1                  // Filtrar por empleado
fecha_inicio=2025-01-01  // Desde fecha
fecha_fin=2025-12-31     // Hasta fecha
search=Juan              // Buscar por nombre/teléfono cliente
sort_by=fechaven         // Campo para ordenar
sort_order=desc          // asc o desc
```

**Ejemplo de URL completa**:
```
GET http://localhost/api/v1/ventas?per_page=10&idcli=1&sort_order=desc
```

**Respuesta exitosa (200)**:
```json
{
  "success": true,
  "data": [
    {
      "idven": 1,
      "idemp": 1,
      "idcli": 1,
      "fechaven": "2025-12-04T10:30:00.000000Z",
      "created_at": "2025-12-04T10:30:00.000000Z",
      "updated_at": "2025-12-04T10:30:00.000000Z",
      "cliente": {
        "idcli": 1,
        "nombrecli": "Juan Pérez",
        "telefonocli": "1234567890"
      },
      "empleado": {
        "idemp": 1,
        "nombre1emp": "María",
        "apellido1emp": "González"
      },
      "detalles_venta": [
        {
          "iddet": 1,
          "idven": 1,
          "idper": 1,
          "montodet": 25.00,
          "activodet": true
        }
      ]
    }
  ],
  "pagination": {
    "total": 50,
    "per_page": 15,
    "current_page": 1,
    "last_page": 4,
    "from": 1,
    "to": 15
  }
}
```

---

### 2. Ver Venta Específica

**GET** `/ventas/{id}`

**Ejemplo**:
```
GET http://localhost/api/v1/ventas/1
```

**Respuesta exitosa (200)**:
```json
{
  "success": true,
  "data": {
    "idven": 1,
    "idemp": 1,
    "idcli": 1,
    "fechaven": "2025-12-04T10:30:00.000000Z",
    "monto_total": 50.00,
    "cantidad_detalles": 2,
    "cliente": {
      "idcli": 1,
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
        "iddet": 1,
        "idven": 1,
        "idper": 1,
        "descripciondet": "Netflix Premium",
        "fechavendet": "2026-01-04T10:30:00.000000Z",
        "montodet": 25.00,
        "activodet": true,
        "perfil": {
          "idper": 1,
          "numeroper": 1,
          "pinper": "1234",
          "cuenta": {
            "idcue": 1,
            "correocue": "cuenta@netflix.com"
          }
        }
      }
    ]
  }
}
```

**Error 404**:
```json
{
  "success": false,
  "error": "Venta no encontrada",
  "message": "No existe una venta con ID 999"
}
```

---

### 3. Crear Nueva Venta

**POST** `/ventas`

**Body (JSON)**:
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
    },
    {
      "idper": 2,
      "descripciondet": "Spotify Premium",
      "fechavendet": "2026-01-04",
      "montodet": 15.00,
      "activodet": true
    }
  ]
}
```

**Validaciones**:
- `idemp`: Requerido, debe existir en empleados
- `idcli`: Requerido, debe existir en clientes
- `fechaven`: Opcional, fecha válida (por defecto: hoy)
- `detalles`: Requerido, array con al menos 1 elemento
- `detalles[].idper`: Requerido, debe existir en perfiles
- `detalles[].montodet`: Requerido, número >= 0

**Respuesta exitosa (201)**:
```json
{
  "success": true,
  "message": "Venta creada exitosamente",
  "data": {
    "idven": 5,
    "idemp": 1,
    "idcli": 1,
    "fechaven": "2025-12-04T00:00:00.000000Z",
    "monto_total": 40.00,
    "cliente": {...},
    "empleado": {...},
    "detalles_venta": [...]
  }
}
```

**Error de validación (422)**:
```json
{
  "success": false,
  "error": "Errores de validación",
  "errors": {
    "idemp": ["El campo idemp es obligatorio."],
    "detalles": ["El campo detalles debe contener al menos 1 elemento."]
  }
}
```

---

### 4. Actualizar Venta

**PUT** `/ventas/{id}`

**Body (JSON)**:
```json
{
  "idemp": 2,
  "idcli": 3,
  "fechaven": "2025-12-05"
}
```

**Nota**: Solo actualiza la venta principal, NO los detalles.

**Respuesta exitosa (200)**:
```json
{
  "success": true,
  "message": "Venta actualizada exitosamente",
  "data": {
    "idven": 1,
    "idemp": 2,
    "idcli": 3,
    "fechaven": "2025-12-05T00:00:00.000000Z",
    "cliente": {...},
    "empleado": {...}
  }
}
```

---

### 5. Eliminar Venta

**DELETE** `/ventas/{id}`

**Ejemplo**:
```
DELETE http://localhost/api/v1/ventas/5
```

**Respuesta exitosa (200)**:
```json
{
  "success": true,
  "message": "Venta eliminada exitosamente"
}
```

**Error 400 (tiene detalles activos)**:
```json
{
  "success": false,
  "error": "No se puede eliminar",
  "message": "La venta tiene detalles activos. Desactívelos antes de eliminar."
}
```

---

### 6. Renovar Venta

**POST** `/ventas/{id}/renovar`

Crea una nueva venta copiando los detalles de una venta anterior y extendiendo las fechas de vencimiento.

**Body (JSON)**:
```json
{
  "idemp": 1,
  "fechaven": "2025-12-04",
  "meses_duracion": 3
}
```

**Parámetros**:
- `idemp`: Requerido, empleado que realiza la renovación
- `fechaven`: Opcional (por defecto: hoy)
- `meses_duracion`: Opcional (por defecto: 1), entre 1-12 meses

**Respuesta exitosa (201)**:
```json
{
  "success": true,
  "message": "Venta renovada exitosamente",
  "data": {
    "idven": 10,
    "idemp": 1,
    "idcli": 1,
    "fechaven": "2025-12-04T00:00:00.000000Z",
    "monto_total": 50.00,
    "venta_original_id": 1,
    "detalles_venta": [
      {
        "descripciondet": "Renovación - Netflix Premium",
        "fechavendet": "2026-03-04T10:30:00.000000Z",
        "montodet": 25.00,
        "activodet": true
      }
    ]
  }
}
```

---

### 7. Obtener Detalles de Venta

**GET** `/ventas/{id}/detalles`

Obtiene todos los detalles de una venta con información completa de perfiles, cuentas, servicios y estados.

**Ejemplo**:
```
GET http://localhost/api/v1/ventas/1/detalles
```

**Respuesta exitosa (200)**:
```json
{
  "success": true,
  "data": {
    "idven": 1,
    "total_detalles": 2,
    "monto_total": 40.00,
    "detalles_activos": 2,
    "detalles_vencidos": 0,
    "detalles": [
      {
        "iddet": 1,
        "perfil": {
          "idper": 1,
          "numeroper": 1,
          "pinper": "1234",
          "cuenta": {
            "idcue": 1,
            "correocue": "cuenta@netflix.com",
            "servicio": "Netflix",
            "proveedor": "Proveedor A"
          }
        },
        "descripciondet": "Netflix Premium",
        "fechavendet": "2026-01-04T00:00:00.000000Z",
        "montodet": 25.00,
        "activodet": true,
        "dias_restantes": 31,
        "estado": "Activo"
      },
      {
        "iddet": 2,
        "perfil": {...},
        "descripciondet": "Spotify Premium",
        "fechavendet": "2025-11-04T00:00:00.000000Z",
        "montodet": 15.00,
        "activodet": true,
        "dias_restantes": -30,
        "estado": "Vencido"
      }
    ]
  }
}
```

**Estados posibles**:
- `Activo`: `activodet = true` y fecha no vencida
- `Vencido`: `activodet = true` pero fecha ya pasó
- `Inactivo`: `activodet = false`

---

### 8. Estadísticas de Ventas

**GET** `/ventas-estadisticas`

**Query Parameters** (opcionales):
```
fecha_inicio=2025-01-01  // Por defecto: inicio del mes actual
fecha_fin=2025-12-31     // Por defecto: fin del mes actual
```

**Ejemplo**:
```
GET http://localhost/api/v1/ventas-estadisticas?fecha_inicio=2025-12-01&fecha_fin=2025-12-31
```

**Respuesta exitosa (200)**:
```json
{
  "success": true,
  "data": {
    "periodo": {
      "inicio": "2025-12-01T00:00:00.000000Z",
      "fin": "2025-12-31T23:59:59.000000Z"
    },
    "resumen": {
      "total_ventas": 45,
      "total_ingresos": 1250.50,
      "promedio_venta": 27.79
    },
    "top_clientes": [
      {
        "idcli": 1,
        "total_ventas": 10,
        "cliente": {
          "idcli": 1,
          "nombrecli": "Juan Pérez",
          "telefonocli": "1234567890"
        }
      }
    ],
    "ventas_por_empleado": [
      {
        "idemp": 1,
        "total_ventas": 25,
        "empleado": {
          "idemp": 1,
          "nombre1emp": "María",
          "apellido1emp": "González"
        }
      }
    ],
    "ventas_por_dia": [
      {
        "fecha": "2025-12-04",
        "total": 5,
        "monto": 125.00
      },
      {
        "fecha": "2025-12-03",
        "total": 3,
        "monto": 75.50
      }
    ]
  }
}
```

---

## 🧪 CASOS DE PRUEBA

### Caso 1: Crear venta completa
```bash
# 1. Listar clientes disponibles
GET /api/v1/clientes

# 2. Ver perfiles disponibles (asumiendo endpoint futuro)
GET /api/v1/perfiles?disponibles=true

# 3. Crear la venta
POST /api/v1/ventas
{
  "idemp": 1,
  "idcli": 1,
  "detalles": [
    {"idper": 1, "montodet": 25.00, "fechavendet": "2026-01-04"},
    {"idper": 2, "montodet": 15.00, "fechavendet": "2026-01-04"}
  ]
}

# 4. Verificar la venta creada
GET /api/v1/ventas/{nuevo_id}
```

### Caso 2: Renovar venta existente
```bash
# 1. Buscar ventas del cliente
GET /api/v1/ventas?idcli=1

# 2. Ver detalles de la venta a renovar
GET /api/v1/ventas/1/detalles

# 3. Renovar por 3 meses
POST /api/v1/ventas/1/renovar
{
  "idemp": 1,
  "meses_duracion": 3
}
```

### Caso 3: Estadísticas del mes
```bash
# Estadísticas de diciembre 2025
GET /api/v1/ventas-estadisticas?fecha_inicio=2025-12-01&fecha_fin=2025-12-31
```

### Caso 4: Buscar ventas de un cliente
```bash
# Por ID de cliente
GET /api/v1/ventas?idcli=1&per_page=20

# Por nombre de cliente
GET /api/v1/ventas?search=Juan&per_page=20
```

---

## ⚠️ ERRORES COMUNES

### Error 404 - Recurso no encontrado
```json
{
  "success": false,
  "error": "Venta no encontrada",
  "message": "No existe una venta con ID 999"
}
```

### Error 422 - Validación fallida
```json
{
  "success": false,
  "error": "Errores de validación",
  "errors": {
    "idemp": ["El campo idemp es obligatorio."],
    "detalles.0.montodet": ["El campo montodet debe ser un número mayor o igual a 0."]
  }
}
```

### Error 400 - Acción no permitida
```json
{
  "success": false,
  "error": "No se puede eliminar",
  "message": "La venta tiene detalles activos. Desactívelos antes de eliminar."
}
```

### Error 500 - Error del servidor
```json
{
  "success": false,
  "error": "Error al crear venta",
  "message": "SQLSTATE[23000]: Integrity constraint violation..."
}
```

---

## 📦 COLECCIÓN POSTMAN

### Variables de entorno sugeridas:
```json
{
  "base_url": "http://localhost/api/v1",
  "api_key": "tu-api-key-generada",
  "empleado_id": "1",
  "cliente_id": "1",
  "perfil_id": "1",
  "venta_id": "1"
}
```

### Estructura de carpetas:
```
📁 Streamify API v1
  📁 Auth
    - Login
    - Logout
  📁 Clientes
    - Listar
    - Ver
    - Crear
    - Actualizar
    - Eliminar
  📁 Ventas ⭐
    - Listar ventas
    - Ver venta
    - Crear venta
    - Actualizar venta
    - Eliminar venta
    - Renovar venta
    - Detalles de venta
    - Estadísticas
```

---

**Última actualización**: Diciembre 4, 2025  
**Versión API**: v1.0  
**Controlador**: VentaApiController
