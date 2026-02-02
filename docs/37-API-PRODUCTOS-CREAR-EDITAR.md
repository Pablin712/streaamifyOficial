# API PRODUCTOS - CREAR Y EDITAR (Agente IA)

## 📋 Resumen

APIs para que el agente IA pueda crear y editar productos en el sistema.

**Base URL:** `https://streamify.aaronsoft.es/api/v2/tech-productos`

---

## 1. CREAR PRODUCTO

### Endpoint
```
POST /api/v2/tech-productos/crear
```

### Headers
```json
{
  "Content-Type": "application/json",
  "Accept": "application/json"
}
```

### Body (Request)

#### Ejemplo 1: Producto Individual (1 servicio, 1 mes)
```json
{
  "codigopro": "NETFLIX-PREM-1M",
  "nombrepro": "Netflix Premium 1 Mes",
  "preciopro": 5.99,
  "activo": true,
  "tipo_producto_id": 1,
  "categoria_id": 1,
  "servicios": [
    {
      "idser": 1,
      "meses": 1,
      "descripcion": "Plan Premium"
    }
  ]
}
```

#### Ejemplo 2: Producto Individual (1 servicio, 6 meses)
```json
{
  "codigopro": "MAX-STAND-6M",
  "nombrepro": "Max Standard 6 Meses",
  "preciopro": 21.59,
  "activo": true,
  "tipo_producto_id": 1,
  "categoria_id": 1,
  "servicios": [
    {
      "idser": 2,
      "meses": 6,
      "descripcion": "Plan Standard 6 meses"
    }
  ]
}
```

#### Ejemplo 3: Producto Combo (múltiples servicios)
```json
{
  "codigopro": "COMBO-STREAM-PREMIUM",
  "nombrepro": "Combo Streaming Premium",
  "preciopro": 14.99,
  "activo": true,
  "tipo_producto_id": 2,
  "categoria_id": 2,
  "servicios": [
    {
      "idser": 1,
      "meses": 1,
      "descripcion": "Netflix Premium"
    },
    {
      "idser": 2,
      "meses": 1,
      "descripcion": "Max Standard"
    },
    {
      "idser": 3,
      "meses": 1,
      "descripcion": "Disney+ Premium"
    }
  ]
}
```

### Campos del Body

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `codigopro` | string | ✅ | Código único del producto (max 50 chars) |
| `nombrepro` | string | ✅ | Nombre del producto (max 255 chars) |
| `preciopro` | decimal | ✅ | Precio del producto (min: 0) |
| `activo` | boolean | ❌ | Estado del producto (default: true) |
| `tipo_producto_id` | integer | ✅ | ID del tipo de producto |
| `categoria_id` | integer | ✅ | ID de la categoría |
| `servicios` | array | ✅ | Array de servicios (min: 1) |
| `servicios[].idser` | integer | ✅ | ID del servicio |
| `servicios[].meses` | integer | ❌ | Duración en meses (default: 1, min: 1) |
| `servicios[].descripcion` | string | ❌ | Descripción adicional (max 255 chars) |

### Response Exitoso (201)
```json
{
  "success": true,
  "message": "Producto creado exitosamente",
  "data": {
    "id": 123,
    "codigo": "NETFLIX-PREM-1M",
    "nombre": "Netflix Premium 1 Mes",
    "precio": 5.99,
    "activo": true,
    "tipo": "individual",
    "servicios": [
      {
        "id": 456,
        "servicio": "NETFLIX",
        "meses": 1,
        "descripcion": "Plan Premium"
      }
    ]
  }
}
```

### Response Error (422 - Validación)
```json
{
  "success": false,
  "message": "Validación fallida",
  "errors": {
    "codigopro": [
      "El campo codigopro ya ha sido tomado."
    ],
    "servicios.0.idser": [
      "El servicio seleccionado no existe."
    ]
  }
}
```

### Response Error (500 - Servidor)
```json
{
  "success": false,
  "message": "Error al crear producto",
  "error": "Descripción del error técnico"
}
```

---

## 2. EDITAR PRODUCTO

### Endpoint
```
PUT /api/v2/tech-productos/editar/{idprod}
```

### Headers
```json
{
  "Content-Type": "application/json",
  "Accept": "application/json"
}
```

### Path Parameters
| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `idprod` | integer | ID del producto a editar |

### Body (Request)

Todos los campos son **opcionales** - solo envía los campos que quieres actualizar.

#### Ejemplo 1: Cambiar solo el precio
```json
{
  "preciopro": 6.99
}
```

