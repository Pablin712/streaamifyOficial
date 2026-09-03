<?php

namespace App\Http\Controllers;

use App\Services\AparienciaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SistemaController extends Controller
{
    public function __construct(private AparienciaService $apariencia)
    {
    }

    /**
     * Mostrar la vista de configuración del sistema
     * Solo accesible para administradores
     */
    public function index()
    {
        $this->soloAdmin();

        return view('settings.sistema.index');
    }

    /**
     * Guardar la apariencia GLOBAL de la plataforma.
     *
     * Lo que se guarda aqui manda para todos: cualquier empleado, cualquier
     * dispositivo y tambien las vistas publicas de clientes. Antes esto vivia
     * en el localStorage de cada navegador y por eso no se propagaba.
     */
    public function guardarApariencia(Request $request): JsonResponse
    {
        $this->soloAdmin();

        $datos = $request->validate([
            'tema'           => ['sometimes', 'string', 'max:40'],
            'auto_temporada' => ['sometimes', 'boolean'],
        ]);

        if (isset($datos['tema']) && !AparienciaService::temaValido($datos['tema'])) {
            return response()->json([
                'success' => false,
                'message' => 'El tema seleccionado no existe.',
            ], 422);
        }

        /** @var \App\Models\Empleado $user */
        $user = Auth::user();

        $apariencia = $this->apariencia->guardar($datos, $user->nombreemp ?? $user->name ?? null);

        return response()->json([
            'success'    => true,
            'message'    => 'Apariencia actualizada para toda la plataforma.',
            'apariencia' => $apariencia,
        ]);
    }

    /**
     * Guardar la preferencia PERSONAL de modo claro/oscuro.
     *
     * A diferencia del tema, esto no es global: solo afecta a quien lo pide.
     * Cualquier empleado puede hacerlo, no hace falta ser administrador. Se
     * guarda en el servidor para que la preferencia le siga entre dispositivos.
     */
    public function guardarEsquema(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'esquema' => ['required', 'string', 'in:system,light,dark'],
        ]);

        if (!Auth::check()) {
            // Un visitante anonimo no tiene donde guardarlo en el servidor;
            // su navegador lo recuerda por su cuenta.
            return response()->json([
                'success' => true,
                'esquema' => $datos['esquema'],
                'guardado' => false,
            ]);
        }

        $esquema = $this->apariencia->guardarEsquema($datos['esquema']);

        return response()->json([
            'success'  => true,
            'esquema'  => $esquema,
            'guardado' => true,
        ]);
    }

    /**
     * Apariencia vigente. La consultan las pestañas abiertas para reflejar un
     * cambio hecho desde otro dispositivo sin tener que recargar a mano.
     * Es de solo lectura, asi que no exige rol de administrador.
     */
    public function apariencia(): JsonResponse
    {
        return response()->json([
            'success'    => true,
            'apariencia' => $this->apariencia->paraVista(),
        ]);
    }

    private function soloAdmin(): void
    {
        /** @var \App\Models\Empleado|null $user */
        $user = Auth::user();

        if (!$user || !$user->hasRole('Admin')) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }
    }
}
