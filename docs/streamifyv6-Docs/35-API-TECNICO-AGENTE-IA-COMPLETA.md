# API REST para Agente IA Técnico - Documentación Completa

## Índice
1. [Gestión de Cuentas](#gestión-de-cuentas)
2. [Gestión de Usuarios](#gestión-de-usuarios)
3. [Gestión de Ventas](#gestión-de-ventas)
4. [Gestión de Productos](#gestión-de-productos)
5. [Configuración (Valores, Servicios, Proveedores)](#configuración)

---

## Base URL
```
http://localhost/api/v2
```

## Autenticación
Todas las rutas requieren API Key en el header:
```
Authorization: Bearer {API_KEY}
```

---

## 1. Gestión de Cuentas

### 1.1 Resumen General
```http
GET /tech-accounts/resumen
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "activas": 50,
    "vencidas": 5,
    "vigentes": 45,
    "por_vencer_7_dias": 3,
    "danadas": 2
  }
}
```

### 1.2 Cuentas por Servicio
```http
GET /tech-accounts/por-servicio
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "NETFLIX": {
      "total": 20,
      "activas": 18,
      "vencidas": 2
    },
    "SPOTIFY": {
      "total": 15,
      "activas": 15,
      "vencidas": 0
    }
  }
}
```

### 1.3 Cuentas por Estado
```http
GET /tech-accounts/estado?estado=vencidas
```

**Query Params:**
- `estado`: vencidas, vigentes, por_vencer

### 1.4 Detalle de Cuenta
```http
GET /tech-accounts/cuenta/{idcue}
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "cuenta": {
      "idcue": "NETFLIX-1",
      "usuario": "cuenta@ejemplo.com",
      "fecha_vencimiento": "2026-02-24",
      "activa": true
    },
    "perfiles": [...],
    "usuarios_activos": 4,
    "capacidad": 5
  }
}
```

### 1.5 Mover Usuarios de Servicio a Mesa
```http
POST /tech-accounts/acciones/mover-servicio-a-mesa
```

**Body:**
```json
{
  "servicio": "NETFLIX",
  "cuentas": ["NETFLIX-1", "NETFLIX-2"],
  "dry_run": false
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Se movieron 8 usuarios a mesa de trabajo",
  "count": 8,
  "movidos": [...]
}
```

### 1.6 Desactivar Usuarios Masivamente
```http
POST /tech-accounts/acciones/desactivar-usuarios
```

**Body:**
```json
{
  "cuentas": ["NETFLIX-1", "NETFLIX-2"],
  "dry_run": false
}
```

### 1.7 Limpiar Cuentas Vacías
```http
POST /tech-accounts/acciones/limpiar-cuentas
```

**Body:**
```json
{
  "servicio": "NETFLIX",
  "dry_run": true
}
```

---

## 2. Gestión de Usuarios

### 2.1 Desactivar Usuarios Vencidos
```http
POST /tech-usuarios/desactivar-vencidos
```

**Body:**
```json
{
  "servicio": "NETFLIX",
  "dry_run": false
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Se desactivaron 5 usuarios vencidos",
  "count": 5,
  "desactivados": [
    {
      "iddet": "001-001-000000001.1",
      "cliente": "Juan Pérez",
      "cuenta": "NETFLIX-1"
    }
  ]
}
```

### 2.2 Obtener Usuarios Vencidos Hoy
```http
GET /tech-usuarios/vencidos-hoy?servicio=NETFLIX
```

**Respuesta:**
```json
{
  "success": true,
  "count": 3,
  "fecha": "2026-01-24",
  "usuarios": [
    {
      "iddet": "...",
      "cliente": {
        "nombre": "Juan Pérez",
        "email": "juan@ejemplo.com",
        "telefono": "0999999999",
        "telegram": "@juanperez"
      },
      "cuenta": "NETFLIX-1",
      "servicio": "NETFLIX",
      "fecha_vencimiento": "2026-01-24",
      "monto_original": 5.00
    }
  ]
}
```

### 2.3 Cambiar Perfil de Usuario
```http
POST /tech-usuarios/cambiar-perfil
```

**Body:**
```json
{
  "iddet": "001-001-000000001.1",
  "nuevo_idper": "NETFLIX-2.3"
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Usuario movido exitosamente",
  "data": {
    "iddet": "001-001-000000001.1",
    "cliente": "Juan Pérez",
    "anterior": {
      "cuenta": "NETFLIX-1",
      "perfil": 1
    },
    "nuevo": {
      "cuenta": "NETFLIX-2",
      "perfil": 3
    }
  }
}
```

### 2.4 Usuarios por Cliente
```http
GET /tech-usuarios/por-cliente/{idcli}
```

### 2.5 Estadísticas de Usuarios
```http
GET /tech-usuarios/estadisticas
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "activos_vigentes": 120,
    "vencidos_sin_desactivar": 5,
    "total_registros_activos": 125,
    "por_servicio": [
      {"servicio": "NETFLIX", "cantidad": 50},
      {"servicio": "SPOTIFY", "cantidad": 35}
    ]
  }
}
```

---

## 3. Gestión de Ventas

### 3.1 Crear Nueva Venta
```http
POST /tech-ventas/crear
```

**Body:**
```json
{
  "idcli": "CLI-001",
  "empleado_id": 1,
  "detalles": [
    {
      "idper": "NETFLIX-1.2",
      "montodet": 5.00,
      "mesesdet": 1
    }
  ],
  "transaccion": {
    "idbanco": 1,
    "montotran": 5.00,
    "descripciontran": "Pago Netflix"
  }
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Venta creada exitosamente",
  "data": {
    "venta": {
      "idven": "001-001-000000123",
      "fecha": "2026-01-24 10:30:00",
      "total": 5.00,
      "estado": "COBRADO"
    },
    "detalles": [...],
    "transaccion": {
      "idtran": 456,
      "banco": 1,
      "monto": 5.00
    }
  }
}
```

### 3.2 Editar Venta
```http
PUT /tech-ventas/editar/{idven}
```

**Body:**
```json
{
  "estadoven": "COBRADO",
  "detalles": [
    {
      "iddet": "001-001-000000001.1",
      "montodet": 6.00,
      "activodet": true
    }
  ]
}
```

### 3.3 Detalle de Venta
```http
GET /tech-ventas/detalle/{idven}
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "venta": {
      "idven": "001-001-000000123",
      "fecha": "2026-01-24",
      "total": 5.00,
      "estado": "COBRADO"
    },
    "cliente": {
      "idcli": "CLI-001",
      "nombre": "Juan Pérez",
      "email": "juan@ejemplo.com",
      "telefono": "0999999999"
    },
    "empleado": {
      "idemp": 1,
      "nombre": "María González"
    },
    "detalles": [...],
    "transacciones": [...]
  }
}
```

### 3.4 Listar Ventas
```http
GET /tech-ventas/listar?fecha_desde=2026-01-01&estado=COBRADO&limit=50
```

**Query Params:**
- `fecha_desde`: YYYY-MM-DD
- `fecha_hasta`: YYYY-MM-DD
- `estado`: COBRADO, PENDIENTE, CANCELADO
- `idcli`: ID del cliente
- `limit`: Límite de resultados (default: 50)

### 3.5 Estadísticas de Ventas
```http
GET /tech-ventas/estadisticas?periodo=mes
```

**Query Params:**
- `periodo`: hoy, semana, mes, año (default: mes)

**Respuesta:**
```json
{
  "success": true,
  "periodo": "mes",
  "fecha_inicio": "2026-01-01",
  "data": {
    "total_ventas": 150,
    "monto_total": 750.00,
    "promedio_por_venta": 5.00,
    "por_estado": [
      {"estadoven": "COBRADO", "cantidad": 140, "monto": 700.00},
      {"estadoven": "PENDIENTE", "cantidad": 10, "monto": 50.00}
    ]
  }
}
```

---

## 4. Gestión de Productos

### 4.1 Cambiar Estado de Producto
```http
POST /tech-productos/cambiar-estado
```

**Body:**
```json
{
  "idprod": 1,
  "activo": true
}
```

### 4.2 Cambiar Estado Masivo por Servicio
```http
POST /tech-productos/cambiar-estado-masivo
```

**Body:**
```json
{
  "servicio": "NETFLIX",
  "activo": false,
  "tipo": "individual"
}
```

**Parámetros:**
- `tipo`: individual (1 mes) o combo (más meses)

**Respuesta:**
```json
{
  "success": true,
  "message": "Se desactivaron 5 productos",
  "count": 5,
  "productos": [
    {
      "idprod": 1,
      "nombre": "Netflix Individual 1 mes",
      "meses": 1,
      "estado_anterior": true
    }
  ]
}
```

### 4.3 Cambiar Precio de Producto
```http
POST /tech-productos/cambiar-precio
```

**Body:**
```json
{
  "idprod": 1,
  "precioprod": 5.50,
  "precio_combo": 10.00
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Precios actualizados exitosamente",
  "data": {
    "idprod": 1,
    "nombre": "Netflix Individual 1 mes",
    "precio_individual": {
      "anterior": 5.00,
      "actual": 5.50
    },
    "precio_combo": {
      "anterior": 9.00,
      "actual": 10.00
    }
  }
}
```

### 4.4 Cambiar Precios Masivos
```http
POST /tech-productos/cambiar-precio-masivo
```

**Body (Opción 1: Precio fijo):**
```json
{
  "servicio": "NETFLIX",
  "tipo": "individual",
  "nuevo_precio": 5.50
}
```

**Body (Opción 2: Incremento porcentual):**
```json
{
  "servicio": "NETFLIX",
  "tipo": "combo",
  "incremento_porcentaje": 10
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Se actualizaron 8 productos",
  "count": 8,
  "productos": [
    {
      "idprod": 1,
      "nombre": "Netflix Individual 1 mes",
      "meses": 1,
      "precio_anterior": 5.00,
      "precio_nuevo": 5.50
    }
  ]
}
```

### 4.5 Crear Producto
```http
POST /tech-productos/crear
```

**Body:**
```json
{
  "idval": "NETFLIX-Proveedor-com-1m",
  "nombreprod": "Netflix Premium 1 Mes",
  "mesesval": 1,
  "precioprod": 5.00,
  "precio_combo": null,
  "activoprod": true
}
```

### 4.6 Editar Producto
```http
PUT /tech-productos/editar/{idprod}
```

**Body:**
```json
{
  "nombreprod": "Netflix Premium 1 Mes",
  "precioprod": 5.50,
  "activoprod": true
}
```

### 4.7 Listar Productos
```http
GET /tech-productos/listar?servicio=NETFLIX&activo=true&tipo=individual
```

**Query Params:**
- `servicio`: Filtrar por servicio
- `activo`: true/false
- `tipo`: individual, combo

---

## 5. Configuración (Valores, Servicios, Proveedores)

### 5.1 VALORES

#### 5.1.1 Definir Pantallas Mínimas/Máximas
```http
POST /tech-config/valores/pantallas
```

**Body:**
```json
{
  "servicio": "NETFLIX",
  "min_pantallas": 1,
  "max_pantallas": 5
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Se actualizaron 10 valores",
  "data": {
    "servicio": "NETFLIX",
    "min_pantallas": 1,
    "max_pantallas": 5,
    "valores_actualizados": [
      {"idval": "NETFLIX-Proveedor-com-1m", "tipo": "completa", "meses": 1}
    ]
  }
}
```

#### 5.1.2 Crear Valor
```http
POST /tech-config/valores/crear
```

**Body:**
```json
{
  "idser": "NETFLIX",
  "idpro": 1,
  "tipoval": "premium",
  "mesesval": 1,
  "min_pantallas": 1,
  "max_pantallas": 5,
  "bot": "https://t.me/bot_netflix"
}
```

#### 5.1.3 Editar Valor
```http
PUT /tech-config/valores/editar/{idval}
```

**Body:**
```json
{
  "tipoval": "premium",
  "mesesval": 1,
  "min_pantallas": 2,
  "max_pantallas": 5,
  "bot": "https://t.me/bot_netflix"
}
```

#### 5.1.4 Listar Valores
```http
GET /tech-config/valores/listar?servicio=NETFLIX&proveedor=1
```

---

### 5.2 SERVICIOS

#### 5.2.1 Crear Servicio
```http
POST /tech-config/servicios/crear
```

**Body:**
```json
{
  "idser": "YOUTUBE",
  "nombreser": "YouTube Premium",
  "imagenser": "https://ejemplo.com/youtube.png"
}
```

#### 5.2.2 Editar Servicio
```http
PUT /tech-config/servicios/editar/{idser}
```

**Body:**
```json
{
  "nombreser": "YouTube Premium",
  "imagenser": "https://ejemplo.com/youtube.png"
}
```

#### 5.2.3 Listar Servicios
```http
GET /tech-config/servicios/listar
```

**Respuesta:**
```json
{
  "success": true,
  "count": 8,
  "servicios": [
    {
      "idser": "NETFLIX",
      "nombre": "Netflix",
      "imagen": "https://ejemplo.com/netflix.png"
    }
  ]
}
```

---

### 5.3 PROVEEDORES

#### 5.3.1 Crear Proveedor
```http
POST /tech-config/proveedores/crear
```

**Body:**
```json
{
  "nombrepro": "Proveedor Netflix Ecuador",
  "telefonopro": "0999999999",
  "direccionpro": "Quito, Ecuador"
}
```

#### 5.3.2 Editar Proveedor
```http
PUT /tech-config/proveedores/editar/{idpro}
```

**Body:**
```json
{
  "nombrepro": "Proveedor Netflix Ecuador",
  "telefonopro": "0999999999",
  "direccionpro": "Quito, Ecuador"
}
```

#### 5.3.3 Listar Proveedores
```http
GET /tech-config/proveedores/listar
```

**Respuesta:**
```json
{
  "success": true,
  "count": 5,
  "proveedores": [
    {
      "idpro": 1,
      "nombre": "Proveedor Netflix",
      "telefono": "0999999999",
      "direccion": "Quito"
    }
  ]
}
```

---

## Códigos de Estado HTTP

- `200 OK`: Operación exitosa
- `201 Created`: Recurso creado exitosamente
- `400 Bad Request`: Solicitud inválida
- `401 Unauthorized`: No autenticado
- `403 Forbidden`: Sin permisos
- `404 Not Found`: Recurso no encontrado
- `409 Conflict`: Conflicto (ej: perfil ocupado)
- `422 Unprocessable Entity`: Validación fallida
- `500 Internal Server Error`: Error del servidor

---

## Formato de Respuesta de Error

```json
{
  "success": false,
  "message": "Mensaje descriptivo del error",
  "error": "Detalles técnicos del error",
  "errors": {
    "campo": ["mensaje de validación"]
  }
}
```

---

## Ejemplos de Uso con cURL

### Desactivar usuarios vencidos (simulación)
```bash
curl -X POST http://localhost/api/v2/tech-usuarios/desactivar-vencidos \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "servicio": "NETFLIX",
    "dry_run": true
  }'
```

### Crear venta con transacción
```bash
curl -X POST http://localhost/api/v2/tech-ventas/crear \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "idcli": "CLI-001",
    "empleado_id": 1,
    "detalles": [
      {
        "idper": "NETFLIX-1.2",
        "montodet": 5.00,
        "mesesdet": 1
      }
    ],
    "transaccion": {
      "idbanco": 1,
      "montotran": 5.00,
      "descripciontran": "Pago Netflix"
    }
  }'
```

### Cambiar precios masivos con incremento
```bash
curl -X POST http://localhost/api/v2/tech-productos/cambiar-precio-masivo \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "servicio": "NETFLIX",
    "tipo": "individual",
    "incremento_porcentaje": 10
  }'
```

---

## Notas Importantes

1. **Modo Simulación (`dry_run`)**: Muchos endpoints de acciones masivas soportan el parámetro `dry_run: true` para ver qué cambios se aplicarían sin ejecutarlos realmente.

2. **Validaciones**: Todos los endpoints validan los datos de entrada y retornan errores 422 con detalles de validación.

3. **Transacciones**: Las operaciones que modifican múltiples registros usan transacciones de base de datos para garantizar consistencia.

4. **Triggers**: Al crear ventas, los triggers de base de datos calculan automáticamente los totales.

5. **Paginación**: Los endpoints de listado soportan el parámetro `limit` para controlar la cantidad de resultados.

---

## Siguiente Paso: Integración con N8N

Para integrar con N8N y crear un flujo de agente IA:

1. Crear un workflow en N8N
2. Usar nodos HTTP Request para llamar a estos endpoints
3. Configurar el API Key en los headers
4. Usar el nodo AI Agent para procesar las respuestas
5. Implementar lógica de decisión según las respuestas de la API

---

**Fecha de creación:** 2026-01-24  
**Versión:** 1.0.0  
**Autor:** Sistema Streamify
