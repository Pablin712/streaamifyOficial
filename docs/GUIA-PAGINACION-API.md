# 📖 GUÍA COMPLETA DE PAGINACIÓN EN APIs REST - Laravel

## 🎯 Conceptos Fundamentales

La paginación en APIs REST es esencial para:
- ✅ **Performance**: Evitar consultas pesadas
- ✅ **Experiencia de usuario**: Cargas rápidas
- ✅ **Escalabilidad**: Manejo eficiente de grandes datasets
- ✅ **Costos**: Reducir transferencia de datos

---

## 📚 TIPOS DE PAGINACIÓN

### 1️⃣ **Paginación Basada en Páginas (Offset-Based)**

**✅ LO QUE YA TIENES IMPLEMENTADO**

```php
// Controller
$perPage = $request->input('per_page', 15);
$ventas = Venta::paginate($perPage);

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
]);
```

**Request:**
```
GET /api/v1/ventas?page=2&per_page=20
```

**Response:**
```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "total": 150,
    "per_page": 20,
    "current_page": 2,
    "last_page": 8,
    "from": 21,
    "to": 40
  }
}
```

**✅ Ventajas:**
- Fácil de implementar
- Navegación directa a cualquier página
- Conoces el total de registros
- Compatible con interfaces de paginación clásicas

**❌ Desventajas:**
- Ineficiente con datasets grandes (OFFSET lento)
- Puede mostrar duplicados si se insertan registros
- No recomendado para más de 100,000 registros

---

### 2️⃣ **Paginación por Cursor (Cursor-Based)**

**🚀 RECOMENDADO PARA ALTA PERFORMANCE**

```php
// Controller mejorado para VentaApiController
public function index(Request $request)
{
    try {
        // Detectar si se usa cursor o paginación normal
        $useCursor = $request->boolean('use_cursor', false);
        $perPage = $request->input('per_page', 15);
        
        $query = Venta::with(['cliente', 'empleado', 'detalles_venta.perfil.cuenta']);
        
        // Aplicar filtros...
        if ($request->has('idcli')) {
            $query->where('idcli', $request->idcli);
        }
        
        // Ordenamiento (requerido para cursor)
        $sortBy = $request->input('sort_by', 'fechaven');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        if ($useCursor) {
            // Paginación por cursor
            $ventas = $query->cursorPaginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $ventas->items(),
                'pagination' => [
                    'per_page' => $ventas->perPage(),
                    'next_cursor' => $ventas->nextCursor()?->encode(),
                    'prev_cursor' => $ventas->previousCursor()?->encode(),
                    'has_more' => $ventas->hasMorePages(),
                ]
            ]);
        } else {
            // Paginación normal (offset)
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
            ]);
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

**Request con Cursor:**
```
GET /api/v1/ventas?use_cursor=true&per_page=20
GET /api/v1/ventas?use_cursor=true&cursor=eyJpZHZlbiI6MTAsIl9wb2ludHNUb05leHRJdGVtcyI6dHJ1ZX0
```

**Response:**
```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "per_page": 20,
    "next_cursor": "eyJpZHZlbiI6MzAsIl9wb2ludHNUb05leHRJdGVtcyI6dHJ1ZX0",
    "prev_cursor": "eyJpZHZlbiI6MTAsIl9wb2ludHNUb05leHRJdGVtcyI6ZmFsc2V9",
    "has_more": true
  }
}
```

**✅ Ventajas:**
- **Muy rápido** con datasets grandes
- No hay saltos de registros
- Perfecto para scroll infinito
- Performance constante sin importar la página

**❌ Desventajas:**
- No puedes ir directamente a página X
- No conoces el total de registros
- Requiere ordenamiento consistente

---

### 3️⃣ **Paginación Simple (sin totales)**

**⚡ MÁS RÁPIDO - Sin COUNT()**

```php
// Para listas muy grandes donde no necesitas el total
public function index(Request $request)
{
    $perPage = $request->input('per_page', 15);
    
    $ventas = Venta::with(['cliente', 'empleado'])
        ->orderBy('fechaven', 'desc')
        ->simplePaginate($perPage);
    
    return response()->json([
        'success' => true,
        'data' => $ventas->items(),
        'pagination' => [
            'per_page' => $ventas->perPage(),
            'current_page' => $ventas->currentPage(),
            'has_more_pages' => $ventas->hasMorePages(),
            'next_page_url' => $ventas->nextPageUrl(),
            'prev_page_url' => $ventas->previousPageUrl(),
        ]
    ]);
}
```

**✅ Ventajas:**
- No ejecuta COUNT(*) (más rápido)
- Bueno para móviles (botones Anterior/Siguiente)

**❌ Desventajas:**
- No sabes cuántas páginas hay
- No sabes el total de registros

---

## 🎨 FORMATOS DE RESPUESTA ESTÁNDAR

### Opción 1: **Estructura Plana** (TU IMPLEMENTACIÓN ACTUAL)

```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "total": 150,
    "per_page": 20,
    "current_page": 2,
    "last_page": 8,
    "from": 21,
    "to": 40
  }
}
```

### Opción 2: **JSON:API Standard**

```json
{
  "data": [...],
  "meta": {
    "total": 150,
    "per_page": 20,
    "current_page": 2,
    "last_page": 8
  },
  "links": {
    "first": "http://api.example.com/ventas?page=1",
    "last": "http://api.example.com/ventas?page=8",
    "prev": "http://api.example.com/ventas?page=1",
    "next": "http://api.example.com/ventas?page=3"
  }
}
```

### Opción 3: **Laravel API Resource** (RECOMENDADO)

Crear un Resource para respuestas consistentes:

```php
// app/Http/Resources/VentaCollection.php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class VentaCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray($request)
    {
        return [
            'success' => true,
            'data' => $this->collection,
            'pagination' => [
                'total' => $this->total(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
                'from' => $this->firstItem(),
                'to' => $this->lastItem(),
            ],
            'links' => [
                'first' => $this->url(1),
                'last' => $this->url($this->lastPage()),
                'prev' => $this->previousPageUrl(),
                'next' => $this->nextPageUrl(),
            ],
        ];
    }
}
```

**Uso en el Controller:**
```php
use App\Http\Resources\VentaCollection;