#### Ejemplo 2: Cambiar nombre y precio
```json
{
  "nombrepro": "Netflix Premium 1 Mes (Actualizado)",
  "preciopro": 6.49
}
```

#### Ejemplo 3: Actualización completa
```json
{
  "codigopro": "NETFLIX-PREM-1M-V2",
  "nombrepro": "Netflix Premium 1 Mes Renovado",
  "preciopro": 7.99,
  "activo": false,
  "tipo_producto_id": 1,
  "categoria_id": 1
}
```

### Campos del Body (todos opcionales)

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `codigopro` | string | Código único del producto (max 50 chars) |
| `nombrepro` | string | Nombre del producto (max 255 chars) |
| `preciopro` | decimal | Precio del producto (min: 0) |
| `activo` | boolean | Estado del producto |
| `tipo_producto_id` | integer | ID del tipo de producto |
| `categoria_id` | integer | ID de la categoría |

**⚠️ NOTA:** Esta API NO permite editar los servicios del producto. Para modificar servicios, necesitas:
1. Eliminar el producto antiguo
2. Crear uno nuevo con los servicios actualizados

O usar la API de `DetalleProducto` directamente.

### Response Exitoso (200)
```json
{
  "success": true,
  "message": "Producto actualizado exitosamente",
  "data": {
    "id": 123,
    "codigo": "NETFLIX-PREM-1M-V2",
    "nombre": "Netflix Premium 1 Mes Renovado",
    "precio": 7.99,
    "activo": false,
    "tipo": "individual",
    "num_servicios": 1
  }
}
```

### Response Error (404 - No encontrado)
```json
{
  "success": false,
  "message": "No query results for model [App\\Models\\Producto] 999"
}
```

### Response Error (422 - Validación)
```json
{
  "success": false,
  "message": "Validación fallida",
  "errors": {
    "preciopro": [
      "El campo preciopro debe ser al menos 0."
    ]
  }
}
```

---

## 3. IDs DE REFERENCIA

### Tipos de Producto (tipo_producto_id)
```sql
SELECT * FROM tipo_productos;
```
| ID | Nombre |
|----|--------|
| 1  | Individual |
| 2  | Combo |

### Categorías (categoria_id)
```sql
SELECT * FROM categorias;
```
| ID | Nombre |
|----|--------|
| 1  | Individual |
| 2  | Combos |

### Servicios (idser)
```sql
SELECT idser, nombreser FROM servicios;
```
Ejemplos comunes:
| idser | nombreser |
|-------|-----------|
| 1     | NETFLIX |
| 2     | MAX |
| 3     | DISNEY+ |
| 4     | PRIME VIDEO |
| 5     | SPOTIFY |
| 6     | YOUTUBE PREMIUM |

**⚠️ IMPORTANTE:** Siempre verifica los IDs disponibles en la base de datos antes de crear productos.

---

## 4. EJEMPLOS DE USO PARA AGENTE IA

### Escenario 1: Usuario pide "Crear Netflix Premium 1 mes a $5.99"

**Paso 1:** Identificar el servicio
```bash
GET /api/v2/tech-config/servicios/listar?activo=true
# Buscar servicio NETFLIX
```

**Paso 2:** Crear el producto
```bash
POST /api/v2/tech-productos/crear
{
  "codigopro": "NETFLIX-PREM-1M",
  "nombrepro": "Netflix Premium 1 Mes",
  "preciopro": 5.99,
  "activo": true,
  "tipo_producto_id": 1,
  "categoria_id": 1,
  "servicios": [
    {
      "idser": 1,
      "meses": 1
    }
  ]
}
```

### Escenario 2: Usuario pide "Actualizar el precio de Netflix Premium a $6.99"

**Paso 1:** Buscar el producto
```bash
GET /api/v2/tech-productos/listar?servicio=NETFLIX
# Identificar el ID del producto
```

**Paso 2:** Actualizar solo el precio
```bash
PUT /api/v2/tech-productos/editar/123
{
  "preciopro": 6.99
}
```

### Escenario 3: Usuario pide "Crear combo de Netflix + Max + Disney a $14.99"

**Paso 1:** Identificar los servicios
```bash
GET /api/v2/tech-config/servicios/listar
# Buscar NETFLIX, MAX, DISNEY+
# IDs: 1, 2, 3
```

