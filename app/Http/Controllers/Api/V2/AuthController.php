<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Support\ClienteAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function __construct()
    {
        // Forzar que todas las respuestas sean JSON
        request()->headers->set('Accept', 'application/json');
    }

    public function validarCredenciales(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string'
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 200);
        }

        $cliente = Cliente::where('email', $request->email)->first();

        if (!$cliente) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'Usuario no encontrado'
            ], 200); // Agregué el status code 200 para consistencia
        }

        // Verificar la contraseña
        $valid = \Illuminate\Support\Facades\Hash::check($request->password, $cliente->password);

        // ❌ PROBLEMA: Si la contraseña es incorrecta, NO deberías retornar success: true
        if (!$valid) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'Contraseña incorrecta'
            ], 200);
        }

        // ✅ Solo llega aquí si la contraseña es correcta
        return response()->json([
            'success' => true,
            'valid' => true,
            'message' => 'Credenciales válidas',
            'cliente' => [
                'id' => $cliente->idcli,
                'nombre' => $cliente->nombrecli,
                'email' => $cliente->email,
                'telefono' => $cliente->telefonocli,
            ]
        ], 200);
    }

    public function validarEmpleado(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'usuario' => 'required|email',
            'password' => 'required|string'
        ], [
            'usuario.required' => 'El correo electrónico es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 200);
        }

        $empleado = Empleado::where('usuarioemp', $request->usuario)->firstOrFail();
        $valid = \Illuminate\Support\Facades\Hash::check($request->password, $empleado->passwordemp);

        if (!$valid) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'Contraseña incorrecta'
            ], 200);
        }

        return response()->json([
            'success' => true,
            'valid' => true,
            'message' => 'Credenciales de empleado válidas',
            // Puedes incluir información adicional del empleado si es necesario
        ], 200);
    }

    public function crearCliente(Request $request)
    {
        $payload = array_merge($request->all(), [
            'password_confirmation' => $request->input('confirm'),
        ]);

        // Validar datos de entrada con Validator manual
        $validator = \Illuminate\Support\Facades\Validator::make($payload, [
            'name' => 'required|string|max:' . ClienteAuth::MAX_FULL_NAME_LENGTH,
            'email' => 'required|email|unique:clientes,email',
            'password' => ClienteAuth::passwordRules(),
            'telefono' => 'required|string|max:' . ClienteAuth::MAX_PHONE_LENGTH,
            'codigo_referidor' => 'nullable|string|max:50|exists:clientes,codigo_referidor',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'codigo_referidor.exists' => 'El código de referido no existe.',
        ] + ClienteAuth::passwordMessages());

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 200);
        }

        $nombre = ClienteAuth::buildFullName(fullName: $request->name);
        $telefonoFormateado = ClienteAuth::normalizePhone($request->telefono);
        $clienteExistente = Cliente::buscarPorTelefonoNormalizado($telefonoFormateado);

        if ($clienteExistente) {
            // Si el cliente ya tiene email registrado, no puede registrarse de nuevo
            if ($clienteExistente->email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este número de teléfono ya está registrado con una cuenta activa.'
                ], 200);
            }

            // Si existe el teléfono pero SIN email, actualizar (primera vez registrándose)
            $referidoPor = null;
            if ($request->filled('codigo_referidor')) {
                $referidor = Cliente::where('codigo_referidor', $request->codigo_referidor)->first();
                if ($referidor) {
                    $referidoPor = $referidor->idcli;
                }
            }

            $clienteExistente->update([
                'nombrecli' => $nombre,
                'email' => $request->email,
                'password' => $request->password,
                'referido_por' => $referidoPor,
            ]);

            return response()->json([
                'success' => true,
                'message' => '🎉 ¡FELICIDADES! Su cuenta ha sido completada exitosamente. Ahora puedes vincular tu chat de telegram al iniciar sesión. Presiona 1 para ir a login.',
                'cliente' => [
                    'id' => $clienteExistente->idcli,
                    'nombre' => $clienteExistente->nombrecli,
                    'email' => $clienteExistente->email,
                    'telefono' => $clienteExistente->telefonocli,
                    'codigo_referidor' => $clienteExistente->codigo_referidor,
                ]
            ]);
        }

        // Buscar referidor si se proporcionó código
        $referidoPor = null;
        if ($request->filled('codigo_referidor')) {
            $referidor = Cliente::where('codigo_referidor', $request->codigo_referidor)->first();
            if ($referidor) {
                $referidoPor = $referidor->idcli;
            }
        }

        try {
            $cliente = Cliente::create([
                'nombrecli' => $nombre,
                'email' => $request->email,
                'password' => $request->password,
                'telefonocli' => $telefonoFormateado,
                'saldo' => 0,
                'referido_por' => $referidoPor,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error creando cliente desde API v2 auth', [
                'email' => $request->email,
                'telefono' => $request->telefono,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo completar el registro en este momento.'
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => '🎉 ¡FELICIDADES! Su cuenta ha sido completada exitosamente. Ahora puedes vincular tu chat de telegram al iniciar sesión. Presiona 1 para ir a login.',
            'cliente' => [
                'id' => $cliente->idcli,
                'nombre' => $cliente->nombrecli,
                'email' => $cliente->email,
                'telefono' => $cliente->telefonocli,
                'codigo_referidor' => $cliente->codigo_referidor,
            ]
        ]);
    }

}
