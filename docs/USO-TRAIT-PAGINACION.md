# 🔧 IMPLEMENTACIÓN DEL TRAIT DE PAGINACIÓN

## 📝 Paso 1: El Trait ya está creado

**Ubicación**: `app/Traits/ApiPaginationTrait.php`

Este trait proporciona:
- ✅ `paginatedResponse()` - Paginación estándar
- ✅ `cursorPaginatedResponse()` - Paginación por cursor
- ✅ `simplePaginatedResponse()` - Paginación simple
- ✅ `getPerPage()` - Validar límites de paginación
- ✅ `getSortParams()` - Validar parámetros de ordenamiento
- ✅ `emptyPaginatedResponse()` - Respuesta vacía

---

## 📝 Paso 2: Cómo usarlo en tus Controllers

### Ejemplo 1: Refactorizar VentaApiController

**ANTES** (tu código actual):
```php
public function index(Request $request)
{
    try {
        $perPage = $request->input('per_page', 15);
        $query = Venta::with(['cliente', 'empleado', 'detalles_venta.perfil.cuenta']);

        // Filtros...
        if ($request->has('idcli')) {
            $query->where('idcli', $request->idcli);
        }

        $sortBy = $request->input('sort_by', 'fechaven');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $ventas = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $ventas->items(),
            'pagination' => [
                'total' => $ventas->total(),
                'per_page' => $ventas->perPage(),
                'current_page' => $ventas->currentPage(),
                'last_page' => $ventas->lastPage(),
                'from' => $ventas->firstItem(),
                'to' => $ventas->lastItem(),
            ]
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Error al obtener ventas',
            'message' => $e->getMessage()
        ], 500);
    }
}
```

**DESPUÉS** (usando el trait):
```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Traits\ApiPaginationTrait;  // ← Importar
use Illuminate\Http\Request;

class VentaApiController extends Controller
{
    use ApiPaginationTrait;  // ← Usar el trait

    public function index(Request $request)
    {
        try {
            // Validar y obtener per_page (entre 1 y 100)
            $perPage = $this->getPerPage($request, 15, 100);
            
            // Campos permitidos para ordenamiento
            $allowedSortFields = ['idven', 'fechaven', 'created_at'];
            $sort = $this->getSortParams($request, 'fechaven', 'desc', $allowedSortFields);
            
            // Construir query
            $query = Venta::with(['cliente', 'empleado', 'detalles_venta.perfil.cuenta']);

            // Filtros
            if ($request->has('idcli')) {
                $query->where('idcli', $request->idcli);
            }

            if ($request->has('idemp')) {
                $query->where('idemp', $request->idemp);
            }

            if ($request->has('fecha_inicio')) {
                $query->whereDate('fechaven', '>=', $request->fecha_inicio);
            }

            if ($request->has('fecha_fin')) {
                $query->whereDate('fechaven', '<=', $request->fecha_fin);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->whereHas('cliente', function ($q) use ($search) {
                    $q->where('nombrecli', 'LIKE', "%{$search}%")
                      ->orWhere('telefonocli', 'LIKE', "%{$search}%");
                });
            }

            // Ordenamiento
            $query->orderBy($sort['sort_by'], $sort['sort_order']);

            // Paginar
            $ventas = $query->paginate($perPage);

            // Retornar usando el trait (respuesta consistente + links)
            return $this->paginatedResponse($ventas, 'Ventas obtenidas exitosamente');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener ventas',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
```

**Beneficios**:
- ✅ Respuesta consistente en TODAS las APIs
- ✅ Links de navegación automáticos (first, last, prev, next)
- ✅ Validación automática de límites
- ✅ Validación de campos de ordenamiento
- ✅ Menos código repetitivo

---

