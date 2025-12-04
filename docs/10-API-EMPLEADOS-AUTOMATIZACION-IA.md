# 10. API de Empleados - Automatización y Asistente IA

## Índice
1. [Introducción y Casos de Uso](#1-introducción-y-casos-de-uso)
2. [Arquitectura del Sistema](#2-arquitectura-del-sistema)
3. [Endpoints de Empleados](#3-endpoints-de-empleados)
4. [Endpoints de Tareas Automáticas](#4-endpoints-de-tareas-automáticas)
5. [Endpoints de Asistencia (Historial)](#5-endpoints-de-asistencia-historial)
6. [Endpoints de Ventas y Estadísticas](#6-endpoints-de-ventas-y-estadísticas)
7. [Bot de Automatización](#7-bot-de-automatización)
8. [Asistente IA para Atención al Cliente](#8-asistente-ia-para-atención-al-cliente)
9. [Webhooks y Notificaciones](#9-webhooks-y-notificaciones)
10. [Ejemplos de Integración](#10-ejemplos-de-integración)

---

## 1. Introducción y Casos de Uso

### ¿Qué vamos a construir?

Este documento describe la implementación de APIs REST para:

1. **Bot de Automatización**: Sistema que ejecuta tareas programadas automáticamente
   - Generar reportes diarios de ventas
   - Enviar recordatorios de tareas pendientes
   - Actualizar estados de pedidos
   - Sincronizar inventarios
   - Procesar recargas automáticas

2. **Asistente IA de Atención al Cliente**: Chatbot inteligente que atiende consultas
   - Consultar saldo de clientes
   - Revisar historial de compras
   - Procesar nuevas ventas
   - Responder preguntas frecuentes
   - Escalar a empleados humanos cuando sea necesario

### Ventajas de usar APIs

- ✅ **Desacoplamiento**: El bot/IA funciona independiente del sistema web
- ✅ **Escalabilidad**: Múltiples bots pueden trabajar en paralelo
- ✅ **Integración**: Puedes usar Python, Node.js, o cualquier lenguaje para el bot
- ✅ **Seguridad**: Autenticación con API Keys dedicadas
- ✅ **Monitoreo**: Logs centralizados de todas las operaciones automáticas

---

## 2. Arquitectura del Sistema

```
┌─────────────────────────────────────────────────────────────┐
│                    SISTEMA STREAMIFY v6.0                   │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────┐         ┌──────────────┐                 │
│  │   Bot de     │◄────────┤  Laravel API │                 │
│  │ Automatización│         │   REST       │                 │
│  │  (Python/JS) │────────►│              │                 │
│  └──────────────┘         │ - Empleados  │                 │
│                           │ - Tareas     │                 │
│  ┌──────────────┐         │ - Ventas     │                 │
│  │  Asistente   │◄────────┤ - Clientes   │                 │
│  │  IA Chatbot  │────────►│ - Estadísticas│                │
│  │ (OpenAI/etc) │         └──────────────┘                 │
│  └──────────────┘                │                          │
│                                   │                          │
│                           ┌───────▼───────┐                 │
│                           │   MySQL DB    │                 │
│                           │  - empleados  │                 │
│                           │  - tareas     │                 │
│                           │  - ventas     │                 │
│                           │  - clientes   │                 │
│                           └───────────────┘                 │
└─────────────────────────────────────────────────────────────┘
```

### Flujo de Autenticación

```
Bot/IA → API Key → Middleware → Empleado Auth → Endpoint → Respuesta
```

---

## 3. Endpoints de Empleados

### 3.1 Crear el Controlador

```bash
php artisan make:controller Api/V1/EmpleadoApiController
```

### 3.2 Implementación del Controlador

**Archivo**: `app/Http/Controllers/Api/V1/EmpleadoApiController.php`

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Models\Asistencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class EmpleadoApiController extends Controller
{
    /**
     * Listar empleados activos
     * GET /api/v1/empleados
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');
            $rol = $request->input('rol'); // Filtrar por rol

            $query = Empleado::with('roles');

            // Búsqueda por nombre o usuario
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombreemp', 'like', "%{$search}%")
                      ->orWhere('usuarioemp', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Filtrar por rol
            if ($rol) {
                $query->whereHas('roles', function ($q) use ($rol) {
                    $q->where('name', $rol);
                });
            }

            $empleados = $query->orderBy('idemp', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $empleados->items(),
                'pagination' => [
                    'total' => $empleados->total(),
                    'per_page' => $empleados->perPage(),
                    'current_page' => $empleados->currentPage(),
                    'last_page' => $empleados->lastPage(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener empleados',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener empleado por ID
     * GET /api/v1/empleados/{id}
     */
    public function show(string $id)
    {
        try {
            $empleado = Empleado::with(['roles', 'asistencias' => function ($query) {
                $query->orderBy('fecha', 'desc')->limit(10);
            }])->find($id);

            if (!$empleado) {
                return response()->json([
                    'success' => false,
                    'error' => 'Empleado no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'empleado' => $empleado,
                    'estadisticas' => [
                        'total_asistencias' => $empleado->asistencias->count(),
                        'ultima_asistencia' => $empleado->asistencias->first()?->fecha,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener empleado',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo empleado
     * POST /api/v1/empleados
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombreemp' => 'required|string|max:50',
                'usuarioemp' => 'required|string|max:20|unique:empleados,usuarioemp',
                'passwordemp' => 'required|string|min:6',
                'telefonoemp' => 'nullable|string|max:15',
                'email' => 'nullable|email|max:50',
                'rol' => 'nullable|string|exists:roles,name', // Nombre del rol
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $empleado = Empleado::create([
                'nombreemp' => $request->nombreemp,
                'usuarioemp' => $request->usuarioemp,
                'passwordemp' => Hash::make($request->passwordemp),
                'telefonoemp' => $request->telefonoemp,
                'email' => $request->email,
            ]);

            // Asignar rol si se proporcionó
            if ($request->rol) {
                $empleado->assignRole($request->rol);
            }

            return response()->json([
                'success' => true,
                'message' => 'Empleado creado exitosamente',
                'data' => $empleado->load('roles')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al crear empleado',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar empleado
     * PUT /api/v1/empleados/{id}
     */
    public function update(Request $request, string $id)
    {
        try {
            $empleado = Empleado::find($id);

            if (!$empleado) {
                return response()->json([
                    'success' => false,
                    'error' => 'Empleado no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'nombreemp' => 'sometimes|string|max:50',
                'usuarioemp' => 'sometimes|string|max:20|unique:empleados,usuarioemp,' . $id . ',idemp',
                'passwordemp' => 'sometimes|string|min:6',
                'telefonoemp' => 'nullable|string|max:15',
                'email' => 'nullable|email|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->except('passwordemp');
            if ($request->has('passwordemp')) {
                $data['passwordemp'] = Hash::make($request->passwordemp);
            }

            $empleado->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Empleado actualizado exitosamente',
                'data' => $empleado->fresh()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al actualizar empleado',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener empleado autenticado (desde API Key)
     * GET /api/v1/empleados/me
     */
    public function me(Request $request)
    {
        try {
            $empleado = auth()->guard('empleado')->user();

            if (!$empleado) {
                return response()->json([
                    'success' => false,
                    'error' => 'No autenticado'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'data' => $empleado->load('roles')
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener empleado',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
```

---

## 4. Endpoints de Tareas Automáticas

### 4.1 Crear Controlador de Tareas

```bash
php artisan make:controller Api/V1/TareaApiController
```

### 4.2 Implementación

**Archivo**: `app/Http/Controllers/Api/V1/TareaApiController.php`

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tarea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TareaApiController extends Controller
{
    /**
     * Listar tareas (con filtros)
     * GET /api/v1/tareas
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $estado = $request->input('estado'); // pendiente, en_proceso, completada
            $empleadoId = $request->input('empleado_id');
            $fecha = $request->input('fecha'); // YYYY-MM-DD

            $query = Tarea::with(['empleado']);

            // Filtrar por estado
            if ($estado) {
                $query->where('estado', $estado);
            }

            // Filtrar por empleado
            if ($empleadoId) {
                $query->where('idemp', $empleadoId);
            }

            // Filtrar por fecha
            if ($fecha) {
                $query->whereDate('fecha', $fecha);
            }

            $tareas = $query->orderBy('fecha', 'desc')
                           ->orderBy('hora', 'desc')
                           ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $tareas->items(),
                'pagination' => [
                    'total' => $tareas->total(),
                    'current_page' => $tareas->currentPage(),
                    'last_page' => $tareas->lastPage(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener tareas',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nueva tarea
     * POST /api/v1/tareas
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'idemp' => 'required|exists:empleados,idemp',
                'titulo' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
                'fecha' => 'required|date',
                'hora' => 'nullable|string',
                'prioridad' => 'nullable|in:baja,media,alta,urgente',
                'estado' => 'nullable|in:pendiente,en_proceso,completada,cancelada',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $tarea = Tarea::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Tarea creada exitosamente',
                'data' => $tarea->load('empleado')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al crear tarea',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar estado de tarea
     * PATCH /api/v1/tareas/{id}/estado
     */
    public function updateEstado(Request $request, string $id)
    {
        try {
            $tarea = Tarea::find($id);

            if (!$tarea) {
                return response()->json([
                    'success' => false,
                    'error' => 'Tarea no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'estado' => 'required|in:pendiente,en_proceso,completada,cancelada',
                'nota' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $tarea->update([
                'estado' => $request->estado,
                'fecha_completada' => $request->estado === 'completada' ? now() : null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado exitosamente',
                'data' => $tarea->fresh()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al actualizar estado',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener tareas pendientes del día
     * GET /api/v1/tareas/pendientes-hoy
     */
    public function pendientesHoy()
    {
        try {
            $tareas = Tarea::with('empleado')
                ->whereDate('fecha', today())
                ->whereIn('estado', ['pendiente', 'en_proceso'])
                ->orderBy('hora', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $tareas,
                'total' => $tareas->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener tareas',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
```

---

## 5. Endpoints de Asistencia (Historial)

### 5.1 Crear Controlador

```bash
php artisan make:controller Api/V1/AsistenciaApiController
```

### 5.2 Implementación

**Archivo**: `app/Http/Controllers/Api/V1/AsistenciaApiController.php`

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AsistenciaApiController extends Controller
{
    /**
     * Registrar entrada/salida
     * POST /api/v1/asistencias
     */
    public function store(Request $request)
    {
        try {
            $empleado = auth()->guard('empleado')->user();

            if (!$empleado) {
                return response()->json([
                    'success' => false,
                    'error' => 'No autenticado'
                ], 401);
            }

            // Buscar asistencia del día
            $asistenciaHoy = Asistencia::where('idemp', $empleado->idemp)
                ->whereDate('fecha', today())
                ->first();

            if (!$asistenciaHoy) {
                // Registrar entrada
                $asistencia = Asistencia::create([
                    'idemp' => $empleado->idemp,
                    'fecha' => today(),
                    'hora_entrada' => now()->format('H:i:s'),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Entrada registrada',
                    'tipo' => 'entrada',
                    'data' => $asistencia
                ], 201);
            }

            if (!$asistenciaHoy->hora_salida) {
                // Registrar salida
                $asistenciaHoy->update([
                    'hora_salida' => now()->format('H:i:s'),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Salida registrada',
                    'tipo' => 'salida',
                    'data' => $asistenciaHoy->fresh()
                ], 200);
            }

            return response()->json([
                'success' => false,
                'error' => 'Ya registraste entrada y salida hoy'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al registrar asistencia',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener asistencias por rango de fechas
     * GET /api/v1/asistencias
     */
    public function index(Request $request)
    {
        try {
            $empleadoId = $request->input('empleado_id');
            $fechaInicio = $request->input('fecha_inicio', today()->subDays(30));
            $fechaFin = $request->input('fecha_fin', today());

            $query = Asistencia::with('empleado')
                ->whereBetween('fecha', [$fechaInicio, $fechaFin]);

            if ($empleadoId) {
                $query->where('idemp', $empleadoId);
            }

            $asistencias = $query->orderBy('fecha', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $asistencias,
                'total' => $asistencias->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener asistencias',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
```

---

## 6. Endpoints de Ventas y Estadísticas

### 6.1 Crear Controlador de Ventas

```bash
php artisan make:controller Api/V1/VentaApiController
```

### 6.2 Implementación

**Archivo**: `app/Http/Controllers/Api/V1/VentaApiController.php`

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class VentaApiController extends Controller
{
    /**
     * Listar ventas
     * GET /api/v1/ventas
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 20);
            $clienteId = $request->input('cliente_id');
            $fechaInicio = $request->input('fecha_inicio');
            $fechaFin = $request->input('fecha_fin');

            $query = Venta::with(['cliente', 'producto']);

            if ($clienteId) {
                $query->where('idcli', $clienteId);
            }

            if ($fechaInicio && $fechaFin) {
                $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
            }

            $ventas = $query->orderBy('fecha', 'desc')
                           ->orderBy('hora', 'desc')
                           ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $ventas->items(),
                'pagination' => [
                    'total' => $ventas->total(),
                    'current_page' => $ventas->currentPage(),
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

    /**
     * Crear nueva venta
     * POST /api/v1/ventas
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'idcli' => 'required|exists:clientes,idcli',
                'idprod' => 'required|exists:productos,idprod',
                'cantidad' => 'required|integer|min:1',
                'precio_unitario' => 'required|numeric|min:0',
                'metodo_pago' => 'nullable|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $total = $request->cantidad * $request->precio_unitario;

            $venta = Venta::create([
                'idcli' => $request->idcli,
                'idprod' => $request->idprod,
                'cantidad' => $request->cantidad,
                'precio_unitario' => $request->precio_unitario,
                'total' => $total,
                'fecha' => now()->format('Y-m-d'),
                'hora' => now()->format('H:i:s'),
                'metodo_pago' => $request->metodo_pago ?? 'efectivo',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Venta registrada exitosamente',
                'data' => $venta->load(['cliente', 'producto'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al crear venta',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas de ventas
     * GET /api/v1/ventas/estadisticas
     */
    public function estadisticas(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', today()->subDays(30));
            $fechaFin = $request->input('fecha_fin', today());

            $totalVentas = Venta::whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->count();

            $ingresoTotal = Venta::whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->sum('total');

            $ventasHoy = Venta::whereDate('fecha', today())->count();
            $ingresosHoy = Venta::whereDate('fecha', today())->sum('total');

            $topProductos = Venta::select('idprod', DB::raw('COUNT(*) as total_vendido'))
                ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->groupBy('idprod')
                ->orderBy('total_vendido', 'desc')
                ->limit(5)
                ->with('producto')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'periodo' => [
                        'fecha_inicio' => $fechaInicio,
                        'fecha_fin' => $fechaFin,
                    ],
                    'totales' => [
                        'ventas' => $totalVentas,
                        'ingresos' => $ingresoTotal,
                    ],
                    'hoy' => [
                        'ventas' => $ventasHoy,
                        'ingresos' => $ingresosHoy,
                    ],
                    'top_productos' => $topProductos,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener estadísticas',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
```

---

## 7. Bot de Automatización

### 7.1 Configurar Rutas API

**Archivo**: `routes/api.php`

```php
use App\Http\Controllers\Api\V1\EmpleadoApiController;
use App\Http\Controllers\Api\V1\TareaApiController;
use App\Http\Controllers\Api\V1\AsistenciaApiController;
use App\Http\Controllers\Api\V1\VentaApiController;

// Rutas públicas
Route::get('/ping', function () {
    return response()->json([
        'success' => true,
        'message' => 'Streamify API v1.0',
        'status' => 'active',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Rutas protegidas con API Key
Route::prefix('v1')->middleware('api.key')->group(function () {
    
    // Empleados
    Route::get('/empleados/me', [EmpleadoApiController::class, 'me']);
    Route::apiResource('empleados', EmpleadoApiController::class);
    
    // Tareas
    Route::get('/tareas/pendientes-hoy', [TareaApiController::class, 'pendientesHoy']);
    Route::patch('/tareas/{id}/estado', [TareaApiController::class, 'updateEstado']);
    Route::apiResource('tareas', TareaApiController::class);
    
    // Asistencias
    Route::apiResource('asistencias', AsistenciaApiController::class);
    
    // Ventas
    Route::get('/ventas/estadisticas', [VentaApiController::class, 'estadisticas']);
    Route::apiResource('ventas', VentaApiController::class);
    
    // Clientes (ya creado anteriormente)
    Route::apiResource('clientes', ClienteApiController::class);
    Route::get('clientes/{id}/ventas', [ClienteApiController::class, 'ventas']);
});
```

### 7.2 Ejemplo de Bot en Python

**Archivo**: `bot_automatizacion.py`

```python
import requests
import schedule
import time
from datetime import datetime

class StreamifyBot:
    def __init__(self, api_key, base_url='http://localhost/api/v1'):
        self.api_key = api_key
        self.base_url = base_url
        self.headers = {
            'X-API-Key': api_key,
            'Content-Type': 'application/json'
        }
    
    def ping(self):
        """Verificar que la API está activa"""
        response = requests.get(f'{self.base_url.replace("/v1", "")}/ping')
        return response.json()
    
    def obtener_tareas_pendientes(self):
        """Obtener tareas pendientes del día"""
        response = requests.get(
            f'{self.base_url}/tareas/pendientes-hoy',
            headers=self.headers
        )
        return response.json()
    
    def completar_tarea(self, tarea_id):
        """Marcar tarea como completada"""
        response = requests.patch(
            f'{self.base_url}/tareas/{tarea_id}/estado',
            headers=self.headers,
            json={'estado': 'completada'}
        )
        return response.json()
    
    def obtener_estadisticas_diarias(self):
        """Obtener estadísticas de ventas del día"""
        response = requests.get(
            f'{self.base_url}/ventas/estadisticas',
            headers=self.headers
        )
        return response.json()
    
    def enviar_recordatorio_tareas(self):
        """Enviar recordatorios de tareas pendientes"""
        print(f"[{datetime.now()}] Verificando tareas pendientes...")
        
        tareas = self.obtener_tareas_pendientes()
        
        if tareas['success'] and tareas['total'] > 0:
            print(f"✅ {tareas['total']} tareas pendientes encontradas")
            for tarea in tareas['data']:
                print(f"   - {tarea['titulo']} (Empleado: {tarea['empleado']['nombreemp']})")
        else:
            print("✅ No hay tareas pendientes")
    
    def generar_reporte_diario(self):
        """Generar reporte de ventas del día"""
        print(f"\n[{datetime.now()}] Generando reporte diario...")
        
        stats = self.obtener_estadisticas_diarias()
        
        if stats['success']:
            hoy = stats['data']['hoy']
            print(f"📊 Ventas hoy: {hoy['ventas']}")
            print(f"💰 Ingresos hoy: ${hoy['ingresos']}")
        else:
            print(f"❌ Error: {stats.get('error')}")

# Uso del bot
if __name__ == '__main__':
    # API Key del empleado Laravel (Bot)
    API_KEY = 'sk_X23MK7KzyvLe7XdbYSlYjSIPTd3eQFlSvW5SmPBrsk5WKpNrfdRUexhEtN2X'
    
    bot = StreamifyBot(API_KEY)
    
    # Verificar conexión
    print("🤖 Iniciando Bot de Automatización Streamify")
    ping = bot.ping()
    print(f"✅ API Status: {ping['status']}")
    
    # Programar tareas
    schedule.every().day.at("09:00").do(bot.generar_reporte_diario)
    schedule.every().day.at("14:00").do(bot.enviar_recordatorio_tareas)
    schedule.every().day.at("18:00").do(bot.generar_reporte_diario)
    
    print("\n📅 Tareas programadas:")
    print("   - Reporte diario: 09:00, 18:00")
    print("   - Recordatorio tareas: 14:00")
    print("\n⏳ Bot ejecutándose... (Ctrl+C para detener)\n")
    
    # Bucle principal
    while True:
        schedule.run_pending()
        time.sleep(60)  # Verificar cada minuto
```

### 7.3 Instalación del Bot

```bash
# Instalar dependencias
pip install requests schedule

# Ejecutar bot
python bot_automatizacion.py
```

---

## 8. Asistente IA para Atención al Cliente

### 8.1 Ejemplo con OpenAI

**Archivo**: `asistente_ia.py`

```python
import requests
import openai
import json

class AsistenteStreamify:
    def __init__(self, api_key, openai_key):
        self.api_key = api_key
        self.base_url = 'http://localhost/api/v1'
        self.headers = {
            'X-API-Key': api_key,
            'Content-Type': 'application/json'
        }
        openai.api_key = openai_key
    
    def consultar_saldo_cliente(self, telefono):
        """Consultar saldo de cliente por teléfono"""
        response = requests.get(
            f'{self.base_url}/clientes',
            headers=self.headers,
            params={'search': telefono}
        )
        data = response.json()
        
        if data['success'] and len(data['data']) > 0:
            cliente = data['data'][0]
            return {
                'exito': True,
                'cliente': cliente['nombrecli'],
                'saldo': cliente['saldo'],
                'telefono': cliente['telefonocli']
            }
        return {'exito': False, 'mensaje': 'Cliente no encontrado'}
    
    def historial_compras(self, cliente_id):
        """Obtener historial de compras de un cliente"""
        response = requests.get(
            f'{self.base_url}/clientes/{cliente_id}/ventas',
            headers=self.headers
        )
        return response.json()
    
    def procesar_venta(self, cliente_id, producto_id, cantidad):
        """Registrar nueva venta"""
        # Primero obtener el producto para saber el precio
        producto_response = requests.get(
            f'{self.base_url}/productos/{producto_id}',
            headers=self.headers
        )
        
        if not producto_response.json()['success']:
            return {'exito': False, 'mensaje': 'Producto no encontrado'}
        
        producto = producto_response.json()['data']
        
        venta_response = requests.post(
            f'{self.base_url}/ventas',
            headers=self.headers,
            json={
                'idcli': cliente_id,
                'idprod': producto_id,
                'cantidad': cantidad,
                'precio_unitario': producto['precio']
            }
        )
        
        return venta_response.json()
    
    def chatbot(self, mensaje_usuario):
        """Procesar mensaje del cliente con IA"""
        
        # Contexto del sistema
        system_prompt = """
        Eres un asistente virtual de Streamify, una plataforma de servicios de streaming.
        
        Puedes ayudar con:
        - Consultar saldo de clientes
        - Ver historial de compras
        - Registrar nuevas ventas
        - Responder preguntas frecuentes
        
        Si necesitas realizar una acción, responde en formato JSON:
        {
            "accion": "consultar_saldo|historial_compras|registrar_venta",
            "parametros": {...}
        }
        
        Si es una pregunta general, responde directamente de forma amigable.
        """
        
        response = openai.ChatCompletion.create(
            model="gpt-4",
            messages=[
                {"role": "system", "content": system_prompt},
                {"role": "user", "content": mensaje_usuario}
            ]
        )
        
        respuesta_ia = response.choices[0].message.content
        
        # Intentar parsear como JSON
        try:
            accion = json.loads(respuesta_ia)
            
            if accion['accion'] == 'consultar_saldo':
                resultado = self.consultar_saldo_cliente(accion['parametros']['telefono'])
                if resultado['exito']:
                    return f"Cliente: {resultado['cliente']}\nSaldo: ${resultado['saldo']}"
                else:
                    return resultado['mensaje']
            
            elif accion['accion'] == 'historial_compras':
                resultado = self.historial_compras(accion['parametros']['cliente_id'])
                if resultado['success']:
                    total = resultado['data']['total_ventas']
                    return f"Total de compras: {total}"
                else:
                    return "No se encontraron compras"
            
        except json.JSONDecodeError:
            # Es una respuesta normal, no una acción
            return respuesta_ia

# Uso
if __name__ == '__main__':
    API_KEY = 'sk_X23MK7KzyvLe7XdbYSlYjSIPTd3eQFlSvW5SmPBrsk5WKpNrfdRUexhEtN2X'
    OPENAI_KEY = 'sk-...'  # Tu API Key de OpenAI
    
    asistente = AsistenteStreamify(API_KEY, OPENAI_KEY)
    
    print("🤖 Asistente IA de Streamify")
    print("=" * 50)
    
    while True:
        mensaje = input("\nCliente: ")
        if mensaje.lower() in ['salir', 'exit', 'quit']:
            break
        
        respuesta = asistente.chatbot(mensaje)
        print(f"\n🤖 Asistente: {respuesta}")
```

### 8.2 Ejemplo Simple sin IA (Reglas)

**Archivo**: `asistente_simple.py`

```python
import requests
import re

class AsistenteSimple:
    def __init__(self, api_key):
        self.api_key = api_key
        self.base_url = 'http://localhost/api/v1'
        self.headers = {
            'X-API-Key': api_key,
            'Content-Type': 'application/json'
        }
    
    def procesar_mensaje(self, mensaje):
        """Procesar mensaje con reglas simples"""
        mensaje = mensaje.lower()
        
        # Detectar intención: consultar saldo
        if 'saldo' in mensaje or 'cuanto tengo' in mensaje:
            # Extraer teléfono del mensaje
            telefono = re.search(r'\d{10}', mensaje)
            if telefono:
                return self.consultar_saldo(telefono.group())
            else:
                return "Por favor proporciona tu número de teléfono (10 dígitos)"
        
        # Detectar intención: historial
        if 'historial' in mensaje or 'compras' in mensaje:
            return "Para ver tu historial, necesito tu ID de cliente. ¿Cuál es?"
        
        # Detectar intención: ayuda
        if 'ayuda' in mensaje or 'hola' in mensaje:
            return """
            ¡Hola! Soy el asistente de Streamify. Puedo ayudarte con:
            
            • Consultar tu saldo: "¿Cuál es mi saldo? Mi teléfono es 1234567890"
            • Ver tu historial de compras
            • Resolver dudas sobre servicios
            
            ¿En qué puedo ayudarte?
            """
        
        return "No entendí tu mensaje. Escribe 'ayuda' para ver qué puedo hacer."
    
    def consultar_saldo(self, telefono):
        """Consultar saldo por teléfono"""
        response = requests.get(
            f'{self.base_url}/clientes',
            headers=self.headers,
            params={'search': telefono}
        )
        
        data = response.json()
        
        if data['success'] and len(data['data']) > 0:
            cliente = data['data'][0]
            return f"✅ {cliente['nombrecli']}, tu saldo actual es: ${cliente['saldo']}"
        
        return "❌ No encontré un cliente con ese teléfono"

# Uso
if __name__ == '__main__':
    API_KEY = 'sk_X23MK7KzyvLe7XdbYSlYjSIPTd3eQFlSvW5SmPBrsk5WKpNrfdRUexhEtN2X'
    
    asistente = AsistenteSimple(API_KEY)
    
    print("🤖 Asistente Simple de Streamify")
    print("=" * 50)
    
    while True:
        mensaje = input("\nTú: ")
        if mensaje.lower() in ['salir', 'exit']:
            break
        
        respuesta = asistente.procesar_mensaje(mensaje)
        print(f"\n🤖 Asistente:\n{respuesta}")
```

---

## 9. Webhooks y Notificaciones

### 9.1 Endpoint para Webhooks

```php
// routes/api.php

Route::post('/v1/webhooks/venta-creada', function (Request $request) {
    // Verificar API Key
    $apiKey = $request->header('X-API-Key');
    
    // Enviar notificación al bot/IA
    // Puedes usar eventos de Laravel
    event(new \App\Events\VentaCreada($request->all()));
    
    return response()->json(['success' => true]);
})->middleware('api.key');
```

### 9.2 Escuchar Webhooks desde el Bot

```python
from flask import Flask, request
import requests

app = Flask(__name__)

@app.route('/webhook/venta', methods=['POST'])
def webhook_venta():
    data = request.json
    print(f"🔔 Nueva venta recibida: {data}")
    
    # Procesar la venta
    # Enviar notificación, actualizar dashboard, etc.
    
    return {'status': 'recibido'}, 200

if __name__ == '__main__':
    app.run(port=5000)
```

---

## 10. Ejemplos de Integración

### 10.1 Script de Node.js

```javascript
const axios = require('axios');

class StreamifyAPI {
    constructor(apiKey) {
        this.apiKey = apiKey;
        this.baseURL = 'http://localhost/api/v1';
        this.headers = {
            'X-API-Key': apiKey,
            'Content-Type': 'application/json'
        };
    }
    
    async obtenerEstadisticas() {
        const response = await axios.get(`${this.baseURL}/ventas/estadisticas`, {
            headers: this.headers
        });
        return response.data;
    }
    
    async crearTarea(empleadoId, titulo, fecha) {
        const response = await axios.post(`${this.baseURL}/tareas`, {
            idemp: empleadoId,
            titulo: titulo,
            fecha: fecha,
            estado: 'pendiente'
        }, {
            headers: this.headers
        });
        return response.data;
    }
}

// Uso
const api = new StreamifyAPI('sk_X23MK7KzyvLe7XdbYSlYjSIPTd3eQFlSvW5SmPBrsk5WKpNrfdRUexhEtN2X');

api.obtenerEstadisticas().then(stats => {
    console.log('📊 Estadísticas:', stats);
});
```

### 10.2 Ejemplo con cURL

```bash
# Obtener empleado autenticado
curl -X GET http://localhost/api/v1/empleados/me \
  -H "X-API-Key: sk_X23MK7KzyvLe7XdbYSlYjSIPTd3eQFlSvW5SmPBrsk5WKpNrfdRUexhEtN2X"

# Crear tarea
curl -X POST http://localhost/api/v1/tareas \
  -H "X-API-Key: sk_X23MK7KzyvLe7XdbYSlYjSIPTd3eQFlSvW5SmPBrsk5WKpNrfdRUexhEtN2X" \
  -H "Content-Type: application/json" \
  -d '{
    "idemp": 8,
    "titulo": "Revisar inventario",
    "fecha": "2025-12-04",
    "prioridad": "alta"
  }'

# Obtener tareas pendientes
curl -X GET "http://localhost/api/v1/tareas/pendientes-hoy" \
  -H "X-API-Key: sk_X23MK7KzyvLe7XdbYSlYjSIPTd3eQFlSvW5SmPBrsk5WKpNrfdRUexhEtN2X"
```

---

## Próximos Pasos

1. ✅ **Implementar controladores** (Empleados, Tareas, Asistencias, Ventas)
2. ✅ **Configurar rutas** en `routes/api.php`
3. ⏳ **Crear bot en Python/Node.js**
4. ⏳ **Integrar IA** (OpenAI, Claude, etc.)
5. ⏳ **Configurar webhooks** para notificaciones en tiempo real
6. ⏳ **Agregar autenticación OAuth** para clientes externos
7. ⏳ **Documentar con Swagger/OpenAPI**

---

## Recursos Adicionales

- [Documentación Laravel API](https://laravel.com/docs/10.x/eloquent-resources)
- [OpenAI API Reference](https://platform.openai.com/docs/api-reference)
- [Python Requests Library](https://requests.readthedocs.io/)
- [Schedule (Python cron)](https://schedule.readthedocs.io/)
- [Axios (Node.js HTTP Client)](https://axios-http.com/)

---

**¡Listo para automatizar Streamify! 🚀**
