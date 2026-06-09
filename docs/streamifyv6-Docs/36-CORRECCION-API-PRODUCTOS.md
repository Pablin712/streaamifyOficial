# TecnicoProductosController - Documentación Corregida

## Estructura de Modelos

```
Producto (productos)
  └── hasMany → DetalleProducto (detalle_productos)
        └── belongsTo → Servicio (servicios)
```

### Producto Individual
- Tiene **1 DetalleProducto** (un único servicio)
- Ejemplo: "Netflix Premium" con 1 servicio Netflix

### Producto Combo
- Tiene **múltiples DetalleProducto** (varios servicios)
- Ejemplo: "Combo Premium" con Netflix + Max + Disney+

## Campos de la Tabla `productos`

```sql
- id (PK)
- codigopro (string, único)
- nombrepro (string)
- preciopro (decimal)
- activo (boolean)
- tipo_producto_id (FK)
- categoria_id (FK)
- estrellaspro (int)
- descripcionpro (text)
- foto (string)
```

## Campos de la Tabla `detalle_productos`

```sql
- id (PK)
- producto_id (FK → productos.id)
- idser (FK → servicios.idser)
- descripcion (string)
- meses (int)
```

---

## Endpoints Corregidos

### 1. Cambiar Estado Individual
**POST** `/api/v2/tech-productos/cambiar-estado`

```json
{
  "idprod": 1,
  "activo": true
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Producto activado",
  "data": {
    "id": 1,
    "codigo": "NETFLIX",
    "nombre": "Netflix Premium",
    "estado_anterior": false,
    "estado_actual": true
  }
}
```

---

### 2. Cambiar Estado Masivo (CORREGIDO)
**POST** `/api/v2/tech-productos/cambiar-estado-masivo`

```json
{
  "servicio": "NETFLIX",
  "activo": true,
  "tipo": "individual"
}
```

**Query corregida:**
```php
// ANTES (INCORRECTO):
Producto::whereHas('valor.servicio', function($q) {...})

// AHORA (CORRECTO):
Producto::whereHas('detalles.servicio', function($q) {...})
  ->has('detalles', '=', 1); // individual
  // o
  ->has('detalles', '>', 1); // combo
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Se activaron 3 productos",
  "count": 3,
  "productos": [
    {
      "id": 1,
      "codigo": "NETFLIX",
      "nombre": "Netflix Premium",
      "tipo": "individual",
      "num_servicios": 1,
      "estado_anterior": false
    }
  ]
}
```

---

### 3. Cambiar Precio Individual
**POST** `/api/v2/tech-productos/cambiar-precio`

```json
{
  "idprod": 1,
  "preciopro": 5.50
}
```

**Campo corregido:**
- ✅ `preciopro` (único campo de precio en productos)
- ❌ ~~`precioprod`~~ (no existe)
- ❌ ~~`precio_combo`~~ (no existe)
- ❌ ~~`precio_individual`~~ (no existe)

---

### 4. Cambiar Precio Masivo (CORREGIDO)
**POST** `/api/v2/tech-productos/cambiar-precio-masivo`

```json
{
  "servicio": "NETFLIX",
  "tipo": "individual",
  "nuevo_precio": 5.50
}
```

O con incremento porcentual:
```json
{
  "servicio": "NETFLIX",
  "tipo": "combo",
  "incremento_porcentaje": 10
}
```

**Query corregida:**
```php
// ANTES (INCORRECTO):
Producto::whereHas('valor.servicio', function($q) {...})
  ->where('mesesval', 1); // campo no existe

// AHORA (CORRECTO):
Producto::whereHas('detalles.servicio', function($q) {...})
  ->has('detalles', '=', 1); // individual
```

---

### 5. Crear Producto (NUEVO FORMATO)
**POST** `/api/v2/tech-productos/crear`

