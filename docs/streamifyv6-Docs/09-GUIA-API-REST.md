# 🔑 Guía Completa: API REST con Autenticación por API Keys

> **Streamify v6.0 - Sistema de API REST**  
> Implementación de API REST con autenticación mediante API Keys para integración externa  
> Fecha de creación: 3 de Diciembre, 2025

---

## 📋 Tabla de Contenidos

1. [Introducción](#1-introducción)
2. [Instalación de Laravel Sanctum](#2-instalación-de-laravel-sanctum)
3. [Base de Datos: Tabla API Keys](#3-base-de-datos-tabla-api-keys)
4. [Modelo ApiKey](#4-modelo-apikey)
5. [Middleware de Autenticación](#5-middleware-de-autenticación)
6. [Controladores API](#6-controladores-api)
7. [Rutas de API](#7-rutas-de-api)
8. [Panel de Administración](#8-panel-de-administración)
9. [Testing con Postman](#9-testing-con-postman)
10. [Ejemplos de Uso](#10-ejemplos-de-uso)

---

## 1. Introducción

### ¿Qué es una API REST?

Una **API REST** (Representational State Transfer) es una interfaz que permite a aplicaciones externas comunicarse con Streamify mediante peticiones HTTP estándar.

### ¿Por qué usar API Keys?

- ✅ **Seguridad**: Cada aplicación tiene su propia clave única
- ✅ **Trazabilidad**: Registro de quién usa la API
- ✅ **Control**: Activar/desactivar accesos sin afectar el sistema
- ✅ **Permisos**: Diferentes niveles de acceso por key
- ✅ **Sin sesiones**: Stateless, ideal para microservicios

### Casos de Uso en Streamify

- 📱 **App móvil** para clientes
- 🔗 **Integración** con sistemas de facturación
- 🤖 **Automatización** de procesos (scripts, bots)
- 📊 **Dashboard público** de estadísticas
- 🔔 **Webhooks** para notificaciones externas

---

## 2. Instalación de Laravel Sanctum

### Paso 1: Instalar Sanctum

```bash
composer require laravel/sanctum
```

### Paso 2: Publicar configuración

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### Paso 3: Ejecutar migraciones

```bash
php artisan migrate
```

### Paso 4: Configurar Kernel (Opcional para SPA)

Si planeas usar Sanctum para SPA, agrega el middleware en `app/Http/Kernel.php`:

```php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

---

## 3. Base de Datos: Tabla API Keys

### Migración

Crear archivo: `database/migrations/2025_12_03_000000_create_api_keys_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre descriptivo (ej: "App Móvil iOS")
            $table->string('key', 64)->unique(); // API Key única
            $table->unsignedBigInteger('user_id')->nullable(); // Usuario propietario
            $table->json('permissions')->nullable(); // Permisos específicos
            $table->timestamp('last_used_at')->nullable(); // Última vez usada
            $table->timestamp('expires_at')->nullable(); // Fecha de expiración
            $table->boolean('is_active')->default(true); // Activa/Inactiva
            $table->string('ip_whitelist')->nullable(); // IPs permitidas (opcional)
            $table->integer('requests_count')->default(0); // Contador de peticiones
            $table->timestamps();
            
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            
            // Índices para optimizar búsquedas
            $table->index('key');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
```

### Ejecutar migración

```bash
php artisan migrate
```

---

## 4. Modelo ApiKey

### Crear modelo

```bash
php artisan make:model ApiKey
```

### Implementación completa

Archivo: `app/Models/ApiKey.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ApiKey extends Model
{
    protected $fillable = [
        'name',
        'key',
        'user_id',
        'permissions',
        'last_used_at',
        'expires_at',
        'is_active',
        'ip_whitelist',
        'requests_count',
    ];

    protected $casts = [
        'permissions' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'key', // Ocultar en respuestas JSON
    ];

    /**
     * Relación con usuario propietario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generar nueva API Key
     */
    public static function generate(string $name, ?int $userId = null, array $permissions = [], ?Carbon $expiresAt = null)
    {
        return self::create([
            'name' => $name,
            'key' => 'sk_' . Str::random(60), // Prefijo "sk_" + 60 caracteres aleatorios
            'user_id' => $userId,
            'permissions' => $permissions,
            'expires_at' => $expiresAt,
            'is_active' => true,
        ]);
    }

    /**
     * Verificar si la API Key está vigente
     */
    public function isValid(): bool
    {
        // Verificar si está activa
        if (!$this->is_active) {
            return false;
        }

        // Verificar si ha expirado
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Verificar si la IP está permitida
     */
    public function isIpAllowed(string $ip): bool
    {
        if (!$this->ip_whitelist) {
            return true; // Sin restricción de IP
        }

        $allowedIps = explode(',', $this->ip_whitelist);
        return in_array($ip, array_map('trim', $allowedIps));
    }

    /**
     * Verificar si tiene un permiso específico
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->permissions) {
            return true; // Sin restricción de permisos
        }

        return in_array($permission, $this->permissions);
    }

    /**
     * Marcar como usada (actualizar timestamp y contador)
     */
    public function markAsUsed(): void
    {
        $this->increment('requests_count');
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Obtener key oculta (solo primeros 10 caracteres)
     */
    public function getMaskedKeyAttribute(): string
    {
        return substr($this->key, 0, 10) . '...' . substr($this->key, -4);
    }

    /**
     * Scope: Solo API Keys activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: API Keys no expiradas
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }
}
```

---

## 5. Middleware de Autenticación

### Crear middleware

```bash
php artisan make:middleware AuthenticateApiKey
```

### Implementación

Archivo: `app/Http/Middleware/AuthenticateApiKey.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthenticateApiKey
{
    /**
     * Manejar petición entrante
     */
    public function handle(Request $request, Closure $next, ?string $permission = null)
    {
        // Obtener API Key del header o query parameter
        $apiKeyValue = $request->header('X-API-Key') ?? $request->input('api_key');

        // Validar que se proporcionó la API Key
        if (!$apiKeyValue) {
            return response()->json([
                'success' => false,
                'error' => 'API Key no proporcionada',
                'message' => 'Incluye el header "X-API-Key: tu_api_key" o el parámetro ?api_key=tu_api_key'
            ], 401);
        }

        // Buscar la API Key en la base de datos
        $apiKey = ApiKey::where('key', $apiKeyValue)->first();

        if (!$apiKey) {
            Log::warning('API Key no encontrada', ['key' => substr($apiKeyValue, 0, 10) . '...']);
            
            return response()->json([
                'success' => false,
                'error' => 'API Key inválida',
                'message' => 'La API Key proporcionada no existe en el sistema'
            ], 403);
        }

        // Verificar si la API Key está vigente
        if (!$apiKey->isValid()) {
            Log::warning('API Key expirada o inactiva', ['key_id' => $apiKey->id]);
            
            return response()->json([
                'success' => false,
                'error' => 'API Key no válida',
                'message' => 'La API Key está desactivada o ha expirado'
            ], 403);
        }

        // Verificar restricción de IP (si existe)
        if (!$apiKey->isIpAllowed($request->ip())) {
            Log::warning('IP no permitida para API Key', [
                'key_id' => $apiKey->id,
                'ip' => $request->ip()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'IP no autorizada',
                'message' => 'Tu dirección IP no está autorizada para usar esta API Key'
            ], 403);
        }

        // Verificar permiso específico (si se requiere)
        if ($permission && !$apiKey->hasPermission($permission)) {
            Log::warning('Permiso denegado para API Key', [
                'key_id' => $apiKey->id,
                'permission' => $permission
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Permiso denegado',
                'message' => "Esta API Key no tiene permiso para: {$permission}"
            ], 403);
        }

        // Marcar como usada
        $apiKey->markAsUsed();

        // Adjuntar modelo de API Key al request para uso posterior
        $request->merge(['api_key_model' => $apiKey]);
        
        // Si la API Key tiene usuario asociado, autenticarlo
        if ($apiKey->user_id) {
            auth()->loginUsingId($apiKey->user_id);
        }

        // Registrar petición exitosa
        Log::info('API Request autenticada', [
            'key_id' => $apiKey->id,
            'key_name' => $apiKey->name,
            'method' => $request->method(),
            'url' => $request->path(),
            'ip' => $request->ip(),
        ]);

        return $next($request);
    }
}
```

### Registrar middleware

Archivo: `app/Http/Kernel.php`

```php
protected $middlewareAliases = [
    // ... otros middlewares
    'api.key' => \App\Http\Middleware\AuthenticateApiKey::class,
];
```

---

## 6. Controladores API

### Estructura recomendada

```
app/Http/Controllers/Api/V1/
├── ClienteApiController.php
├── VentaApiController.php
├── CuentaApiController.php
├── ProductoApiController.php
├── ServicioApiController.php
└── DashboardApiController.php
```

### Ejemplo: ClienteApiController

Crear controlador:

```bash
php artisan make:controller Api/V1/ClienteApiController --api
```

Implementación completa:

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClienteApiController extends Controller
{
    /**
     * Listar todos los clientes
     * GET /api/v1/clientes
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');

            $query = Cliente::query();

            // Búsqueda por nombre o teléfono
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('telefono', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $clientes = $query->orderBy('id', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $clientes->items(),
                'pagination' => [
                    'total' => $clientes->total(),
                    'per_page' => $clientes->perPage(),
                    'current_page' => $clientes->currentPage(),
                    'last_page' => $clientes->lastPage(),
                    'from' => $clientes->firstItem(),
                    'to' => $clientes->lastItem(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener clientes',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener un cliente específico
     * GET /api/v1/clientes/{id}
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $cliente = Cliente::with(['ventas.producto', 'pedidos'])->find($id);

            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cliente no encontrado',
                    'message' => "No existe un cliente con ID {$id}"
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $cliente
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener cliente',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo cliente
     * POST /api/v1/clientes
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:50',
                'telefono' => 'required|string|max:15|unique:clientes,telefono',
                'email' => 'nullable|email|max:50',
                'usuario' => 'nullable|string|max:50',
                'password' => 'nullable|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $cliente = Cliente::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Cliente creado exitosamente',
                'data' => $cliente
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al crear cliente',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar cliente existente
     * PUT /api/v1/clientes/{id}
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $cliente = Cliente::find($id);

            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cliente no encontrado',
                    'message' => "No existe un cliente con ID {$id}"
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'nombre' => 'sometimes|string|max:50',
                'telefono' => 'sometimes|string|max:15|unique:clientes,telefono,' . $id,
                'email' => 'nullable|email|max:50',
                'usuario' => 'nullable|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $cliente->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Cliente actualizado exitosamente',
                'data' => $cliente
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al actualizar cliente',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar cliente
     * DELETE /api/v1/clientes/{id}
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $cliente = Cliente::find($id);

            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cliente no encontrado',
                    'message' => "No existe un cliente con ID {$id}"
                ], 404);
            }

            $cliente->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cliente eliminado exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al eliminar cliente',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener ventas de un cliente
     * GET /api/v1/clientes/{id}/ventas
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function ventas($id)
    {
        try {
            $cliente = Cliente::with('ventas.producto')->find($id);

            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cliente no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'cliente' => $cliente->only(['id', 'nombre', 'telefono', 'email']),
                    'total_ventas' => $cliente->ventas->count(),
                    'ventas' => $cliente->ventas
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
}
```

---

## 7. Rutas de API

### Configurar rutas

Archivo: `routes/api.php`

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ClienteApiController;
use App\Http\Controllers\Api\V1\VentaApiController;
use App\Http\Controllers\Api\V1\CuentaApiController;
use App\Http\Controllers\Api\V1\ProductoApiController;

/*
|--------------------------------------------------------------------------
| API Routes - Streamify v6.0
|--------------------------------------------------------------------------
*/

// Ruta de prueba (sin autenticación)
Route::get('/ping', function () {
    return response()->json([
        'success' => true,
        'message' => 'Streamify API v1.0',
        'status' => 'active',
        'timestamp' => now()->toIso8601String(),
        'version' => '1.0.0',
    ]);
});

// Grupo API v1
Route::prefix('v1')->group(function () {
    
    // Rutas protegidas con API Key
    Route::middleware('api.key')->group(function () {
        
        // Clientes
        Route::apiResource('clientes', ClienteApiController::class);
        Route::get('clientes/{id}/ventas', [ClienteApiController::class, 'ventas']);
        
        // Ventas
        Route::apiResource('ventas', VentaApiController::class);
        
        // Cuentas
        Route::apiResource('cuentas', CuentaApiController::class);
        Route::get('cuentas/disponibles', [CuentaApiController::class, 'disponibles']);
        
        // Productos
        Route::apiResource('productos', ProductoApiController::class);
    });
    
    // Rutas con permisos específicos (ejemplo)
    Route::middleware('api.key:admin')->group(function () {
        // Rutas solo para API Keys con permiso "admin"
    });
});
```

### Rate Limiting

Configurar límites de peticiones en `app/Http/Kernel.php`:

```php
protected function configureRateLimiting()
{
    RateLimiter::for('api', function (Request $request) {
        return Limit::perMinute(60)->by($request->header('X-API-Key') ?: $request->ip());
    });
}
```

---

## 8. Panel de Administración

### Controlador de API Keys

Crear controlador:

```bash
php artisan make:controller ApiKeyController
```

Implementación:

```php
<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ApiKeyController extends Controller
{
    public function index()
    {
        $apiKeys = ApiKey::with('user')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('administration.api-keys.index', compact('apiKeys'));
    }

    public function create()
    {
        return view('administration.api-keys.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'expires_at' => 'nullable|date|after:today',
            'permissions' => 'nullable|array',
        ]);

        $expiresAt = $request->expires_at ? Carbon::parse($request->expires_at) : null;

        $apiKey = ApiKey::generate(
            $request->name,
            auth()->id(),
            $request->permissions ?? [],
            $expiresAt
        );

        return redirect()
            ->route('api-keys.index')
            ->with('success', 'API Key creada exitosamente')
            ->with('new_key', $apiKey->key); // Mostrar solo una vez
    }

    public function destroy($id)
    {
        $apiKey = ApiKey::findOrFail($id);
        $apiKey->delete();

        return redirect()
            ->route('api-keys.index')
            ->with('success', 'API Key eliminada');
    }

    public function toggle($id)
    {
        $apiKey = ApiKey::findOrFail($id);
        $apiKey->update(['is_active' => !$apiKey->is_active]);

        $status = $apiKey->is_active ? 'activada' : 'desactivada';

        return redirect()
            ->route('api-keys.index')
            ->with('success', "API Key {$status}");
    }
}
```

### Rutas de administración

Archivo: `routes/web.php`

```php
use App\Http\Controllers\ApiKeyController;

Route::middleware('auth')->prefix('admin')->group(function () {
    Route::get('/api-keys', [ApiKeyController::class, 'index'])->name('api-keys.index');
    Route::get('/api-keys/create', [ApiKeyController::class, 'create'])->name('api-keys.create');
    Route::post('/api-keys', [ApiKeyController::class, 'store'])->name('api-keys.store');
    Route::delete('/api-keys/{id}', [ApiKeyController::class, 'destroy'])->name('api-keys.destroy');
    Route::post('/api-keys/{id}/toggle', [ApiKeyController::class, 'toggle'])->name('api-keys.toggle');
});
```

### Vista Index

Crear archivo: `resources/views/administration/api-keys/index.blade.php`

```blade
@extends('layouts.static')

@section('h1')
<i class="fas fa-key text-primary me-2"></i> Gestión de API Keys
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">API Keys</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>API Keys del Sistema</h2>
        <a href="{{ route('api-keys.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nueva API Key
        </a>
    </div>

    {{-- Alerta de API Key recién creada --}}
    @if(session('new_key'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <h5><i class="fas fa-check-circle"></i> API Key creada exitosamente</h5>
            <p class="mb-2">
                <strong>⚠️ IMPORTANTE:</strong> Guarda esta key de forma segura. No podrás verla nuevamente.
            </p>
            <div class="input-group">
                <input type="text" class="form-control font-monospace bg-dark text-light" 
                       id="newApiKey" value="{{ session('new_key') }}" readonly>
                <button class="btn btn-outline-light" onclick="copyApiKey()">
                    <i class="fas fa-copy"></i> Copiar
                </button>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Tabla de API Keys --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Key (oculta)</th>
                            <th>Creada por</th>
                            <th>Último uso</th>
                            <th>Peticiones</th>
                            <th>Expira</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($apiKeys as $key)
                        <tr>
                            <td>
                                <i class="fas fa-key text-primary me-2"></i>
                                <strong>{{ $key->name }}</strong>
                            </td>
                            <td>
                                <code class="text-muted">{{ $key->masked_key }}</code>
                            </td>
                            <td>{{ $key->user->name ?? 'Sistema' }}</td>
                            <td>
                                @if($key->last_used_at)
                                    <small class="text-muted">
                                        {{ $key->last_used_at->diffForHumans() }}
                                    </small>
                                @else
                                    <span class="badge bg-secondary">Nunca</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ number_format($key->requests_count) }}</span>
                            </td>
                            <td>
                                @if($key->expires_at)
                                    <small class="text-{{ $key->expires_at->isPast() ? 'danger' : 'muted' }}">
                                        {{ $key->expires_at->format('d/m/Y') }}
                                    </small>
                                @else
                                    <span class="text-muted">Sin expiración</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $key->is_active ? 'success' : 'danger' }}">
                                    {{ $key->is_active ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <form action="{{ route('api-keys.toggle', $key) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning" 
                                                title="{{ $key->is_active ? 'Desactivar' : 'Activar' }}">
                                            <i class="fas fa-{{ $key->is_active ? 'ban' : 'check' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('api-keys.destroy', $key) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('¿Eliminar esta API Key permanentemente?')"
                                                title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-info-circle fa-2x mb-2"></i>
                                <p>No hay API Keys creadas. Crea una para comenzar a usar la API.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function copyApiKey() {
    const input = document.getElementById('newApiKey');
    input.select();
    document.execCommand('copy');
    
    alert('✅ API Key copiada al portapapeles');
}
</script>
@endsection
```

### Vista Create

Crear archivo: `resources/views/administration/api-keys/create.blade.php`

```blade
@extends('layouts.static')

@section('h1')
<i class="fas fa-plus-circle text-primary me-2"></i> Crear Nueva API Key
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('api-keys.index') }}">API Keys</a></li>
<li class="breadcrumb-item active">Crear</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-key me-2"></i> Nueva API Key</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('api-keys.store') }}" method="POST">
                        @csrf

                        {{-- Nombre de la API Key --}}
                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold">
                                <i class="fas fa-tag text-primary"></i> Nombre descriptivo
                            </label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   placeholder="Ej: App Móvil iOS, Integración CRM, Dashboard Público"
                                   value="{{ old('name') }}"
                                   required>
                            <small class="form-text text-muted">
                                Identifica para qué se usará esta API Key
                            </small>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Fecha de expiración --}}
                        <div class="mb-3">
                            <label for="expires_at" class="form-label fw-bold">
                                <i class="fas fa-calendar-times text-warning"></i> Fecha de expiración (opcional)
                            </label>
                            <input type="date" 
                                   class="form-control @error('expires_at') is-invalid @enderror" 
                                   id="expires_at" 
                                   name="expires_at"
                                   min="{{ now()->addDay()->format('Y-m-d') }}"
                                   value="{{ old('expires_at') }}">
                            <small class="form-text text-muted">
                                Deja vacío para que no expire nunca
                            </small>
                            @error('expires_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Botones --}}
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('api-keys.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Crear API Key
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Información de seguridad --}}
            <div class="alert alert-info mt-4">
                <h6><i class="fas fa-info-circle"></i> Información de Seguridad</h6>
                <ul class="mb-0">
                    <li>La API Key se mostrará <strong>solo una vez</strong> después de crearla</li>
                    <li>Guárdala en un lugar seguro (gestor de contraseñas, variables de entorno)</li>
                    <li>No la compartas en código público ni repositorios</li>
                    <li>Puedes desactivarla temporalmente sin eliminarla</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
```

---

## 9. Testing con Postman

### Colección de Postman

Crear archivo: `postman/Streamify_API_v1.postman_collection.json`

```json
{
  "info": {
    "name": "Streamify API v1.0",
    "description": "Colección de endpoints de la API REST de Streamify",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Health Check",
      "request": {
        "method": "GET",
        "header": [],
        "url": {
          "raw": "{{base_url}}/api/ping",
          "host": ["{{base_url}}"],
          "path": ["api", "ping"]
        }
      }
    },
    {
      "name": "Clientes",
      "item": [
        {
          "name": "Listar clientes",
          "request": {
            "method": "GET",
            "header": [
              {
                "key": "X-API-Key",
                "value": "{{api_key}}",
                "type": "text"
              }
            ],
            "url": {
              "raw": "{{base_url}}/api/v1/clientes?per_page=10",
              "host": ["{{base_url}}"],
              "path": ["api", "v1", "clientes"],
              "query": [
                {
                  "key": "per_page",
                  "value": "10"
                }
              ]
            }
          }
        },
        {
          "name": "Obtener cliente",
          "request": {
            "method": "GET",
            "header": [
              {
                "key": "X-API-Key",
                "value": "{{api_key}}",
                "type": "text"
              }
            ],
            "url": {
              "raw": "{{base_url}}/api/v1/clientes/1",
              "host": ["{{base_url}}"],
              "path": ["api", "v1", "clientes", "1"]
            }
          }
        },
        {
          "name": "Crear cliente",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "X-API-Key",
                "value": "{{api_key}}",
                "type": "text"
              },
              {
                "key": "Content-Type",
                "value": "application/json",
                "type": "text"
              }
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"nombre\": \"Juan Pérez\",\n  \"telefono\": \"555-1234\",\n  \"email\": \"juan@example.com\"\n}"
            },
            "url": {
              "raw": "{{base_url}}/api/v1/clientes",
              "host": ["{{base_url}}"],
              "path": ["api", "v1", "clientes"]
            }
          }
        }
      ]
    }
  ],
  "variable": [
    {
      "key": "base_url",
      "value": "http://localhost",
      "type": "string"
    },
    {
      "key": "api_key",
      "value": "sk_your_api_key_here",
      "type": "string"
    }
  ]
}
```

---

## 10. Ejemplos de Uso

### cURL

```bash
# Health check
curl http://localhost/api/ping

# Listar clientes
curl -H "X-API-Key: sk_your_api_key_here" \
     http://localhost/api/v1/clientes

# Crear cliente
curl -X POST \
     -H "X-API-Key: sk_your_api_key_here" \
     -H "Content-Type: application/json" \
     -d '{"nombre":"María García","telefono":"555-5678"}' \
     http://localhost/api/v1/clientes
```

### JavaScript (Axios)

```javascript
const axios = require('axios');

const API_URL = 'http://localhost/api/v1';
const API_KEY = 'sk_your_api_key_here';

// Configuración global
axios.defaults.headers.common['X-API-Key'] = API_KEY;

// Listar clientes
async function listarClientes() {
  try {
    const response = await axios.get(`${API_URL}/clientes`);
    console.log('Clientes:', response.data);
  } catch (error) {
    console.error('Error:', error.response.data);
  }
}

// Crear cliente
async function crearCliente(datos) {
  try {
    const response = await axios.post(`${API_URL}/clientes`, datos);
    console.log('Cliente creado:', response.data);
  } catch (error) {
    console.error('Error:', error.response.data);
  }
}
```

### PHP (cURL)

```php
<?php

$apiUrl = 'http://localhost/api/v1';
$apiKey = 'sk_your_api_key_here';

// Listar clientes
$ch = curl_init("$apiUrl/clientes");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "X-API-Key: $apiKey",
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$clientes = json_decode($response, true);
curl_close($ch);

print_r($clientes);
```

---

## 📋 Checklist de Implementación

### Fase 1: Base de Datos
- [ ] Crear migración `api_keys`
- [ ] Ejecutar `php artisan migrate`
- [ ] Verificar tabla en base de datos

### Fase 2: Modelo y Middleware
- [ ] Crear modelo `ApiKey.php`
- [ ] Implementar métodos auxiliares
- [ ] Crear middleware `AuthenticateApiKey`
- [ ] Registrar middleware en `Kernel.php`

### Fase 3: Controladores
- [ ] Crear `ClienteApiController`
- [ ] Implementar métodos CRUD
- [ ] Agregar manejo de errores

### Fase 4: Rutas
- [ ] Configurar rutas en `routes/api.php`
- [ ] Aplicar middleware a rutas protegidas
- [ ] Probar ruta de health check

### Fase 5: Panel de Administración
- [ ] Crear controlador `ApiKeyController`
- [ ] Crear vistas (index, create)
- [ ] Agregar rutas web
- [ ] Probar creación de API Keys

### Fase 6: Testing
- [ ] Probar con Postman/cURL
- [ ] Validar respuestas de error
- [ ] Verificar rate limiting
- [ ] Testear expiración de keys

### Fase 7: Documentación
- [ ] Documentar endpoints
- [ ] Crear colección de Postman
- [ ] Ejemplos de código

---

## 🎯 Próximos Pasos

1. **Crear más controladores API** para otros módulos
2. **Implementar webhooks** para notificaciones
3. **Agregar GraphQL** como alternativa a REST
4. **Crear SDK/biblioteca** para facilitar integración
5. **Dashboard de métricas** de uso de API

---

**Última actualización**: 3 de Diciembre, 2025  
**Versión**: 1.0.0  
**Mantenido por**: Equipo de Desarrollo Streamify
