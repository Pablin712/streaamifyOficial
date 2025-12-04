<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
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

        // Si la API Key tiene empleado asociado, autenticarlo
        if ($apiKey->empleado_id) {
            auth()->guard('empleado')->loginUsingId($apiKey->empleado_id);
        }        // Registrar petición exitosa
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