```json
{
  "codigopro": "PROD-001",
  "nombrepro": "Netflix Premium Individual",
  "preciopro": 15.99,
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

**Para un Combo:**
```json
{
  "codigopro": "COMBO-PREMIUM",
  "nombrepro": "Combo Premium Netflix + Max",
  "preciopro": 25.99,
  "activo": true,
  "tipo_producto_id": 1,
  "categoria_id": 1,
  "servicios": [
    {
      "idser": 1,
      "meses": 1,
      "descripcion": "Netflix Premium"
    },
    {
      "idser": 2,
      "meses": 1,
      "descripcion": "Max Premium"
    }
  ]
}
```

**Validaciones:**
```php
'codigopro' => 'required|string|max:50|unique:productos,codigopro'
'nombrepro' => 'required|string|max:255'
'preciopro' => 'required|numeric|min:0'
'servicios' => 'required|array|min:1'
'servicios.*.idser' => 'required|integer|exists:servicios,idser'
```

---

### 6. Editar Producto
**PUT** `/api/v2/tech-productos/editar/{idprod}`

```json
{
  "nombrepro": "Netflix Premium Actualizado",
  "preciopro": 17.99,
  "activo": true
}
```

**Nota:** No edita los servicios (detalles), solo los campos del producto.

---

### 7. Listar Productos (CORREGIDO)
**GET** `/api/v2/tech-productos/listar?servicio=NETFLIX&activo=true&tipo=individual`

**Query corregida:**
```php
// ANTES (INCORRECTO):
Producto::with(['valor.servicio'])
  ->whereHas('valor.servicio', function($q) {...})
  ->where('mesesval', 1);

// AHORA (CORRECTO):
Producto::with(['detalles.servicio'])
  ->whereHas('detalles.servicio', function($q) {...})
  ->has('detalles', '=', 1);
```

**Respuesta:**
```json
{
  "success": true,
  "count": 3,
  "productos": [
    {
      "id": 1,
      "codigo": "NETFLIX",
      "nombre": "Netflix Premium",
      "precio": 3.50,
      "activo": true,
      "tipo": "individual",
      "servicios": [
        {
          "nombre": "Netflix Premium",
          "meses": 1,
          "descripcion": "Netflix es la plataforma más popular..."
        }
      ]
    }
  ]
}
```

---

## Cambios Principales Realizados

### ❌ ANTES (Incorrecto)
```php
// Usaba relación inexistente
Producto::whereHas('valor.servicio', ...)

// Campos que no existen
$producto->mesesval
$producto->precio_combo
$producto->precioprod
$producto->idval
$producto->activoprod
```

### ✅ AHORA (Correcto)
```php
// Usa la cadena correcta de relaciones
Producto::whereHas('detalles.servicio', ...)

// Campos reales
$producto->id (PK)
$producto->codigopro
$producto->nombrepro
$producto->preciopro (único precio)
$producto->activo
$producto->tipo_producto_id
$producto->categoria_id

// Detectar tipo por cantidad de detalles
$producto->detalles->count() === 1 // Individual
$producto->detalles->count() > 1   // Combo
```

---

## Prueba con cURL

### Cambiar estado masivo de productos Netflix
```bash
curl -X POST http://localhost/api/v2/tech-productos/cambiar-estado-masivo \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_API_KEY" \
  -d '{
    "servicio": "NETFLIX",
    "tipo": "individual",
    "activo": true
  }'
```

### Cambiar precio masivo con incremento del 15%
```bash
curl -X POST http://localhost/api/v2/tech-productos/cambiar-precio-masivo \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_API_KEY" \
  -d '{
    "servicio": "NETFLIX",
    "tipo": "individual",
    "incremento_porcentaje": 15
  }'
```

### Listar productos activos de Netflix
```bash
curl -X GET "http://localhost/api/v2/tech-productos/listar?servicio=NETFLIX&activo=true&tipo=individual" \
  -H "Authorization: Bearer TU_API_KEY"
```

---

## Integración con N8N

### Nodo HTTP Request para cambiar estado masivo
```json
{
  "method": "POST",
  "url": "http://localhost/api/v2/tech-productos/cambiar-estado-masivo",
  "headers": {
    "Content-Type": "application/json",
    "Authorization": "Bearer {{$credentials.apiKey}}"
  },
  "body": {
    "servicio": "{{$json.servicio}}",
    "tipo": "{{$json.tipo}}",
    "activo": true
  }
}
```

---

## Estado Actual

✅ **CORREGIDO Y FUNCIONANDO:**
1. cambiarEstado - ✅
2. cambiarEstadoMasivo - ✅ (usa `detalles.servicio`)
3. cambiarPrecio - ✅
4. cambiarPrecioMasivo - ✅ (usa `detalles.servicio`)
5. crear - ✅ (con transacciones DB)
6. editar - ✅
7. listar - ✅ (usa `detalles.servicio`)

**Pruebas realizadas:**
- ✅ Relaciones Producto → DetalleProducto → Servicio funcionan
- ✅ Script de prueba ejecutado exitosamente
- ✅ Detección de tipo individual/combo por cantidad de detalles
