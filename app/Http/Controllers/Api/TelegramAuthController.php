<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TelegramAuthService;
use App\Support\ClienteAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Controlador API para gestionar autenticación de Telegram
 *
 * Endpoints para que N8N pueda interactuar con el sistema de autenticación
 */
class TelegramAuthController extends Controller
{
    protected $authService;

    public function __construct(TelegramAuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Verificar si un cliente ya está registrado
     *
     * POST /api/telegram/check-registered
     * Body: { "chat_id": 123456789 }
     */
    public function checkRegistered(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chat_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Datos inválidos',
                'errores' => $validator->errors(),
            ], 400);
        }

        $cliente = $this->authService->clienteEstaRegistrado($request->chat_id);

        return response()->json([
            'exito' => true,
            'registrado' => !is_null($cliente),
            'cliente' => $cliente ? [
                'id' => $cliente->idcli,
                'nombre' => $cliente->nombrecli,
                'email' => $cliente->email,
            ] : null,
        ]);
    }

    /**
     * Obtener estado de sesión actual
     *
     * POST /api/telegram/get-session
     * Body: { "chat_id": 123456789 }
     */
    public function getSession(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chat_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Datos inválidos',
                'errores' => $validator->errors(),
            ], 400);
        }

        $session = $this->authService->obtenerSesion($request->chat_id);

        return response()->json([
            'exito' => true,
            'sesion' => [
                'chat_id' => $session->chat_id,
                'step' => $session->step,
                'proceso' => $session->proceso,
                'datos' => $session->datos,
                'intentos' => $session->intentos,
                'expirada' => $session->estaExpirada(),
            ],
        ]);
    }

    /**
     * Procesar entrada del usuario
     *
     * POST /api/telegram/process-input
     * Body: {
     *   "chat_id": 123456789,
     *   "message": "texto del usuario"
     * }
     */
    public function processInput(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chat_id' => 'required|integer',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Datos inválidos',
                'errores' => $validator->errors(),
            ], 400);
        }

        // Obtener sesión actual
        $session = $this->authService->obtenerSesion($request->chat_id);

        // Procesar paso actual
        $resultado = $this->authService->procesarPaso(
            $request->chat_id,
            $session->step,
            $request->message
        );

        return response()->json($resultado);
    }

    /**
     * Validar credenciales
     *
     * POST /api/telegram/validate-credentials
     * Body: {
     *   "email": "user@example.com",
     *   "password": "password123"
     * }
     */
    public function validateCredentials(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Datos inválidos',
                'errores' => $validator->errors(),
            ], 400);
        }

        $resultado = $this->authService->validarCredenciales(
            $request->email,
            $request->password
        );

        return response()->json([
            'exito' => $resultado['valido'],
            'mensaje' => $resultado['mensaje'],
            'cliente' => $resultado['cliente'] ? [
                'id' => $resultado['cliente']->idcli,
                'nombre' => $resultado['cliente']->nombrecli,
                'email' => $resultado['cliente']->email,
            ] : null,
        ]);
    }

    /**
     * Verificar si email existe
     *
     * POST /api/telegram/check-email
     * Body: { "email": "user@example.com" }
     */
    public function checkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Datos inválidos',
                'errores' => $validator->errors(),
            ], 400);
        }

        $existe = $this->authService->emailExiste($request->email);

        return response()->json([
            'exito' => true,
            'existe' => $existe,
        ]);
    }

    /**
     * Crear nuevo cliente
     *
     * POST /api/telegram/create-cliente
     * Body: {
     *   "chat_id": 123456789,
     *   "nombre": "Juan Pérez",
     *   "email": "juan@example.com",
     *   "telefono": "0987654321",
     *   "password": "password123"
     * }
     */
    public function createCliente(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chat_id' => 'required|integer',
            'nombre' => 'required|string|max:' . ClienteAuth::MAX_FULL_NAME_LENGTH,
            'email' => 'required|email',
            'telefono' => 'required|string|max:' . ClienteAuth::MAX_PHONE_LENGTH,
            'password' => ClienteAuth::passwordRules(false),
        ], ClienteAuth::passwordMessages());

        if ($validator->fails()) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Datos inválidos',
                'errores' => $validator->errors(),
            ], 400);
        }

        $resultado = $this->authService->crearCliente([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'password' => $request->password,
        ], $request->chat_id);

        return response()->json($resultado);
    }

    /**
     * Vincular telegram a cliente existente
     *
     * POST /api/telegram/link-telegram
     * Body: {
     *   "cliente_id": 123,
     *   "chat_id": 123456789
     * }
     */
    public function linkTelegram(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cliente_id' => 'required|integer',
            'chat_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Datos inválidos',
                'errores' => $validator->errors(),
            ], 400);
        }

        $exito = $this->authService->vincularTelegramACliente(
            $request->cliente_id,
            $request->chat_id
        );

        return response()->json([
            'exito' => $exito,
            'mensaje' => $exito ? 'Telegram vinculado exitosamente' : 'Error al vincular Telegram',
        ]);
    }

    /**
     * Reiniciar sesión
     *
     * POST /api/telegram/reset-session
     * Body: { "chat_id": 123456789 }
     */
    public function resetSession(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chat_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Datos inválidos',
                'errores' => $validator->errors(),
            ], 400);
        }

        $session = $this->authService->obtenerSesion($request->chat_id);
        $session->reiniciar();

        return response()->json([
            'exito' => true,
            'mensaje' => 'Sesión reiniciada',
        ]);
    }

    /**
     * Eliminar sesión
     *
     * DELETE /api/telegram/delete-session
     * Body: { "chat_id": 123456789 }
     */
    public function deleteSession(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chat_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Datos inválidos',
                'errores' => $validator->errors(),
            ], 400);
        }

        $session = $this->authService->obtenerSesion($request->chat_id);
        $session->delete();

        return response()->json([
            'exito' => true,
            'mensaje' => 'Sesión eliminada',
        ]);
    }

    /**
     * Limpiar sesiones expiradas
     *
     * POST /api/telegram/clean-sessions
     */
    public function cleanSessions()
    {
        $eliminadas = $this->authService->limpiarSesionesExpiradas();

        return response()->json([
            'exito' => true,
            'mensaje' => "Se eliminaron {$eliminadas} sesiones expiradas",
            'eliminadas' => $eliminadas,
        ]);
    }
}