public function index(Request $request)
{
    $ventas = Venta::with(['cliente', 'empleado'])->paginate(15);
    return new VentaCollection($ventas);
}
```

---

## 🔧 MEJORAS PARA TU IMPLEMENTACIÓN ACTUAL

### 1. **Validar límites de paginación**

```php
public function index(Request $request)
{
    // Validar que per_page esté en rango válido
    $perPage = $request->input('per_page', 15);
    $perPage = min(max($perPage, 1), 100); // Entre 1 y 100
    
    $ventas = Venta::paginate($perPage);
    
    // ... resto del código
}
```

### 2. **Agregar metadata adicional**

```php
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
        // NUEVO: Links de navegación
        'first_page_url' => $ventas->url(1),
        'last_page_url' => $ventas->url($ventas->lastPage()),
        'next_page_url' => $ventas->nextPageUrl(),
        'prev_page_url' => $ventas->previousPageUrl(),
        // NUEVO: Información de rango
        'path' => $ventas->path(),
    ]
]);
```

### 3. **Trait reutilizable para paginación**

```php
// app/Traits/ApiPaginationTrait.php
<?php

namespace App\Traits;

trait ApiPaginationTrait
{
    /**
     * Formatear respuesta paginada estándar
     */
    protected function paginatedResponse($paginator, $message = 'Datos obtenidos exitosamente')
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'has_more_pages' => $paginator->hasMorePages(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ]
        ]);
    }
    
    /**
     * Formatear respuesta con cursor
     */
    protected function cursorPaginatedResponse($paginator, $message = 'Datos obtenidos exitosamente')
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'per_page' => $paginator->perPage(),
                'next_cursor' => $paginator->nextCursor()?->encode(),
                'prev_cursor' => $paginator->previousCursor()?->encode(),
                'has_more' => $paginator->hasMorePages(),
            ]
        ]);
    }
}
```

**Uso en Controller:**
```php
use App\Traits\ApiPaginationTrait;

class VentaApiController extends Controller
{
    use ApiPaginationTrait;
    
