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
use App\Http\Controllers\Api\V2\AuthController;
use App\Http\Controllers\Api\V2\InformationController;
use App\Http\Controllers\Api\V2\ContadorController;
use App\Http\Controllers\Api\V2\TecnicoCuentasController;
use App\Http\Controllers\Api\V2\TecnicoUsuariosController;
use App\Http\Controllers\Api\V2\TecnicoVentasController;
use App\Http\Controllers\Api\V2\TecnicoProductosController;
use App\Http\Controllers\Api\V2\TecnicoConfigController;
use App\Http\Controllers\Api\DailyStatisticsController;

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

// === ESTADÍSTICAS DIARIAS (CRON JOB) ===
Route::prefix('daily-statistics')->group(function () {
    Route::match(['get', 'post'], '/save', [DailyStatisticsController::class, 'save']);
    Route::post('/save-range', [DailyStatisticsController::class, 'saveRange']);
    Route::get('/{date}', [DailyStatisticsController::class, 'show']);
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
        Route::apiResource('clientes', ClienteApiController::class)->names([
            'index' => 'api.clientes.index',
            'store' => 'api.clientes.store',
            'show' => 'api.clientes.show',
            'update' => 'api.clientes.update',
            'destroy' => 'api.clientes.destroy',
        ]);
        Route::get('clientes/{id}/ventas', [ClienteApiController::class, 'ventas']);

        // Ventas (CRUD completo + métodos adicionales)
        Route::apiResource('ventas', VentaApiController::class)->names([
            'index' => 'api.ventas.index',
            'store' => 'api.ventas.store',
            'show' => 'api.ventas.show',
            'update' => 'api.ventas.update',
            'destroy' => 'api.ventas.destroy',
        ]);
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

    // Chat - Público (clientes autenticados y anónimos) - SIN API Key
    Route::post('/chat/cliente/enviar', [ChatController::class, 'clienteEnviarMensaje'])->name('api.chat.cliente.enviar');
    Route::get('/chat/cliente/{idcli}/conversacion', [ChatController::class, 'obtenerConversacionCliente'])->name('api.chat.cliente.conversacion');
    Route::post('/chat/anonimo/enviar', [ChatController::class, 'anonimoEnviarMensaje'])->name('api.chat.anonimo.enviar');
    Route::get('/chat/anonimo/{sessionId}/conversacion', [ChatController::class, 'obtenerConversacionAnonima'])->name('api.chat.anonimo.conversacion');

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

Route::prefix('v2')->group(function () {
    // Ruta de prueba simple
    Route::get('/test', function () {
        return response()->json(['success' => true, 'message' => 'Test route works!']);
    });

    // Validación de Credenciales - Público (para N8N sin bcrypt)
    Route::controller(AuthController::class)->group(function () {
        Route::post('/auth/create-customer', 'crearCliente')->name('api.auth.create-customer');
        Route::post('/auth/validate-credentials', 'validarCredenciales')->name('api.auth.validate');
    });

    // Rutas públicas de información (sin prefijo 'info')
    Route::controller(InformationController::class)->group(function () {
        Route::get('/precios', 'getPrecios')->name('api.v2.precios.public');
        Route::get('/metodos-pago', 'getMetodosPago')->name('api.v2.metodos-pago.public');
        Route::get('/banco/{nombrebanco}', 'getBanco')->name('api.v2.banco.public');
        Route::get('/tareas-hoy', 'getTareasHoy')->name('api.v2.tareas-hoy.public');
    });

    // Información y Precios - Público (con prefijo 'info' - legacy)
    Route::controller(InformationController::class)->prefix('info')->group(function () {
        Route::get('/precios', 'getPrecios')->name('api.v2.precios');
        Route::get('/metodos-pago', 'getMetodosPago')->name('api.v2.metodos-pago');
        Route::get('/banco/{nombrebanco}', 'getBanco')->name('api.v2.banco');
        Route::get('/tareas-hoy', 'getTareasHoy')->name('api.v2.tareas-hoy');
    });

    // ==========================================
    // RUTAS PARA EMPLEADOS - ROL CONTADOR
    // ==========================================
    Route::controller(ContadorController::class)->prefix('accountant')->group(function () {
        // Ventas y Finanzas
        Route::get('/ventas/resumen', 'resumenVentas')->name('api.accountant.ventas.resumen');
        Route::get('/ventas/facturas-pendientes', 'facturasPendientes')->name('api.accountant.ventas.facturas-pendientes');
        Route::get('/ventas/facturas-vencen-hoy', 'facturasVencenHoy')->name('api.accountant.ventas.facturas-vencen-hoy');
        Route::get('/ventas/facturas-vencidas-quitar', 'facturasVencidasParaQuitar')->name('api.accountant.ventas.facturas-vencidas-quitar');
        Route::get('/cuentas/evaluar-renovacion', 'evaluarCuentasRenovacion')->name('api.accountant.cuentas.evaluar-renovacion');
        Route::get('/cuentas/evaluar-mudanza', 'evaluarUsuariosParaMudanza')->name('api.accountant.cuentas.evaluar-mudanza');
        Route::get('/ventas/ingresos-por-servicio', 'ingresosPorServicio')->name('api.accountant.ventas.ingresos-por-servicio');
        Route::get('/ventas/proyeccion', 'proyeccionIngresos')->name('api.accountant.ventas.proyeccion');

        // Clientes y Análisis
        Route::get('/clientes/morosos', 'clientesMorosos')->name('api.accountant.clientes.morosos');
        Route::get('/clientes/estadisticas', 'estadisticasClientes')->name('api.accountant.clientes.estadisticas');

        // Reportes
        Route::get('/reportes/general', 'reporteGeneral')->name('api.accountant.reportes.general');
        Route::post('/reportes/exportar', 'exportarReporte')->name('api.accountant.reportes.exportar');

        // Métodos de Pago
        Route::get('/metodos-pago/estadisticas', 'estadisticasMetodosPago')->name('api.accountant.metodos-pago.estadisticas');
    });

    // ==========================================
    // RUTAS PARA AGENTE IA - TÉCNICO DE CUENTAS
    // ==========================================
    Route::controller(TecnicoCuentasController::class)->prefix('tech-accounts')->group(function () {
        // Resumen y Estado de Cuentas
        Route::get('/resumen', 'resumenGeneral')->name('api.tech-accounts.resumen');
        Route::get('/por-servicio', 'cuentasPorServicio')->name('api.tech-accounts.por-servicio');
        Route::get('/estado', 'cuentasPorEstado')->name('api.tech-accounts.estado');
        Route::get('/cuenta/{id}', 'detalleCuenta')->name('api.tech-accounts.detalle');
        Route::get('/clientes-vencen-hoy', 'clientesVencenHoy')->name('api.tech-accounts.clientes-vencen-hoy');

        // Estadísticas y Análisis
        Route::get('/estadisticas-uso', 'estadisticasUso')->name('api.tech-accounts.estadisticas-uso');
        Route::get('/mas-vendidos', 'serviciosMasVendidos')->name('api.tech-accounts.mas-vendidos');
        Route::get('/promedio-clientes', 'promedioClientesPorCuenta')->name('api.tech-accounts.promedio-clientes');

        // Análisis Financiero
        Route::get('/analisis-financiero', 'analisisFinanciero')->name('api.tech-accounts.analisis-financiero');
        Route::get('/costos-servicio', 'costosPorServicio')->name('api.tech-accounts.costos-servicio');

        // ⚡ ACCIONES AUTOMÁTICAS (POST)
        Route::prefix('acciones')->group(function () {
            Route::get('/usuarios-por-mover', 'usuariosPorMover')->name('api.tech-accounts.acciones.usuarios-por-mover');
            Route::post('/mover-servicio-a-mesa', 'moverServicioAMesa')->name('api.tech-accounts.acciones.mover-servicio-a-mesa');
            Route::post('/desactivar-usuarios', 'desactivarUsuariosMasivo')->name('api.tech-accounts.acciones.desactivar-usuarios');
            Route::post('/limpiar-cuentas', 'limpiarCuentas')->name('api.tech-accounts.acciones.limpiar-cuentas');
        });
    });

    // ==========================================
    // RUTAS PARA AGENTE IA - GESTIÓN DE USUARIOS
    // ==========================================
    Route::controller(TecnicoUsuariosController::class)->prefix('tech-usuarios')->group(function () {
        Route::get('/listar', 'listarUsuariosConFiltros')->name('api.tech-usuarios.listar');
        Route::post('/desactivar-vencidos', 'desactivarVencidos')->name('api.tech-usuarios.desactivar-vencidos');
        Route::get('/vencidos-hoy', 'vencidosHoy')->name('api.tech-usuarios.vencidos-hoy');
        Route::post('/cambiar-perfil', 'cambiarPerfil')->name('api.tech-usuarios.cambiar-perfil');
        Route::post('/mover-otro-servicio', 'moverClienteOtroServicio')->name('api.tech-usuarios.mover-otro-servicio');
        Route::get('/por-cliente/{idcli}', 'usuariosPorCliente')->name('api.tech-usuarios.por-cliente');
        Route::get('/obtener/{iddet}', 'obtener')->name('api.tech-usuarios.obtener');
        Route::get('/estadisticas', 'estadisticas')->name('api.tech-usuarios.estadisticas');
    });

    // ==========================================
    // RUTAS PARA AGENTE IA - GESTIÓN DE VENTAS
    // ==========================================
    Route::controller(TecnicoVentasController::class)->prefix('tech-ventas')->group(function () {
        Route::post('/crear', 'crear')->name('api.tech-ventas.crear');
        Route::put('/editar/{idven}', 'editar')->name('api.tech-ventas.editar');
        Route::get('/detalle/{idven}', 'detalle')->name('api.tech-ventas.detalle');
        Route::get('/listar', 'listar')->name('api.tech-ventas.listar');
        Route::get('/estadisticas', 'estadisticas')->name('api.tech-ventas.estadisticas');
    });

    // ==========================================
    // RUTAS PARA AGENTE IA - GESTIÓN DE PRODUCTOS
    // ==========================================
    Route::controller(TecnicoProductosController::class)->prefix('tech-productos')->group(function () {
        Route::post('/cambiar-estado', 'cambiarEstado')->name('api.tech-productos.cambiar-estado');
        Route::post('/cambiar-estado-masivo', 'cambiarEstadoMasivo')->name('api.tech-productos.cambiar-estado-masivo');
        Route::post('/cambiar-precio', 'cambiarPrecio')->name('api.tech-productos.cambiar-precio');
        Route::post('/actualizar-precios-base', 'actualizarPreciosBase')->name('api.tech-productos.actualizar-precios-base');
        Route::post('/crear', 'crear')->name('api.tech-productos.crear');
        Route::put('/editar/{idprod?}', 'editar')->name('api.tech-productos.editar');
        Route::post('/editar', 'editar')->name('api.tech-productos.editar-post');
        Route::get('/obtener/{id}', 'obtener')->name('api.tech-productos.obtener');
        Route::get('/listar', 'listar')->name('api.tech-productos.listar');
        Route::get('/servicios', 'listarServicios')->name('api.tech-productos.servicios');
    });

    // ==========================================
    // RUTAS PARA AGENTE IA - CONFIGURACIÓN (Valores, Servicios, Proveedores)
    // ==========================================
    Route::controller(TecnicoConfigController::class)->prefix('tech-config')->group(function () {
        // Valores
        Route::prefix('valores')->group(function () {
            Route::post('/pantallas', 'definirPantallas')->name('api.tech-config.valores.pantallas');
            Route::post('/crear', 'crearValor')->name('api.tech-config.valores.crear');
            Route::put('/editar/{idval}', 'editarValor')->name('api.tech-config.valores.editar');
            Route::get('/obtener/{idval}', 'obtenerValor')->name('api.tech-config.valores.obtener');
            Route::get('/listar', 'listarValores')->name('api.tech-config.valores.listar');
        });

        // Servicios
        Route::prefix('servicios')->group(function () {
            Route::post('/crear', 'crearServicio')->name('api.tech-config.servicios.crear');
            Route::put('/editar/{idser}', 'editarServicio')->name('api.tech-config.servicios.editar');
            Route::get('/obtener/{idser}', 'obtenerServicio')->name('api.tech-config.servicios.obtener');
            Route::get('/listar', 'listarServicios')->name('api.tech-config.servicios.listar');
        });

        // Proveedores
        Route::prefix('proveedores')->group(function () {
            Route::post('/crear', 'crearProveedor')->name('api.tech-config.proveedores.crear');
            Route::put('/editar/{idpro}', 'editarProveedor')->name('api.tech-config.proveedores.editar');
            Route::get('/obtener/{idpro}', 'obtenerProveedor')->name('api.tech-config.proveedores.obtener');
            Route::get('/listar', 'listarProveedores')->name('api.tech-config.proveedores.listar');
        });
    });

    // Chat AI - Para empleados (proxy a n8n) - Necesita middleware web para sesiones
    Route::middleware('web')->post('/chat/ai/send', [\App\Http\Controllers\Api\ChatAIController::class, 'sendMessage'])->name('api.v2.chat.ai.send');
});

//Route::middleware('auth:api')->post('ventas', [VentaController::class, 'storeApi']);