### Ejemplo 2: ClienteApiController con el trait

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Traits\ApiPaginationTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClienteApiController extends Controller
{
    use ApiPaginationTrait;

    /**
     * Listar todos los clientes
     * GET /api/v1/clientes
     */
    public function index(Request $request)
    {
        try {
            $perPage = $this->getPerPage($request);
            $search = $request->input('search');

            $query = Cliente::query();

            // Búsqueda
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombrecli', 'like', "%{$search}%")
                      ->orWhere('telefonocli', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Ordenamiento
            $sort = $this->getSortParams($request, 'idcli', 'desc', ['idcli', 'nombrecli', 'created_at']);
            $query->orderBy($sort['sort_by'], $sort['sort_order']);

            $clientes = $query->paginate($perPage);

            return $this->paginatedResponse($clientes, 'Clientes obtenidos exitosamente');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener clientes',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
```

---

### Ejemplo 3: Usando Cursor Pagination para datasets grandes

```php
public function index(Request $request)
{
    try {
        $perPage = $this->getPerPage($request);
        $useCursor = $request->boolean('use_cursor', false);
        
        $query = Venta::with(['cliente', 'empleado']);
        
        // Aplicar filtros...
        
        // Ordenamiento (REQUERIDO para cursor)
        $sort = $this->getSortParams($request, 'fechaven', 'desc');
        $query->orderBy($sort['sort_by'], $sort['sort_order']);
        
        if ($useCursor) {
            // Usar paginación por cursor
            $ventas = $query->cursorPaginate($perPage);
            return $this->cursorPaginatedResponse($ventas, 'Ventas obtenidas exitosamente');
        } else {
            // Usar paginación normal
            $ventas = $query->paginate($perPage);
            return $this->paginatedResponse($ventas, 'Ventas obtenidas exitosamente');
        }
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Error al obtener ventas',
            'message' => $e->getMessage()
        ], 500);
    }
}
```

---

### Ejemplo 4: Paginación Simple (sin totales - más rápido)

```php
public function index(Request $request)
{
    try {
        $perPage = $this->getPerPage($request);
        
        $ventas = Venta::with(['cliente', 'empleado'])
            ->orderBy('fechaven', 'desc')
            ->simplePaginate($perPage);
        
        return $this->simplePaginatedResponse($ventas);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Error al obtener ventas',
            'message' => $e->getMessage()
        ], 500);
    }
}
```

---

## 📊 Comparación de Respuestas

### Con `paginatedResponse()`:
```json
{
  "success": true,
  "message": "Ventas obtenidas exitosamente",
  "data": [...],
  "pagination": {
    "total": 150,
    "per_page": 20,
    "current_page": 2,
    "last_page": 8,
    "from": 21,
    "to": 40,
    "has_more_pages": true
  },
  "links": {
    "first": "http://localhost/api/v1/ventas?page=1",
    "last": "http://localhost/api/v1/ventas?page=8",
    "prev": "http://localhost/api/v1/ventas?page=1",
    "next": "http://localhost/api/v1/ventas?page=3",
    "path": "http://localhost/api/v1/ventas"
  }
}
```

### Con `cursorPaginatedResponse()`:
```json
{
  "success": true,
  "message": "Ventas obtenidas exitosamente",
  "data": [...],
  "pagination": {
    "per_page": 20,
    "next_cursor": "eyJpZHZlbiI6MzAsIl9wb2ludHNUb05leHRJdGVtcyI6dHJ1ZX0",
    "prev_cursor": "eyJpZHZlbiI6MTAsIl9wb2ludHNUb05leHRJdGVtcyI6ZmFsc2V9",
    "has_more": true,
    "path": "http://localhost/api/v1/ventas"
  }
}
```

### Con `simplePaginatedResponse()`:
```json
{
  "success": true,
  "message": "Ventas obtenidas exitosamente",
  "data": [...],
  "pagination": {
    "per_page": 20,
    "current_page": 2,
    "has_more_pages": true
  },
  "links": {
    "prev": "http://localhost/api/v1/ventas?page=1",
    "next": "http://localhost/api/v1/ventas?page=3",
    "path": "http://localhost/api/v1/ventas"
  }
}
```

---

## 🎯 Métodos del Trait

### `getPerPage($request, $default = 15, $max = 100)`

**Validar límites de paginación**

```php
// Uso básico (default: 15, max: 100)
$perPage = $this->getPerPage($request);

// Personalizado (default: 20, max: 50)
$perPage = $this->getPerPage($request, 20, 50);
```

**Resultado**:
- Request: `?per_page=30` → Retorna: `30`
- Request: `?per_page=150` → Retorna: `100` (límite máximo)
- Request: `?per_page=-5` → Retorna: `1` (límite mínimo)
- Sin parámetro → Retorna: `15` (default)

---

### `getSortParams($request, $defaultSortBy, $defaultSortOrder, $allowedFields = [])`

**Validar parámetros de ordenamiento**

```php
// Sin restricción de campos
$sort = $this->getSortParams($request, 'created_at', 'desc');

// Con campos permitidos (recomendado)
$allowedFields = ['idven', 'fechaven', 'created_at'];
$sort = $this->getSortParams($request, 'fechaven', 'desc', $allowedFields);

// Usar en query
$query->orderBy($sort['sort_by'], $sort['sort_order']);
```

**Resultado**:
```php
[
    'sort_by' => 'fechaven',
    'sort_order' => 'desc'
]
```

---

## 🚀 Aplicar en TODOS tus Controllers API

### Checklist de Migración:

1. **VentaApiController** - ✅ Listo para actualizar
2. **ClienteApiController** - ✅ Listo para actualizar
3. **ProductoApiController** - ⏳ Cuando lo crees, úsalo desde el inicio
4. **CuentaApiController** - ⏳ Cuando lo crees, úsalo desde el inicio
5. **EmpleadoApiController** - ⏳ Cuando lo crees, úsalo desde el inicio

### Template para nuevos controllers:

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TuModelo;
use App\Traits\ApiPaginationTrait;
use Illuminate\Http\Request;

class TuModeloApiController extends Controller
{
    use ApiPaginationTrait;

    public function index(Request $request)
    {
        try {
            $perPage = $this->getPerPage($request);
            $sort = $this->getSortParams($request, 'created_at', 'desc', ['id', 'nombre', 'created_at']);
            
            $query = TuModelo::query();
            
            // Filtros personalizados aquí...
            
            $query->orderBy($sort['sort_by'], $sort['sort_order']);
            $items = $query->paginate($perPage);
            
            return $this->paginatedResponse($items);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener datos',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
```

---

## 🧪 Testing

### Probar los diferentes tipos de paginación:

```bash
# Paginación normal
curl -H "X-API-Key: tu-key" "http://localhost/api/v1/ventas?page=2&per_page=20"

# Con ordenamiento
curl -H "X-API-Key: tu-key" "http://localhost/api/v1/ventas?sort_by=fechaven&sort_order=asc"

# Con cursor
curl -H "X-API-Key: tu-key" "http://localhost/api/v1/ventas?use_cursor=true&per_page=20"

# Límite excedido (retorna max: 100)
curl -H "X-API-Key: tu-key" "http://localhost/api/v1/ventas?per_page=500"
```

---

## 📝 Ventajas de usar este Trait

✅ **Consistencia**: Todas las APIs retornan el mismo formato
✅ **DRY**: No repetir código de paginación en cada controller
✅ **Validación**: Límites automáticos y campos de ordenamiento seguros
✅ **Links**: URLs de navegación automáticas
✅ **Flexibilidad**: Soporta 3 tipos de paginación
✅ **Mantenibilidad**: Un solo lugar para actualizar formato de paginación

---

**Última actualización**: Diciembre 4, 2025
**Archivo**: `app/Traits/ApiPaginationTrait.php`