    public function index(Request $request)
    {
        $ventas = Venta::paginate(15);
        return $this->paginatedResponse($ventas);
    }
}
```

---

## 🎯 PARÁMETROS DE QUERY RECOMENDADOS

### Estándar que deberías usar en todos tus endpoints:

| Parámetro | Tipo | Default | Descripción | Ejemplo |
|-----------|------|---------|-------------|---------|
| `page` | int | 1 | Número de página | `?page=2` |
| `per_page` | int | 15 | Resultados por página | `?per_page=20` |
| `sort_by` | string | - | Campo para ordenar | `?sort_by=fechaven` |
| `sort_order` | string | desc | Dirección (asc/desc) | `?sort_order=asc` |
| `cursor` | string | - | Cursor para paginación | `?cursor=eyJ...` |
| `use_cursor` | bool | false | Usar cursor en vez de offset | `?use_cursor=true` |

### Ejemplo de validación completa:

```php
public function index(Request $request)
{
    $validated = $request->validate([
        'page' => 'sometimes|integer|min:1',
        'per_page' => 'sometimes|integer|min:1|max:100',
        'sort_by' => 'sometimes|string|in:idven,fechaven,created_at',
        'sort_order' => 'sometimes|string|in:asc,desc',
        'use_cursor' => 'sometimes|boolean',
    ]);
    
    $perPage = $validated['per_page'] ?? 15;
    $sortBy = $validated['sort_by'] ?? 'fechaven';
    $sortOrder = $validated['sort_order'] ?? 'desc';
    
    // ... resto del código
}
```

---

## 📱 EJEMPLOS DE USO FRONTEND

### JavaScript (Fetch API)

```javascript
// Función reutilizable para paginar
async function fetchPaginatedData(url, page = 1, perPage = 20) {
    const response = await fetch(
        `${url}?page=${page}&per_page=${perPage}`,
        {
            headers: {
                'X-API-Key': 'your-api-key',
                'Content-Type': 'application/json'
            }
        }
    );
    
    const result = await response.json();
    
    return {
        data: result.data,
        pagination: result.pagination
    };
}

// Uso
const { data, pagination } = await fetchPaginatedData('/api/v1/ventas', 2, 20);

console.log(`Mostrando ${pagination.from}-${pagination.to} de ${pagination.total}`);
console.log(`Página ${pagination.current_page} de ${pagination.last_page}`);
```

### React Hook para paginación

```jsx
import { useState, useEffect } from 'react';

function useApiPagination(endpoint, perPage = 15) {
    const [data, setData] = useState([]);
    const [pagination, setPagination] = useState({});
    const [loading, setLoading] = useState(false);
    const [page, setPage] = useState(1);
    
    useEffect(() => {
        const fetchData = async () => {
            setLoading(true);
            try {
                const response = await fetch(
                    `${endpoint}?page=${page}&per_page=${perPage}`,
                    {
                        headers: {
                            'X-API-Key': localStorage.getItem('apiKey')
                        }
                    }
                );
                const result = await response.json();
                
                setData(result.data);
                setPagination(result.pagination);
            } catch (error) {
                console.error('Error:', error);
            } finally {
                setLoading(false);
            }
        };
        
        fetchData();
    }, [endpoint, page, perPage]);
    
    return {
        data,
        pagination,
        loading,
        setPage,
        nextPage: () => setPage(p => p + 1),
        prevPage: () => setPage(p => Math.max(1, p - 1))
    };
}

// Componente
function VentasList() {
    const { data, pagination, loading, nextPage, prevPage } = 
        useApiPagination('/api/v1/ventas', 20);
    
    if (loading) return <div>Cargando...</div>;
    
    return (
        <div>
            <ul>
                {data.map(venta => (
                    <li key={venta.idven}>{venta.fechaven}</li>
                ))}
            </ul>
            
            <div>
                <button 
                    onClick={prevPage} 
                    disabled={pagination.current_page === 1}
                >
                    Anterior
                </button>
                
                <span>
                    Página {pagination.current_page} de {pagination.last_page}
                </span>
                
                <button 
                    onClick={nextPage}
                    disabled={pagination.current_page === pagination.last_page}
                >
                    Siguiente
                </button>
            </div>
            
            <p>
                Mostrando {pagination.from}-{pagination.to} de {pagination.total} registros
            </p>
        </div>
    );
}
```

### Vue.js Composable

```javascript
// composables/usePagination.js
import { ref, computed } from 'vue';

