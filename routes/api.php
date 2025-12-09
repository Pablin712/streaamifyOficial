<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\Api\V1\ClienteApiController;
use App\Http\Controllers\Api\V1\ChatController;
use App\Http\Controllers\Api\V1\VentaApiController;
use App\Http\Controllers\Api\V1\QuickResponseController;
use App\Http\Controllers\Api\V1\AIAssistantController;

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

        // Ventas (CRUD completo + métodos adicionales)
        Route::apiResource('ventas', VentaApiController::class);
        Route::post('ventas/{id}/renovar', [VentaApiController::class, 'renovar']);
        Route::get('ventas/{id}/detalles', [VentaApiController::class, 'detalles']);
        Route::get('ventas-estadisticas', [VentaApiController::class, 'estadisticas']);

        // Chat - Empleados
        Route::get('/chat/conversaciones', [ChatController::class, 'listarConversaciones']);
        Route::get('/chat/conversaciones/{id}/mensajes', [ChatController::class, 'obtenerMensajes']);
        Route::post('/chat/conversaciones/{id}/mensajes', [ChatController::class, 'enviarMensaje']);
        Route::patch('/chat/conversaciones/{id}/estado', [ChatController::class, 'cambiarEstado']);
        Route::get('/chat/estadisticas', [ChatController::class, 'estadisticas']);

        // Chat - Endpoints para n8n AI Bot
        Route::get('/chat/n8n/mensajes-pendientes', [ChatController::class, 'mensajesPendientesParaAI']);
        Route::post('/chat/n8n/responder', [ChatController::class, 'responderDesdeAI']);
        Route::post('/chat/n8n/marcar-requiere-humano', [ChatController::class, 'marcarRequiereHumano']);

        // Respuestas Rápidas (CRUD + búsqueda)
        Route::get('/quick-responses', [QuickResponseController::class, 'index']);
        Route::get('/quick-responses/search', [QuickResponseController::class, 'search']);
        Route::get('/quick-responses/comando/{comando}', [QuickResponseController::class, 'porComando']);
        Route::post('/quick-responses', [QuickResponseController::class, 'store']);
        Route::put('/quick-responses/{id}', [QuickResponseController::class, 'update']);
        Route::delete('/quick-responses/{id}', [QuickResponseController::class, 'destroy']);

        // AI Assistant - Endpoints para n8n + DeepSeek (empleados)
        Route::prefix('ai')->group(function() {
            Route::get('/perfiles-disponibles', [AIAssistantController::class, 'perfilesDisponibles']);
            Route::get('/servicios', [AIAssistantController::class, 'serviciosDisponibles']);
            Route::get('/precios', [AIAssistantController::class, 'preciosServicios']);
            Route::get('/buscar-cliente', [AIAssistantController::class, 'buscarCliente']);
            Route::get('/cliente/{id}/ventas', [AIAssistantController::class, 'ventasCliente']);
            Route::get('/estadisticas', [AIAssistantController::class, 'estadisticas']);
        });
    });

    // Chat - Público (sin autenticación, para clientes)
    Route::post('/chat/cliente/enviar', [ChatController::class, 'clienteEnviarMensaje']);
    Route::get('/chat/cliente/{idcli}/conversacion', [ChatController::class, 'obtenerConversacionCliente']);
    Route::post('/chat/anonimo/enviar', [ChatController::class, 'anonimoEnviarMensaje']);
    Route::get('/chat/anonimo/{sessionId}/conversacion', [ChatController::class, 'obtenerConversacionAnonima']);

    // Respuestas Rápidas - Público (para clientes, solo tipo=cliente o ambos)
    Route::get('/public/quick-responses', [QuickResponseController::class, 'index']);
    Route::get('/public/quick-responses/comando/{comando}', [QuickResponseController::class, 'porComando']);
    Route::get('/public/quick-responses/search', [QuickResponseController::class, 'search']);

    // AI Assistant - Público (para chatbot de clientes)
    Route::prefix('public/ai')->group(function() {
        Route::get('/knowledge-base', [AIAssistantController::class, 'knowledgeBase']);
        Route::get('/servicios', [AIAssistantController::class, 'serviciosDisponibles']);
        Route::get('/precios', [AIAssistantController::class, 'preciosServicios']);
    });
});

//Route::middleware('auth:api')->post('ventas', [VentaController::class, 'storeApi']);