**Paso 2:** Crear el combo
```bash
POST /api/v2/tech-productos/crear
{
  "codigopro": "COMBO-STREAM-3",
  "nombrepro": "Combo Streaming Premium",
  "preciopro": 14.99,
  "activo": true,
  "tipo_producto_id": 2,
  "categoria_id": 2,
  "servicios": [
    {
      "idser": 1,
      "meses": 1,
      "descripcion": "Netflix Premium"
    },
    {
      "idser": 2,
      "meses": 1,
      "descripcion": "Max Standard"
    },
    {
      "idser": 3,
      "meses": 1,
      "descripcion": "Disney+ Premium"
    }
  ]
}
```

### Escenario 4: Usuario pide "Desactivar el producto Netflix Premium"

```bash
PUT /api/v2/tech-productos/editar/123
{
  "activo": false
}
```

---

## 5. VALIDACIONES Y REGLAS DE NEGOCIO

### Código de Producto (codigopro)
- **DEBE** ser único en toda la tabla `productos`
- Máximo 50 caracteres
- Se recomienda formato: `SERVICIO-PLAN-DURACION`
  - Ejemplo: `NETFLIX-PREM-1M`, `MAX-STAND-6M`, `COMBO-STREAM-3`

### Precio (preciopro)
- Debe ser mayor o igual a 0
- Se guarda con 2 decimales (ejemplo: 5.99, 14.99)

### Servicios
- Al menos 1 servicio es requerido
- Para productos **individuales**: usar `tipo_producto_id: 1` y `categoria_id: 1`
- Para productos **combo**: usar `tipo_producto_id: 2` y `categoria_id: 2`

### Meses
- Valor por defecto: 1
- Afecta el cálculo de precio en productos individuales multi-mes
- Para combos, generalmente se usa 1 mes por servicio

---

## 6. PRUEBAS CON CURL

### Crear Producto Individual
```bash
curl -X POST https://streamify.aaronsoft.es/api/v2/tech-productos/crear \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "codigopro": "TEST-NETFLIX-1M",
    "nombrepro": "Test Netflix Premium 1 Mes",
    "preciopro": 5.99,
    "activo": true,
    "tipo_producto_id": 1,
    "categoria_id": 1,
    "servicios": [
      {
        "idser": 1,
        "meses": 1,
        "descripcion": "Plan Premium"
      }
    ]
  }'
```

### Editar Producto
```bash
curl -X PUT https://streamify.aaronsoft.es/api/v2/tech-productos/editar/123 \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "preciopro": 6.99,
    "activo": true
  }'
```

### Verificar Producto Creado
```bash
curl -X GET https://streamify.aaronsoft.es/api/v2/tech-productos/obtener/123 \
  -H "Accept: application/json"
```

---

## 7. ERRORES COMUNES

| Error | Causa | Solución |
|-------|-------|----------|
| "El campo codigopro ya ha sido tomado" | Código duplicado | Usar un código único |
| "El servicio seleccionado no existe" | idser inválido | Verificar IDs con `/tech-config/servicios/listar` |
| "El campo servicios es obligatorio" | Array vacío o faltante | Enviar al menos 1 servicio |
| "No query results for model" | ID de producto no existe | Verificar el ID con `/listar` |
| "Validation failed" | Campos mal formateados | Revisar tipos de datos (string, integer, boolean) |

---

## 8. TRANSACCIONES Y SEGURIDAD

### Transacciones de Base de Datos
- Ambas APIs usan **DB::beginTransaction()**
- Si falla alguna operación, se hace **rollback** automático
- Garantiza integridad de datos (producto + detalles)

### Validaciones
- Todos los campos son validados antes de crear/actualizar
- Se verifica que los servicios existan en la tabla `servicios`
- Se verifica que tipo_producto_id y categoria_id existan

---

## 9. APIS RELACIONADAS

Para un flujo completo de gestión de productos, el agente IA puede usar:

1. **Listar productos:** `GET /api/v2/tech-productos/listar`
2. **Obtener producto:** `GET /api/v2/tech-productos/obtener/{id}`
3. **Crear producto:** `POST /api/v2/tech-productos/crear` (esta API)
4. **Editar producto:** `PUT /api/v2/tech-productos/editar/{id}` (esta API)
5. **Cambiar estado:** `POST /api/v2/tech-productos/cambiar-estado`
6. **Cambiar precio:** `POST /api/v2/tech-productos/cambiar-precio`
7. **Listar servicios:** `GET /api/v2/tech-config/servicios/listar`

---

## 📝 NOTAS FINALES

- Las APIs están listas para producción
- No requieren autenticación con token (como solicitaste para n8n)
- Todos los cambios se registran con `created_at` y `updated_at`
- El campo `tipo` en las respuestas es calculado automáticamente:
  - `individual`: 1 servicio
  - `combo`: 2+ servicios

**Última actualización:** 2 de febrero de 2026