export function usePagination(endpoint, perPage = 15) {
    const data = ref([]);
    const pagination = ref({});
    const loading = ref(false);
    const currentPage = ref(1);
    
    const fetchData = async () => {
        loading.value = true;
        try {
            const response = await fetch(
                `${endpoint}?page=${currentPage.value}&per_page=${perPage}`,
                {
                    headers: {
                        'X-API-Key': localStorage.getItem('apiKey')
                    }
                }
            );
            const result = await response.json();
            
            data.value = result.data;
            pagination.value = result.pagination;
        } catch (error) {
            console.error('Error:', error);
        } finally {
            loading.value = false;
        }
    };
    
    const hasNextPage = computed(() => 
        pagination.value.current_page < pagination.value.last_page
    );
    
    const hasPrevPage = computed(() => 
        pagination.value.current_page > 1
    );
    
    const nextPage = () => {
        if (hasNextPage.value) {
            currentPage.value++;
            fetchData();
        }
    };
    
    const prevPage = () => {
        if (hasPrevPage.value) {
            currentPage.value--;
            fetchData();
        }
    };
    
    const goToPage = (page) => {
        currentPage.value = page;
        fetchData();
    };
    
    return {
        data,
        pagination,
        loading,
        currentPage,
        hasNextPage,
        hasPrevPage,
        nextPage,
        prevPage,
        goToPage,
        fetchData
    };
}
```

---

## 🚀 OPTIMIZACIONES DE PERFORMANCE

### 1. **Eager Loading para evitar N+1**

```php
// ❌ MAL - Problema N+1
$ventas = Venta::paginate(15);
// Genera 1 query + 15 queries para cliente + 15 para empleado = 31 queries

// ✅ BIEN - Con eager loading
$ventas = Venta::with(['cliente', 'empleado'])->paginate(15);
// Genera solo 3 queries (ventas, clientes, empleados)
```

### 2. **Select solo campos necesarios**

```php
// ❌ MAL - Carga todos los campos
$ventas = Venta::with('cliente')->paginate(15);

// ✅ BIEN - Solo campos necesarios
$ventas = Venta::select('idven', 'idcli', 'idemp', 'fechaven')
    ->with(['cliente:idcli,nombrecli,telefonocli'])
    ->paginate(15);
```

### 3. **Cachear total de registros**

```php
use Illuminate\Support\Facades\Cache;

public function index(Request $request)
{
    $cacheKey = 'ventas_total';
    
    // Cachear el total por 5 minutos
    $total = Cache::remember($cacheKey, 300, function () {
        return Venta::count();
    });
    
    // Usar simple paginate si no necesitas el total
    $ventas = Venta::simplePaginate($request->input('per_page', 15));
    
    // ...
}
```

### 4. **Índices en base de datos**

```sql
-- Agregar índices para campos que se usan en WHERE y ORDER BY
CREATE INDEX idx_ventas_fechaven ON ventas(fechaven);
CREATE INDEX idx_ventas_idcli ON ventas(idcli);
CREATE INDEX idx_ventas_idemp ON ventas(idemp);

-- Índice compuesto para filtros comunes
CREATE INDEX idx_ventas_cliente_fecha ON ventas(idcli, fechaven);
```

---

## 📊 COMPARATIVA DE PERFORMANCE

### Dataset: 100,000 registros

| Método | Primera Página | Página 1000 | Página 5000 |
|--------|----------------|-------------|-------------|
| `paginate()` | ~50ms | ~200ms | ~800ms |
| `cursorPaginate()` | ~50ms | ~50ms | ~50ms |
| `simplePaginate()` | ~30ms | ~180ms | ~780ms |

**Conclusión**: Usa `cursorPaginate()` para datasets grandes con scroll infinito.

---

## 🎓 RECOMENDACIONES FINALES

### ✅ LO QUE DEBES HACER:

1. **Usar tu implementación actual** para la mayoría de casos (está bien)
2. **Limitar per_page** entre 1 y 100
3. **Agregar links de navegación** (first, last, prev, next)
4. **Validar parámetros** de entrada
5. **Documentar en Swagger/Postman** los parámetros de paginación
6. **Usar cursor** para listas grandes (>50k registros)
7. **Eager loading** siempre que cargues relaciones
8. **Índices** en campos de filtro y ordenamiento

### ❌ LO QUE DEBES EVITAR:

1. No permitir `per_page` ilimitado
2. No usar `all()` en APIs públicas
3. No hacer queries adicionales innecesarias
4. No olvidar paginar relaciones grandes
5. No usar OFFSET con millones de registros

---

## 📚 RECURSOS ADICIONALES

- [Laravel Pagination Docs](https://laravel.com/docs/11.x/pagination)
- [JSON:API Specification](https://jsonapi.org/format/#fetching-pagination)
- [API Resource Pattern](https://laravel.com/docs/11.x/eloquent-resources)

---

**Última actualización**: Diciembre 4, 2025  
**Tu implementación actual**: ✅ **Correcta y bien estructurada**  
**Siguiente nivel**: Implementar API Resources y Cursor Pagination
