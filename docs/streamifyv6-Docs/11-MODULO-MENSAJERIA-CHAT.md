# 11. Módulo de Mensajería - Chat Multi-Agente

## Índice
1. [Concepto y Arquitectura](#1-concepto-y-arquitectura)
2. [Base de Datos - Migraciones](#2-base-de-datos---migraciones)
3. [Modelos Eloquent](#3-modelos-eloquent)
4. [Seeders - Permisos](#4-seeders---permisos)
5. [Controladores y API](#5-controladores-y-api)
6. [WebSockets con Laravel Reverb](#6-websockets-con-laravel-reverb)
7. [Integración con n8n y DeepSeek](#7-integración-con-n8n-y-deepseek)
8. [Frontend - Componente Chat](#8-frontend---componente-chat)
9. [Webhooks para IA](#9-webhooks-para-ia)
10. [Ejemplos de Uso](#10-ejemplos-de-uso)

---

## 1. Concepto y Arquitectura

### 1.1 Modelo de Funcionamiento

```
┌─────────────────────────────────────────────────────────────┐
│                      STREAMIFY CHAT SYSTEM                   │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  Cliente 1 ────► │ Conversación 1 │ ◄──── Empleados con     │
│                  │   (Thread)     │       permiso "chat"    │
│                  │                │                          │
│  Cliente 2 ────► │ Conversación 2 │ ◄──── • Empleado A      │
│                  │   (Thread)     │       • Empleado B      │
│                  │                │       • Empleado C      │
│  Cliente 3 ────► │ Conversación 3 │                          │
│                  │   (Thread)     │       🤖 IA Assistant   │
│                  │                │       (DeepSeek/n8n)    │
│                  └────────────────┘                          │
│                                                              │
│  Características:                                           │
│  • Cada cliente tiene SU propio hilo                        │
│  • Todos los empleados ven TODAS las conversaciones         │
│  • Cualquier empleado puede responder cualquier chat        │
│  • La IA puede intervenir automáticamente                   │
│  • Se registra quién respondió (empleado o IA)              │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

### 1.2 Flujo de Mensajes

```
Escenario 1: Cliente envía mensaje
─────────────────────────────────────
Cliente → API → Base de Datos → WebSocket → Todos los empleados conectados
                                          → Webhook n8n → IA evalúa si responde

Escenario 2: Empleado responde
──────────────────────────────
Empleado → API → Base de Datos → WebSocket → Cliente
                                            → Otros empleados (notificación)

Escenario 3: IA responde automáticamente
────────────────────────────────────────
Cliente → n8n → DeepSeek → n8n → API → Base de Datos → WebSocket → Cliente
                                                                  → Empleados
```

### 1.3 Estados de Conversación

- 🟢 **`abierta`**: Conversación activa, esperando respuesta
- 🟡 **`en_atencion`**: Un empleado está escribiendo/atendiendo
- 🔵 **`en_espera`**: Cliente esperando respuesta
- ⚪ **`cerrada`**: Conversación finalizada
- 🤖 **`bot_activo`**: La IA está manejando la conversación

---

## 2. Base de Datos - Migraciones

### 2.1 Tabla: `conversaciones`

**Descripción**: Representa el hilo de chat entre un cliente y Streamify.

```bash
php artisan make:migration create_conversaciones_table
```

**Archivo**: `database/migrations/xxxx_create_conversaciones_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversaciones', function (Blueprint $table) {
            $table->id('idconv');
            $table->unsignedBigInteger('idcli'); // Cliente
            $table->enum('estado', ['abierta', 'en_atencion', 'en_espera', 'cerrada', 'bot_activo'])
                  ->default('abierta');
            $table->unsignedBigInteger('ultimo_idemp')->nullable(); // Último empleado que respondió
            $table->timestamp('ultima_actividad')->useCurrent();
            $table->integer('mensajes_no_leidos')->default(0); // Para empleados
            $table->boolean('requiere_humano')->default(false); // IA solicita escalación
            $table->json('metadata')->nullable(); // Info adicional (tema, prioridad, tags)
            $table->timestamps();

            // Foreign keys
            $table->foreign('idcli')
                  ->references('idcli')
                  ->on('clientes')
                  ->onDelete('cascade');

            $table->foreign('ultimo_idemp')
                  ->references('idemp')
                  ->on('empleados')
                  ->onDelete('set null');

            // Índices
            $table->index('estado');
            $table->index('ultima_actividad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversaciones');
    }
};
```

### 2.2 Tabla: `mensajes`

**Descripción**: Mensajes individuales dentro de cada conversación.

```bash
php artisan make:migration create_mensajes_table
```

**Archivo**: `database/migrations/xxxx_create_mensajes_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mensajes', function (Blueprint $table) {
            $table->id('idmsg');
            $table->unsignedBigInteger('idconv'); // Conversación a la que pertenece
            $table->enum('tipo_remitente', ['cliente', 'empleado', 'sistema', 'ia']); // Quién envió
            $table->unsignedBigInteger('idcli')->nullable(); // Si es cliente
            $table->unsignedBigInteger('idemp')->nullable(); // Si es empleado
            $table->text('contenido'); // Texto del mensaje
            $table->enum('tipo_contenido', ['texto', 'imagen', 'archivo', 'audio', 'sistema'])
                  ->default('texto');
            $table->string('archivo_url')->nullable(); // Si es archivo/imagen
            $table->boolean('leido')->default(false); // Para cliente
            $table->timestamp('leido_at')->nullable();
            $table->json('metadata')->nullable(); // IA confidence, sentiment, etc.
            $table->timestamps();

            // Foreign keys
            $table->foreign('idconv')
                  ->references('idconv')
                  ->on('conversaciones')
                  ->onDelete('cascade');

            $table->foreign('idcli')
                  ->references('idcli')
                  ->on('clientes')
                  ->onDelete('set null');

            $table->foreign('idemp')
                  ->references('idemp')
                  ->on('empleados')
                  ->onDelete('set null');

            // Índices
            $table->index('idconv');
            $table->index(['tipo_remitente', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensajes');
    }
};
```

### 2.3 Tabla: `empleados_online`

**Descripción**: Tracking de empleados conectados al chat.

```bash
php artisan make:migration create_empleados_online_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empleados_online', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idemp');
            $table->timestamp('ultima_actividad')->useCurrent();
            $table->string('session_id')->unique(); // WebSocket session
            $table->timestamps();

            $table->foreign('idemp')
                  ->references('idemp')
                  ->on('empleados')
                  ->onDelete('cascade');

            $table->index('ultima_actividad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empleados_online');
    }
};
```

### 2.4 Ejecutar Migraciones

```bash
php artisan migrate
```

---

## 3. Modelos Eloquent

### 3.1 Modelo: `Conversacion`

```bash
php artisan make:model Conversacion
```

**Archivo**: `app/Models/Conversacion.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Conversacion extends Model
{
    use HasFactory;

    protected $table = 'conversaciones';
    protected $primaryKey = 'idconv';

    protected $fillable = [
        'idcli',
        'estado',
        'ultimo_idemp',
        'ultima_actividad',
        'mensajes_no_leidos',
        'requiere_humano',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'ultima_actividad' => 'datetime',
        'requiere_humano' => 'boolean',
    ];

    /**
     * Relación con cliente
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'idcli', 'idcli');
    }

    /**
     * Relación con último empleado que atendió
     */
    public function ultimoEmpleado()
    {
        return $this->belongsTo(Empleado::class, 'ultimo_idemp', 'idemp');
    }

    /**
     * Mensajes de la conversación
     */
    public function mensajes()
    {
        return $this->hasMany(Mensaje::class, 'idconv', 'idconv')
                    ->orderBy('created_at', 'asc');
    }

    /**
     * Último mensaje de la conversación
     */
    public function ultimoMensaje()
    {
        return $this->hasOne(Mensaje::class, 'idconv', 'idconv')
                    ->latestOfMany();
    }

    /**
     * Marcar como leída (para empleados)
     */
    public function marcarComoLeida()
    {
        $this->update(['mensajes_no_leidos' => 0]);
    }

    /**
     * Cambiar estado
     */
    public function cambiarEstado(string $nuevoEstado, ?int $empleadoId = null)
    {
        $this->update([
            'estado' => $nuevoEstado,
            'ultimo_idemp' => $empleadoId,
            'ultima_actividad' => now(),
        ]);
    }

    /**
     * Scope: Conversaciones abiertas
     */
    public function scopeAbiertas($query)
    {
        return $query->whereIn('estado', ['abierta', 'en_atencion', 'en_espera', 'bot_activo']);
    }

    /**
     * Scope: Conversaciones con mensajes no leídos
     */
    public function scopeConMensajesNoLeidos($query)
    {
        return $query->where('mensajes_no_leidos', '>', 0);
    }
}
```

### 3.2 Modelo: `Mensaje`

```bash
php artisan make:model Mensaje
```

**Archivo**: `app/Models/Mensaje.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Mensaje extends Model
{
    use HasFactory;

    protected $table = 'mensajes';
    protected $primaryKey = 'idmsg';

    protected $fillable = [
        'idconv',
        'tipo_remitente',
        'idcli',
        'idemp',
        'contenido',
        'tipo_contenido',
        'archivo_url',
        'leido',
        'leido_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'leido' => 'boolean',
        'leido_at' => 'datetime',
    ];

    /**
     * Relación con conversación
     */
    public function conversacion()
    {
        return $this->belongsTo(Conversacion::class, 'idconv', 'idconv');
    }

    /**
     * Relación con cliente (si es remitente)
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'idcli', 'idcli');
    }

    /**
     * Relación con empleado (si es remitente)
     */
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'idemp', 'idemp');
    }

    /**
     * Marcar como leído
     */
    public function marcarComoLeido()
    {
        if (!$this->leido) {
            $this->update([
                'leido' => true,
                'leido_at' => now(),
            ]);
        }
    }

    /**
     * Obtener nombre del remitente
     */
    public function getNombreRemitenteAttribute()
    {
        switch ($this->tipo_remitente) {
            case 'cliente':
                return $this->cliente?->nombrecli ?? 'Cliente';
            case 'empleado':
                return $this->empleado?->nombreemp ?? 'Soporte';
            case 'ia':
                return 'Asistente Virtual';
            case 'sistema':
                return 'Sistema';
            default:
                return 'Desconocido';
        }
    }
}
```

---

## 4. Seeders - Permisos

### 4.1 Crear Seeder de Permisos

```bash
php artisan make:seeder ChatPermisosSeeder
```

**Archivo**: `database/seeders/ChatPermisosSeeder.php`

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ChatPermisosSeeder extends Seeder
{
    public function run(): void
    {
        // Crear permisos de chat
        $permisos = [
            'chat.ver' => 'Ver conversaciones de chat',
            'chat.responder' => 'Responder mensajes de clientes',
            'chat.cerrar' => 'Cerrar conversaciones',
            'chat.transferir' => 'Transferir conversaciones',
            'chat.admin' => 'Administrar sistema de chat',
            'chat.ver_estadisticas' => 'Ver estadísticas de chat',
        ];

        foreach ($permisos as $nombre => $descripcion) {
            Permission::firstOrCreate(
                ['name' => $nombre],
                ['description' => $descripcion]
            );
        }

        // Asignar permisos a roles
        $admin = Role::firstOrCreate(['name' => 'Administrador']);
        $admin->givePermissionTo([
            'chat.ver',
            'chat.responder',
            'chat.cerrar',
            'chat.transferir',
            'chat.admin',
            'chat.ver_estadisticas',
        ]);

        $soporte = Role::firstOrCreate(['name' => 'Soporte']);
        $soporte->givePermissionTo([
            'chat.ver',
            'chat.responder',
            'chat.cerrar',
        ]);

        $vendedor = Role::firstOrCreate(['name' => 'Vendedor']);
        $vendedor->givePermissionTo([
            'chat.ver',
            'chat.responder',
        ]);

        $this->command->info('✅ Permisos de chat creados y asignados');
    }
}
```

### 4.2 Ejecutar Seeder

```bash
php artisan db:seed --class=ChatPermisosSeeder
```

---

## 5. Controladores y API

### 5.1 Crear Controlador de Chat

```bash
php artisan make:controller Api/V1/ChatController
```

**Archivo**: `app/Http/Controllers/Api/V1/ChatController.php`

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Conversacion;
use App\Models\Mensaje;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Events\NuevoMensaje;
use App\Events\ConversacionActualizada;

class ChatController extends Controller
{
    /**
     * Listar todas las conversaciones (para empleados)
     * GET /api/v1/chat/conversaciones
     */
    public function listarConversaciones(Request $request)
    {
        try {
            // Verificar permiso
            $empleado = auth()->guard('empleado')->user();
            
            if (!$empleado || !$empleado->can('chat.ver')) {
                return response()->json([
                    'success' => false,
                    'error' => 'No tienes permiso para ver el chat'
                ], 403);
            }

            $estado = $request->input('estado'); // abierta, cerrada, etc.
            $search = $request->input('search'); // Buscar por nombre de cliente

            $query = Conversacion::with([
                'cliente:idcli,nombrecli,telefonocli',
                'ultimoEmpleado:idemp,nombreemp',
                'ultimoMensaje'
            ]);

            // Filtrar por estado
            if ($estado) {
                $query->where('estado', $estado);
            } else {
                // Por defecto, solo conversaciones abiertas
                $query->abiertas();
            }

            // Buscar por cliente
            if ($search) {
                $query->whereHas('cliente', function ($q) use ($search) {
                    $q->where('nombrecli', 'like', "%{$search}%")
                      ->orWhere('telefonocli', 'like', "%{$search}%");
                });
            }

            $conversaciones = $query->orderBy('ultima_actividad', 'desc')
                                   ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $conversaciones->items(),
                'pagination' => [
                    'total' => $conversaciones->total(),
                    'current_page' => $conversaciones->currentPage(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener conversaciones',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener mensajes de una conversación
     * GET /api/v1/chat/conversaciones/{id}/mensajes
     */
    public function obtenerMensajes(string $idconv)
    {
        try {
            $empleado = auth()->guard('empleado')->user();
            
            if (!$empleado || !$empleado->can('chat.ver')) {
                return response()->json([
                    'success' => false,
                    'error' => 'No tienes permiso'
                ], 403);
            }

            $conversacion = Conversacion::with([
                'cliente',
                'mensajes.empleado:idemp,nombreemp',
                'mensajes.cliente:idcli,nombrecli'
            ])->find($idconv);

            if (!$conversacion) {
                return response()->json([
                    'success' => false,
                    'error' => 'Conversación no encontrada'
                ], 404);
            }

            // Marcar mensajes como leídos por empleado
            $conversacion->marcarComoLeida();

            return response()->json([
                'success' => true,
                'data' => [
                    'conversacion' => $conversacion,
                    'mensajes' => $conversacion->mensajes,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener mensajes',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar mensaje como empleado
     * POST /api/v1/chat/conversaciones/{id}/mensajes
     */
    public function enviarMensaje(Request $request, string $idconv)
    {
        try {
            $empleado = auth()->guard('empleado')->user();
            
            if (!$empleado || !$empleado->can('chat.responder')) {
                return response()->json([
                    'success' => false,
                    'error' => 'No tienes permiso para responder'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'contenido' => 'required|string',
                'tipo_contenido' => 'nullable|in:texto,imagen,archivo',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $conversacion = Conversacion::find($idconv);

            if (!$conversacion) {
                return response()->json([
                    'success' => false,
                    'error' => 'Conversación no encontrada'
                ], 404);
            }

            // Crear mensaje
            $mensaje = Mensaje::create([
                'idconv' => $idconv,
                'tipo_remitente' => 'empleado',
                'idemp' => $empleado->idemp,
                'contenido' => $request->contenido,
                'tipo_contenido' => $request->tipo_contenido ?? 'texto',
            ]);

            // Actualizar conversación
            $conversacion->cambiarEstado('en_atencion', $empleado->idemp);

            // Disparar evento WebSocket
            broadcast(new NuevoMensaje($mensaje->load(['empleado', 'conversacion.cliente'])));

            return response()->json([
                'success' => true,
                'message' => 'Mensaje enviado',
                'data' => $mensaje
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al enviar mensaje',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cliente envía mensaje (inicia o continúa conversación)
     * POST /api/v1/chat/cliente/enviar
     */
    public function clienteEnviarMensaje(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'idcli' => 'required|exists:clientes,idcli',
                'contenido' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Buscar o crear conversación para este cliente
            $conversacion = Conversacion::firstOrCreate(
                [
                    'idcli' => $request->idcli,
                    'estado' => 'abierta', // Solo buscar si está abierta
                ],
                [
                    'estado' => 'abierta',
                    'ultima_actividad' => now(),
                ]
            );

            // Si encontró una cerrada, crear nueva
            if (!$conversacion->wasRecentlyCreated && $conversacion->estado === 'cerrada') {
                $conversacion = Conversacion::create([
                    'idcli' => $request->idcli,
                    'estado' => 'abierta',
                    'ultima_actividad' => now(),
                ]);
            }

            // Crear mensaje del cliente
            $mensaje = Mensaje::create([
                'idconv' => $conversacion->idconv,
                'tipo_remitente' => 'cliente',
                'idcli' => $request->idcli,
                'contenido' => $request->contenido,
                'tipo_contenido' => 'texto',
            ]);

            // Incrementar contador de mensajes no leídos
            $conversacion->increment('mensajes_no_leidos');
            $conversacion->update(['ultima_actividad' => now()]);

            // Disparar evento WebSocket para empleados
            broadcast(new NuevoMensaje($mensaje->load(['cliente', 'conversacion'])));

            // Webhook para n8n (IA puede responder)
            $this->notificarWebhook($conversacion, $mensaje);

            return response()->json([
                'success' => true,
                'message' => 'Mensaje enviado',
                'data' => [
                    'conversacion' => $conversacion,
                    'mensaje' => $mensaje,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al enviar mensaje',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar estado de conversación
     * PATCH /api/v1/chat/conversaciones/{id}/estado
     */
    public function cambiarEstado(Request $request, string $idconv)
    {
        try {
            $empleado = auth()->guard('empleado')->user();
            
            if (!$empleado || !$empleado->can('chat.cerrar')) {
                return response()->json([
                    'success' => false,
                    'error' => 'No tienes permiso'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'estado' => 'required|in:abierta,en_atencion,cerrada,bot_activo',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $conversacion = Conversacion::find($idconv);

            if (!$conversacion) {
                return response()->json([
                    'success' => false,
                    'error' => 'Conversación no encontrada'
                ], 404);
            }

            $conversacion->cambiarEstado($request->estado, $empleado->idemp);

            // Si se cierra, enviar mensaje del sistema
            if ($request->estado === 'cerrada') {
                Mensaje::create([
                    'idconv' => $idconv,
                    'tipo_remitente' => 'sistema',
                    'contenido' => "Conversación cerrada por {$empleado->nombreemp}",
                    'tipo_contenido' => 'sistema',
                ]);
            }

            broadcast(new ConversacionActualizada($conversacion));

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado',
                'data' => $conversacion
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al cambiar estado',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas de chat
     * GET /api/v1/chat/estadisticas
     */
    public function estadisticas()
    {
        try {
            $empleado = auth()->guard('empleado')->user();
            
            if (!$empleado || !$empleado->can('chat.ver_estadisticas')) {
                return response()->json([
                    'success' => false,
                    'error' => 'No tienes permiso'
                ], 403);
            }

            $abiertas = Conversacion::where('estado', 'abierta')->count();
            $enAtencion = Conversacion::where('estado', 'en_atencion')->count();
            $cerradasHoy = Conversacion::where('estado', 'cerrada')
                ->whereDate('updated_at', today())
                ->count();
            
            $noLeidos = Conversacion::sum('mensajes_no_leidos');

            return response()->json([
                'success' => true,
                'data' => [
                    'conversaciones_abiertas' => $abiertas,
                    'en_atencion' => $enAtencion,
                    'cerradas_hoy' => $cerradasHoy,
                    'mensajes_no_leidos' => $noLeidos,
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

    /**
     * Notificar webhook n8n para procesamiento IA
     */
    private function notificarWebhook($conversacion, $mensaje)
    {
        try {
            $webhookUrl = env('N8N_WEBHOOK_URL');
            
            if (!$webhookUrl) {
                return; // No hay webhook configurado
            }

            $client = new \GuzzleHttp\Client();
            $client->post($webhookUrl, [
                'json' => [
                    'conversacion_id' => $conversacion->idconv,
                    'cliente_id' => $conversacion->idcli,
                    'cliente_nombre' => $conversacion->cliente->nombrecli,
                    'mensaje' => $mensaje->contenido,
                    'timestamp' => now()->toIso8601String(),
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al notificar webhook: ' . $e->getMessage());
        }
    }
}
```

### 5.2 Rutas API

**Archivo**: `routes/api.php`

```php
use App\Http\Controllers\Api\V1\ChatController;

Route::prefix('v1')->middleware('api.key')->group(function () {
    
    // Chat - Empleados
    Route::get('/chat/conversaciones', [ChatController::class, 'listarConversaciones']);
    Route::get('/chat/conversaciones/{id}/mensajes', [ChatController::class, 'obtenerMensajes']);
    Route::post('/chat/conversaciones/{id}/mensajes', [ChatController::class, 'enviarMensaje']);
    Route::patch('/chat/conversaciones/{id}/estado', [ChatController::class, 'cambiarEstado']);
    Route::get('/chat/estadisticas', [ChatController::class, 'estadisticas']);
    
    // Chat - Clientes (puede ser sin API Key, con auth de cliente)
    Route::post('/chat/cliente/enviar', [ChatController::class, 'clienteEnviarMensaje']);
});
```

---

## 6. WebSockets con Laravel Reverb

### 6.1 Instalar Laravel Reverb

```bash
php artisan install:broadcasting
```

### 6.2 Crear Eventos

**Evento: NuevoMensaje**

```bash
php artisan make:event NuevoMensaje
```

**Archivo**: `app/Events/NuevoMensaje.php`

```php
<?php

namespace App\Events;

use App\Models\Mensaje;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NuevoMensaje implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $mensaje;

    public function __construct(Mensaje $mensaje)
    {
        $this->mensaje = $mensaje;
    }

    public function broadcastOn()
    {
        return [
            new Channel('chat'),
            new Channel('conversacion.' . $this->mensaje->idconv),
        ];
    }

    public function broadcastAs()
    {
        return 'mensaje.nuevo';
    }

    public function broadcastWith()
    {
        return [
            'mensaje' => $this->mensaje,
            'remitente' => $this->mensaje->nombre_remitente,
            'timestamp' => $this->mensaje->created_at->toIso8601String(),
        ];
    }
}
```

### 6.3 Iniciar Reverb

```bash
php artisan reverb:start
```

---

## 7. Integración con n8n y DeepSeek

### 7.1 Flujo en n8n

```
┌─────────────────────────────────────────────────────────┐
│                    Workflow n8n                         │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  1. Webhook ← Laravel envía nuevo mensaje cliente       │
│       │                                                  │
│       ▼                                                  │
│  2. Filtro: ¿Es pregunta frecuente?                     │
│       │                                                  │
│       ├─ SÍ → 3a. Respuesta automática                  │
│       │           │                                      │
│       │           └─► 4. POST /api/v1/chat/.../mensajes │
│       │                   (tipo_remitente: 'ia')        │
│       │                                                  │
│       └─ NO → 3b. Enviar a DeepSeek                     │
│                   │                                      │
│                   ▼                                      │
│              DeepSeek analiza contexto                   │
│                   │                                      │
│                   ├─ Puede responder                     │
│                   │   └─► 4. POST con respuesta IA      │
│                   │                                      │
│                   └─ Requiere humano                     │
│                       └─► 5. PATCH estado: requiere_humano│
│                                                          │
└─────────────────────────────────────────────────────────┘
```

### 7.2 Configuración n8n

**Nodo 1: Webhook**
```json
{
  "method": "POST",
  "path": "streamify-chat",
  "responseMode": "onReceived"
}
```

**Nodo 2: DeepSeek API**
```json
{
  "url": "https://api.deepseek.com/v1/chat/completions",
  "method": "POST",
  "headers": {
    "Authorization": "Bearer {{ $env.DEEPSEEK_API_KEY }}",
    "Content-Type": "application/json"
  },
  "body": {
    "model": "deepseek-chat",
    "messages": [
      {
        "role": "system",
        "content": "Eres el asistente virtual de Streamify. Ayudas a clientes con consultas sobre servicios de streaming, saldos, y soporte técnico. Si no puedes resolver, escala a humano."
      },
      {
        "role": "user",
        "content": "{{ $json.mensaje }}"
      }
    ]
  }
}
```

**Nodo 3: HTTP Request a Laravel**
```json
{
  "url": "http://localhost/api/v1/chat/conversaciones/{{ $json.conversacion_id }}/mensajes",
  "method": "POST",
  "headers": {
    "X-API-Key": "{{ $env.STREAMIFY_API_KEY }}",
    "Content-Type": "application/json"
  },
  "body": {
    "contenido": "{{ $json.deepseek_response }}",
    "tipo_remitente": "ia"
  }
}
```

### 7.3 Variables de Entorno

**Archivo**: `.env`

```env
# n8n Webhook
N8N_WEBHOOK_URL=http://localhost:5678/webhook/streamify-chat

# DeepSeek API
DEEPSEEK_API_KEY=sk-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

# Laravel Reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
```

---

## 8. Frontend - Componente Chat

### 8.1 Vista Livewire (Ejemplo)

```bash
php artisan make:livewire Chat/PanelChat
```

**Archivo**: `app/Livewire/Chat/PanelChat.php`

```php
<?php

namespace App\Livewire\Chat;

use Livewire\Component;
use App\Models\Conversacion;
use App\Models\Mensaje;

class PanelChat extends Component
{
    public $conversaciones;
    public $conversacionActiva;
    public $mensajes = [];
    public $nuevoMensaje = '';

    protected $listeners = [
        'echo:chat,mensaje.nuevo' => 'recibirMensaje',
    ];

    public function mount()
    {
        $this->cargarConversaciones();
    }

    public function cargarConversaciones()
    {
        $this->conversaciones = Conversacion::with(['cliente', 'ultimoMensaje'])
            ->abiertas()
            ->orderBy('ultima_actividad', 'desc')
            ->get();
    }

    public function seleccionarConversacion($idconv)
    {
        $this->conversacionActiva = Conversacion::find($idconv);
        $this->mensajes = $this->conversacionActiva->mensajes;
        $this->conversacionActiva->marcarComoLeida();
    }

    public function enviarMensaje()
    {
        if (empty($this->nuevoMensaje)) {
            return;
        }

        $mensaje = Mensaje::create([
            'idconv' => $this->conversacionActiva->idconv,
            'tipo_remitente' => 'empleado',
            'idemp' => auth()->id(),
            'contenido' => $this->nuevoMensaje,
            'tipo_contenido' => 'texto',
        ]);

        $this->mensajes[] = $mensaje;
        $this->nuevoMensaje = '';

        broadcast(new \App\Events\NuevoMensaje($mensaje));
    }

    public function recibirMensaje($event)
    {
        if ($this->conversacionActiva && $event['mensaje']['idconv'] == $this->conversacionActiva->idconv) {
            $this->mensajes[] = Mensaje::find($event['mensaje']['idmsg']);
        }
        
        $this->cargarConversaciones();
    }

    public function render()
    {
        return view('livewire.chat.panel-chat');
    }
}
```

### 8.2 Vista Blade

**Archivo**: `resources/views/livewire/chat/panel-chat.blade.php`

```html
<div class="flex h-screen">
    <!-- Lista de conversaciones -->
    <div class="w-1/3 bg-gray-100 border-r overflow-y-auto">
        <div class="p-4 bg-blue-600 text-white">
            <h2 class="text-xl font-bold">Chat Streamify</h2>
        </div>

        @foreach($conversaciones as $conv)
            <div wire:click="seleccionarConversacion({{ $conv->idconv }})"
                 class="p-4 border-b hover:bg-gray-200 cursor-pointer {{ $conversacionActiva?->idconv == $conv->idconv ? 'bg-gray-200' : '' }}">
                <div class="flex justify-between">
                    <span class="font-semibold">{{ $conv->cliente->nombrecli }}</span>
                    @if($conv->mensajes_no_leidos > 0)
                        <span class="bg-red-500 text-white rounded-full px-2 text-xs">
                            {{ $conv->mensajes_no_leidos }}
                        </span>
                    @endif
                </div>
                <p class="text-sm text-gray-600 truncate">
                    {{ $conv->ultimoMensaje?->contenido }}
                </p>
            </div>
        @endforeach
    </div>

    <!-- Panel de mensajes -->
    <div class="w-2/3 flex flex-col">
        @if($conversacionActiva)
            <!-- Header -->
            <div class="p-4 bg-blue-600 text-white flex justify-between">
                <div>
                    <h3 class="font-bold">{{ $conversacionActiva->cliente->nombrecli }}</h3>
                    <p class="text-sm">{{ $conversacionActiva->cliente->telefonocli }}</p>
                </div>
                <button wire:click="cerrarConversacion" class="bg-red-500 px-4 py-2 rounded">
                    Cerrar Chat
                </button>
            </div>

            <!-- Mensajes -->
            <div class="flex-1 overflow-y-auto p-4 bg-gray-50">
                @foreach($mensajes as $msg)
                    <div class="mb-4 {{ $msg->tipo_remitente == 'empleado' ? 'text-right' : '' }}">
                        <div class="inline-block max-w-xs px-4 py-2 rounded-lg
                            {{ $msg->tipo_remitente == 'empleado' ? 'bg-blue-500 text-white' : 'bg-gray-300' }}">
                            <p class="text-sm font-semibold">{{ $msg->nombre_remitente }}</p>
                            <p>{{ $msg->contenido }}</p>
                            <p class="text-xs opacity-75 mt-1">
                                {{ $msg->created_at->format('H:i') }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Input -->
            <div class="p-4 bg-white border-t">
                <form wire:submit.prevent="enviarMensaje" class="flex">
                    <input type="text" wire:model="nuevoMensaje"
                           class="flex-1 border rounded-l px-4 py-2"
                           placeholder="Escribe un mensaje...">
                    <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-r">
                        Enviar
                    </button>
                </form>
            </div>
        @else
            <div class="flex-1 flex items-center justify-center text-gray-400">
                Selecciona una conversación
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Escuchar eventos de WebSocket
    Echo.channel('chat')
        .listen('.mensaje.nuevo', (e) => {
            @this.call('recibirMensaje', e);
        });
</script>
@endpush
```

---

## 9. Webhooks para IA

### 9.1 Endpoint para Respuesta IA

**Ruta**: `routes/api.php`

```php
// Webhook para que n8n envíe respuestas de la IA
Route::post('/v1/chat/ia/responder', function (Request $request) {
    $conversacion = Conversacion::find($request->conversacion_id);
    
    if (!$conversacion) {
        return response()->json(['error' => 'Conversación no encontrada'], 404);
    }

    $mensaje = Mensaje::create([
        'idconv' => $request->conversacion_id,
        'tipo_remitente' => 'ia',
        'contenido' => $request->respuesta,
        'tipo_contenido' => 'texto',
        'metadata' => [
            'confidence' => $request->confidence ?? null,
            'model' => 'deepseek',
        ],
    ]);

    // Si la IA no pudo responder, marcar para humano
    if ($request->requiere_humano) {
        $conversacion->update(['requiere_humano' => true]);
    }

    broadcast(new NuevoMensaje($mensaje->load('conversacion.cliente')));

    return response()->json(['success' => true]);
});
```

---

## 10. Ejemplos de Uso

### 10.1 Cliente envía mensaje (cURL)

```bash
curl -X POST http://localhost/api/v1/chat/cliente/enviar \
  -H "Content-Type: application/json" \
  -d '{
    "idcli": 1,
    "contenido": "Hola, necesito ayuda con mi saldo"
  }'
```

### 10.2 Empleado lista conversaciones

```bash
curl -X GET http://localhost/api/v1/chat/conversaciones \
  -H "X-API-Key: sk_X23MK7KzyvLe7XdbYSlYjSIPTd3eQFlSvW5SmPBrsk5WKpNrfdRUexhEtN2X"
```

### 10.3 Empleado responde

```bash
curl -X POST http://localhost/api/v1/chat/conversaciones/1/mensajes \
  -H "X-API-Key: sk_X23MK7KzyvLe7XdbYSlYjSIPTd3eQFlSvW5SmPBrsk5WKpNrfdRUexhEtN2X" \
  -H "Content-Type: application/json" \
  -d '{
    "contenido": "Hola, tu saldo actual es $150. ¿En qué más puedo ayudarte?"
  }'
```

### 10.4 Script Python - Bot IA

```python
import requests

API_KEY = 'sk_X23MK7KzyvLe7XdbYSlYjSIPTd3eQFlSvW5SmPBrsk5WKpNrfdRUexhEtN2X'
BASE_URL = 'http://localhost/api/v1'

def obtener_conversaciones_pendientes():
    response = requests.get(
        f'{BASE_URL}/chat/conversaciones',
        headers={'X-API-Key': API_KEY},
        params={'estado': 'abierta'}
    )
    return response.json()['data']

def responder_con_ia(conversacion_id, mensaje_cliente):
    # Aquí conectarías con DeepSeek o tu modelo de IA
    respuesta_ia = procesar_con_deepseek(mensaje_cliente)
    
    requests.post(
        f'{BASE_URL}/chat/conversaciones/{conversacion_id}/mensajes',
        headers={'X-API-Key': API_KEY},
        json={
            'contenido': respuesta_ia,
            'tipo_remitente': 'ia'
        }
    )

# Loop principal
while True:
    conversaciones = obtener_conversaciones_pendientes()
    for conv in conversaciones:
        ultimo_msg = conv['ultimo_mensaje']
        if ultimo_msg['tipo_remitente'] == 'cliente':
            responder_con_ia(conv['idconv'], ultimo_msg['contenido'])
    time.sleep(5)
```

---

## Resumen de Arquitectura

**Base de Datos**:
- ✅ `conversaciones` - Hilos de chat por cliente
- ✅ `mensajes` - Mensajes individuales
- ✅ `empleados_online` - Tracking de empleados conectados

**Permisos**:
- ✅ `chat.ver` - Ver conversaciones
- ✅ `chat.responder` - Responder mensajes
- ✅ `chat.cerrar` - Cerrar conversaciones

**API Endpoints**:
- ✅ `GET /chat/conversaciones` - Listar chats
- ✅ `GET /chat/conversaciones/{id}/mensajes` - Ver mensajes
- ✅ `POST /chat/conversaciones/{id}/mensajes` - Responder
- ✅ `POST /chat/cliente/enviar` - Cliente envía mensaje
- ✅ `POST /chat/ia/responder` - IA responde (webhook)

**WebSockets**:
- ✅ Canal `chat` - Broadcast global
- ✅ Canal `conversacion.{id}` - Por conversación
- ✅ Evento `mensaje.nuevo` - Nuevos mensajes

**Integración IA**:
- ✅ Webhook a n8n cuando cliente envía mensaje
- ✅ n8n procesa con DeepSeek
- ✅ n8n responde vía API
- ✅ Flag `requiere_humano` para escalación

---

**🚀 ¡Sistema de chat multi-agente listo para implementar!**
