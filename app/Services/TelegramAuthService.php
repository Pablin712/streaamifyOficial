<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\TelegramAuthSession;
use App\Support\ClienteAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Servicio para gestionar la autenticación de usuarios a través de Telegram
 *
 * Este servicio maneja todo el flujo de login y registro de clientes
 * que interactúan con el bot de Telegram
 */
class TelegramAuthService
{
    /**
     * Obtener o crear sesión de autenticación
     */
    public function obtenerSesion(int $chatId): TelegramAuthSession
    {
        $session = TelegramAuthSession::obtenerOCrear($chatId);

        // Si la sesión está expirada, reiniciarla
        if ($session->estaExpirada()) {
            $session->reiniciar();
        }

        return $session;
    }

    /**
     * Verificar si un cliente ya está registrado con Telegram
     */
    public function clienteEstaRegistrado(int $chatId): ?Cliente
    {
        return Cliente::buscarPorTelegram($chatId);
    }

    /**
     * Validar credenciales de login
     */
    public function validarCredenciales(string $email, string $password): array
    {
        $cliente = Cliente::where('email', $email)->first();

        if (!$cliente) {
            return [
                'valido' => false,
                'mensaje' => 'Email no encontrado',
                'cliente' => null,
            ];
        }

        if (!Hash::check($password, $cliente->password)) {
            return [
                'valido' => false,
                'mensaje' => 'Contraseña incorrecta',
                'cliente' => null,
            ];
        }

        return [
            'valido' => true,
            'mensaje' => 'Credenciales válidas',
            'cliente' => $cliente,
        ];
    }

    /**
     * Verificar si un email ya existe
     */
    public function emailExiste(string $email): bool
    {
        return Cliente::where('email', $email)->exists();
    }

    /**
     * Validar formato de email
     */
    public function validarEmail(string $email): array
    {
        $validator = Validator::make(
            ['email' => $email],
            ['email' => 'required|email']
        );

        return [
            'valido' => !$validator->fails(),
            'mensaje' => $validator->fails() ? 'Formato de email inválido' : 'Email válido',
        ];
    }

    /**
     * Validar contraseña
     */
    public function validarPassword(string $password): array
    {
        $validator = Validator::make(
            ['password' => $password],
            ['password' => ClienteAuth::passwordRules(false)],
            ClienteAuth::passwordMessages()
        );

        return [
            'valido' => !$validator->fails(),
            'mensaje' => $validator->fails() ? $validator->errors()->first('password') : 'Contraseña válida',
        ];
    }

    /**
     * Validar teléfono
     */
    public function validarTelefono(string $telefono): array
    {
        // Validación básica de teléfono
        $valido = preg_match('/^[0-9+\-\s()]{7,20}$/', $telefono);

        return [
            'valido' => $valido,
            'mensaje' => $valido ? 'Teléfono válido' : 'Formato de teléfono inválido',
        ];
    }

    /**
     * Crear nuevo cliente y vincular con Telegram
     */
    public function crearCliente(array $datos, int $chatId): array
    {
        try {
            $nombre = ClienteAuth::buildFullName(fullName: $datos['nombre'] ?? null);
            $telefono = ClienteAuth::normalizePhone($datos['telefono'] ?? null);

            // Validar datos
            $validator = Validator::make($datos, [
                'nombre' => 'required|string|max:' . ClienteAuth::MAX_FULL_NAME_LENGTH,
                'email' => 'required|email|unique:clientes,email',
                'telefono' => 'required|string|max:' . ClienteAuth::MAX_PHONE_LENGTH,
                'password' => ClienteAuth::passwordRules(false),
            ], ClienteAuth::passwordMessages());

            $validator->after(function ($validator) use ($nombre) {
                if (mb_strlen($nombre) > ClienteAuth::MAX_FULL_NAME_LENGTH) {
                    $validator->errors()->add('nombre', ClienteAuth::fullNameTooLongMessage());
                }
            });

            if ($validator->fails()) {
                return [
                    'exito' => false,
                    'mensaje' => 'Datos inválidos: ' . $validator->errors()->first(),
                    'cliente' => null,
                ];
            }

            // Crear cliente
            $cliente = Cliente::create([
                'nombrecli' => $nombre,
                'email' => $datos['email'],
                'telefonocli' => $telefono,
                'password' => $datos['password'], // Se encripta automáticamente en el modelo
                'telegram_chat_id' => $chatId,
                'pais' => ClienteAuth::normalizeName($datos['pais'] ?? 'Ecuador'),
                'saldo' => 0.00,
            ]);

            return [
                'exito' => true,
                'mensaje' => 'Cliente creado exitosamente',
                'cliente' => $cliente,
            ];
        } catch (\Exception $e) {
            return [
                'exito' => false,
                'mensaje' => 'Error al crear cliente: ' . $e->getMessage(),
                'cliente' => null,
            ];
        }
    }

