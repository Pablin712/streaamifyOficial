<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente;

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

    public function crearCliente(Request $request)
    {
        // Validar datos de entrada con Validator manual
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clientes,email',
            'password' => 'required|string|min:6',
            'confirm' => 'required|string|min:6',
            'telefono' => 'required|string|max:50',
            'codigo_referidor' => 'nullable|string|max:50|exists:clientes,codigo_referidor',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'confirm.required' => 'La confirmación de contraseña es obligatoria.',
            'confirm.min' => 'La confirmación debe tener al menos 6 caracteres.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'codigo_referidor.exists' => 'El código de referido no existe.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 200);
        }

        // Verificar que las contraseñas coincidan
        if ($request->password !== $request->confirm) {
            return response()->json([
                'success' => false,
                'message' => 'Las contraseñas no coinciden.'
            ], 200);
        }

        // Normalizar teléfono (quitar espacios, guiones, paréntesis)
        $telefonoNormalizado = preg_replace('/[\s\-\(\)]/', '', $request->telefono);

        // Formatear teléfono para guardar: +593 96 177 8319
        $telefonoFormateado = $this->formatearTelefono($telefonoNormalizado);

        // Buscar cliente por teléfono (comparando versiones normalizadas)
        $clienteExistente = Cliente::all()->first(function ($cliente) use ($telefonoNormalizado) {
            $telefonoBD = preg_replace('/[\s\-\(\)]/', '', $cliente->telefonocli);
            return $telefonoBD === $telefonoNormalizado;
        });

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
                'nombrecli' => ucwords($request->name),
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

        // Crear nuevo cliente
        $cliente = Cliente::create([
            'nombrecli' => ucwords($request->name),
            'email' => $request->email,
            'password' => $request->password,
            'telefonocli' => $telefonoFormateado,
            'saldo' => 0,
            'referido_por' => $referidoPor,
        ]);

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

    /**
     * Formatear teléfono al formato de BD: +593 96 177 8319
     */
    private function formatearTelefono($telefono)
    {
        // Si ya tiene espacios, retornar tal cual
        if (strpos($telefono, ' ') !== false) {
            return $telefono;
        }

        // Formato esperado: +593961778319
        // Resultado: +593 96 177 8319
        if (preg_match('/^(\+\d{1,4})(\d{2})(\d{3})(\d{4})$/', $telefono, $matches)) {
            return $matches[1] . ' ' . $matches[2] . ' ' . $matches[3] . ' ' . $matches[4];
        }

        // Si no coincide con el patrón, retornar sin cambios
        return $telefono;
    }
}
