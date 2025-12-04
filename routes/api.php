<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\Api\V1\ClienteApiController;
use App\Http\Controllers\Api\V1\ChatController;

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

// === RUTAS ANTIGUAS (mantener temporalmente para compatibilidad) ===
Route::post('login', [LoginController::class, 'loginApi']);
Route::post('logout', [LoginController::class, 'logoutApi']);
Route::middleware('auth:api')->get('ventas', [VentaController::class, 'indexApi']);
Route::middleware('auth:api')->get('clientes', [ClienteController::class, 'indexApi']);

// === API v1 - Con autenticación por API Key ===
Route::prefix('v1')->group(function () {

    // Rutas protegidas con API Key
    Route::middleware('api.key')->group(function () {
        Route::get('perfil', function () {
            $empleado = auth()->guard('empleado')->user();
            return response()->json([
                'success' => true,
                'data' => $empleado
            ]);
        });
        // Clientes (CRUD completo)
        Route::apiResource('clientes', ClienteApiController::class);
        Route::get('clientes/{id}/ventas', [ClienteApiController::class, 'ventas']);

        // Chat - Empleados
        Route::get('/chat/conversaciones', [ChatController::class, 'listarConversaciones']);
        Route::get('/chat/conversaciones/{id}/mensajes', [ChatController::class, 'obtenerMensajes']);
        Route::post('/chat/conversaciones/{id}/mensajes', [ChatController::class, 'enviarMensaje']);
        Route::patch('/chat/conversaciones/{id}/estado', [ChatController::class, 'cambiarEstado']);
        Route::get('/chat/estadisticas', [ChatController::class, 'estadisticas']);
    });

    // Chat - Público (sin autenticación, para clientes)
    Route::post('/chat/cliente/enviar', [ChatController::class, 'clienteEnviarMensaje']);
    Route::get('/chat/cliente/{idcli}/conversacion', [ChatController::class, 'obtenerConversacionCliente']);
    Route::post('/chat/anonimo/enviar', [ChatController::class, 'anonimoEnviarMensaje']);
    Route::get('/chat/anonimo/{sessionId}/conversacion', [ChatController::class, 'obtenerConversacionAnonima']);
});

//Route::middleware('auth:api')->post('ventas', [VentaController::class, 'storeApi']);