    /**
     * Vincular telegram_chat_id a un cliente existente
     */
    public function vincularTelegramACliente(int $clienteId, int $chatId): bool
    {
        try {
            $cliente = Cliente::find($clienteId);

            if (!$cliente) {
                return false;
            }

            return $cliente->vincularTelegram($chatId);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Procesar paso del flujo de autenticación
     */
    public function procesarPaso(int $chatId, string $paso, string $entrada): array
    {
        $session = $this->obtenerSesion($chatId);

        switch ($paso) {
            case 'inicio':
                return $this->procesarInicio($session, $entrada);

            case 'login_email':
                return $this->procesarLoginEmail($session, $entrada);

            case 'login_password':
                return $this->procesarLoginPassword($session, $entrada);

            case 'registro_nombre':
                return $this->procesarRegistroNombre($session, $entrada);

            case 'registro_email':
                return $this->procesarRegistroEmail($session, $entrada);

            case 'registro_email_existe':
                return $this->procesarRegistroEmailExiste($session, $entrada);

            case 'registro_telefono':
                return $this->procesarRegistroTelefono($session, $entrada);

            case 'registro_password':
                return $this->procesarRegistroPassword($session, $entrada);

            case 'registro_confirmar':
                return $this->procesarRegistroConfirmar($session, $entrada);

            default:
                return [
                    'exito' => false,
                    'mensaje' => 'Paso no reconocido',
                    'paso_siguiente' => 'inicio',
                ];
        }
    }

    // Métodos privados para procesar cada paso
    private function procesarInicio(TelegramAuthSession $session, string $entrada): array
    {
        $entrada = strtoupper(trim($entrada));

        if (in_array($entrada, ['SI', 'SÍ', 'S', 'YES', 'Y'])) {
            $session->actualizarEstado('login_email', 'login');
            return [
                'exito' => true,
                'mensaje' => '📧 Perfecto. Por favor ingresa tu email de registro:',
                'paso_siguiente' => 'login_email',
            ];
        }

        if (in_array($entrada, ['NO', 'N'])) {
            $session->actualizarEstado('registro_nombre', 'registro');
            return [
                'exito' => true,
                'mensaje' => '📝 ¡Perfecto! Vamos a crear tu cuenta. ¿Cuál es tu nombre completo?',
                'paso_siguiente' => 'registro_nombre',
            ];
        }

        return [
            'exito' => false,
            'mensaje' => 'Por favor responde SI o NO',
            'paso_siguiente' => 'inicio',
        ];
    }

    private function procesarLoginEmail(TelegramAuthSession $session, string $entrada): array
    {
        $validacion = $this->validarEmail($entrada);

        if (!$validacion['valido']) {
            return [
                'exito' => false,
                'mensaje' => '❌ ' . $validacion['mensaje'] . '. Por favor ingresa un email válido:',
                'paso_siguiente' => 'login_email',
            ];
        }

        $session->actualizarDatos(['email' => $entrada]);
        $session->actualizarEstado('login_password', 'login', $session->datos);

        return [
            'exito' => true,
            'mensaje' => '🔐 Ahora ingresa tu contraseña:',
            'paso_siguiente' => 'login_password',
        ];
    }

    private function procesarLoginPassword(TelegramAuthSession $session, string $entrada): array
    {
        $email = $session->datos['email'] ?? null;

        if (!$email) {
            $session->actualizarEstado('login_email', 'login');
            return [
                'exito' => false,
                'mensaje' => '❌ Error en la sesión. Por favor ingresa tu email nuevamente:',
                'paso_siguiente' => 'login_email',
            ];
        }

        $resultado = $this->validarCredenciales($email, $entrada);

        if ($resultado['valido']) {
            // Vincular telegram
            $this->vincularTelegramACliente($resultado['cliente']->idcli, $session->chat_id);

            // Eliminar sesión
            $session->delete();

            return [
                'exito' => true,
                'mensaje' => "✅ ¡Perfecto! Tu cuenta ha sido vinculada exitosamente.\n\nBienvenido/a {$resultado['cliente']->nombrecli} 🎉",
                'paso_siguiente' => 'completado',
                'auth_complete' => true,
                'cliente' => $resultado['cliente'],
            ];
        }

        // Incrementar intentos
        $session->incrementarIntentos();

        if ($session->intentos >= 3) {
            $session->reiniciar();
            return [
                'exito' => false,
                'mensaje' => '❌ Demasiados intentos fallidos. Por favor comienza de nuevo escribiendo "hola".',
                'paso_siguiente' => 'inicio',
            ];
        }

        return [
            'exito' => false,
            'mensaje' => "❌ {$resultado['mensaje']}. Intento {$session->intentos}/3.\n\nPor favor ingresa tu contraseña:",
            'paso_siguiente' => 'login_password',
        ];
    }

    private function procesarRegistroNombre(TelegramAuthSession $session, string $entrada): array
    {
        $nombre = ClienteAuth::buildFullName(fullName: $entrada);

        if (mb_strlen($nombre) < 3) {
            return [
                'exito' => false,
                'mensaje' => '❌ El nombre debe tener al menos 3 caracteres. Por favor ingresa tu nombre completo:',
                'paso_siguiente' => 'registro_nombre',
            ];
        }

        if (mb_strlen($nombre) > ClienteAuth::MAX_FULL_NAME_LENGTH) {
            return [
                'exito' => false,
                'mensaje' => '❌ ' . ClienteAuth::fullNameTooLongMessage() . ' Por favor ingresa un nombre más corto:',
                'paso_siguiente' => 'registro_nombre',
            ];
        }

        $session->actualizarDatos(['nombre' => $nombre]);
        $session->actualizarEstado('registro_email', 'registro', $session->datos);

        return [
            'exito' => true,
            'mensaje' => '📧 Gracias. Ahora, ¿cuál es tu email?',
            'paso_siguiente' => 'registro_email',
        ];
    }

    private function procesarRegistroEmail(TelegramAuthSession $session, string $entrada): array
    {
        $validacion = $this->validarEmail($entrada);

        if (!$validacion['valido']) {
            return [
                'exito' => false,
                'mensaje' => '❌ ' . $validacion['mensaje'] . '. Por favor ingresa un email válido:',
                'paso_siguiente' => 'registro_email',
            ];
        }

        if ($this->emailExiste($entrada)) {
            $session->actualizarDatos(['email' => $entrada]);

            return [
                'exito' => false,
                'mensaje' => "❌ Este email ya está registrado. ¿Quieres hacer login en su lugar?\n\nResponde SI para iniciar sesión o NO para usar otro email.",
                'paso_siguiente' => 'registro_email_existe',
            ];
        }

        $session->actualizarDatos(['email' => $entrada]);
        $session->actualizarEstado('registro_telefono', 'registro', $session->datos);

        return [
            'exito' => true,
            'mensaje' => '📱 ¿Cuál es tu número de teléfono?',
            'paso_siguiente' => 'registro_telefono',
        ];
    }

    private function procesarRegistroEmailExiste(TelegramAuthSession $session, string $entrada): array
    {
        $entrada = strtoupper(trim($entrada));

        if (in_array($entrada, ['SI', 'SÍ', 'S', 'YES', 'Y'])) {
            $session->actualizarEstado('login_password', 'login', $session->datos);

            return [
                'exito' => true,
                'mensaje' => '🔐 Perfecto. Ingresa tu contraseña para iniciar sesión:',
                'paso_siguiente' => 'login_password',
            ];
        }

        if (in_array($entrada, ['NO', 'N'])) {
            $datos = $session->datos ?? [];
            unset($datos['email']);
            $session->actualizarEstado('registro_email', 'registro', $datos);

            return [
                'exito' => true,
                'mensaje' => '📧 De acuerdo. Ingresa otro email para continuar con el registro:',
                'paso_siguiente' => 'registro_email',
            ];
        }

        return [
            'exito' => false,
            'mensaje' => 'Por favor responde SI o NO',
            'paso_siguiente' => 'registro_email_existe',
        ];
    }

    private function procesarRegistroTelefono(TelegramAuthSession $session, string $entrada): array
    {
        $validacion = $this->validarTelefono($entrada);

        if (!$validacion['valido']) {
            return [
                'exito' => false,
                'mensaje' => '❌ ' . $validacion['mensaje'] . '. Por favor ingresa un teléfono válido:',
                'paso_siguiente' => 'registro_telefono',
            ];
        }

        $session->actualizarDatos(['telefono' => ClienteAuth::normalizePhone($entrada)]);
        $session->actualizarEstado('registro_password', 'registro', $session->datos);

        return [
            'exito' => true,
            'mensaje' => '🔐 Perfecto. Ahora crea una contraseña (mínimo 6 caracteres):',
            'paso_siguiente' => 'registro_password',
        ];
    }

    private function procesarRegistroPassword(TelegramAuthSession $session, string $entrada): array
    {
        $validacion = $this->validarPassword($entrada);

        if (!$validacion['valido']) {
            return [
                'exito' => false,
                'mensaje' => '❌ ' . $validacion['mensaje'] . '. Por favor ingresa una contraseña válida:',
                'paso_siguiente' => 'registro_password',
            ];
        }

        $session->actualizarDatos(['password' => $entrada]);
        $session->actualizarEstado('registro_confirmar', 'registro', $session->datos);

        $datos = $session->datos;
        $mensaje = "📋 Por favor confirma tus datos:\n\n";
        $mensaje .= "👤 Nombre: {$datos['nombre']}\n";
        $mensaje .= "📧 Email: {$datos['email']}\n";
        $mensaje .= "📱 Teléfono: {$datos['telefono']}\n";
        $mensaje .= "🔐 Contraseña: *** (oculta)\n\n";
        $mensaje .= "¿Todo está correcto? Responde SI o NO";

        return [
            'exito' => true,
            'mensaje' => $mensaje,
            'paso_siguiente' => 'registro_confirmar',
        ];
    }

    private function procesarRegistroConfirmar(TelegramAuthSession $session, string $entrada): array
    {
        $entrada = strtoupper(trim($entrada));

        if (in_array($entrada, ['SI', 'SÍ', 'S', 'YES', 'Y'])) {
            $resultado = $this->crearCliente($session->datos, $session->chat_id);

            if ($resultado['exito']) {
                $session->delete();

                return [
                    'exito' => true,
                    'mensaje' => "🎉 ¡Cuenta creada y vinculada exitosamente!\n\nBienvenido/a {$resultado['cliente']->nombrecli} 🎉",
                    'paso_siguiente' => 'completado',
                    'auth_complete' => true,
                    'cliente' => $resultado['cliente'],
                ];
            }

            return [
                'exito' => false,
                'mensaje' => '❌ ' . $resultado['mensaje'] . '\n\nPor favor comienza de nuevo escribiendo "hola".',
                'paso_siguiente' => 'inicio',
            ];
        }

        if (in_array($entrada, ['NO', 'N'])) {
            $session->reiniciar();
            $session->actualizarEstado('registro_nombre', 'registro');

            return [
                'exito' => true,
                'mensaje' => '📝 Entendido. Comencemos de nuevo. ¿Cuál es tu nombre completo?',
                'paso_siguiente' => 'registro_nombre',
            ];
        }

        return [
            'exito' => false,
            'mensaje' => 'Por favor responde SI o NO',
            'paso_siguiente' => 'registro_confirmar',
        ];
    }

    /**
     * Limpiar sesiones expiradas (para ejecutar periódicamente)
     */
    public function limpiarSesionesExpiradas(): int
    {
        return TelegramAuthSession::limpiarExpiradas();
    }
}
