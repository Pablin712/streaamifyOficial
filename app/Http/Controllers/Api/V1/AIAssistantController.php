<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Perfil;
use App\Models\Cuenta;
use App\Models\Valor;
use App\Models\Servicio;
use App\Models\Producto;
use App\Models\TipoProducto;
use App\Models\Cliente;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controlador para asistente IA (n8n + DeepSeek)
 * Endpoints específicos para que el modelo de IA consulte datos
 */
class AIAssistantController extends Controller
{
    /**
     * Obtener perfiles disponibles de un servicio específico
     * GET /api/v1/ai/perfiles-disponibles
     *
     * Query params:
     * - servicio: SPOTIFY, NETFLIX, etc. (requerido)
     * - limit: cantidad máxima de resultados (default: 10)
     */
    public function perfilesDisponibles(Request $request)
    {
        try {
            $request->validate([
                'servicio' => 'required|string',
                'limit' => 'nullable|integer|min:1|max:100'
            ]);

            $servicio = strtoupper($request->servicio);
            $limit = $request->input('limit', 10);

            // Buscar perfiles disponibles:
            // 1. La cuenta debe pertenecer al servicio especificado
            // 2. El perfil no debe tener detalles de venta activos
            $perfilesDisponibles = Perfil::select('perfiles.*')
                ->join('cuentas', 'perfiles.idcue', '=', 'cuentas.idcue')
                ->join('valores', 'cuentas.idval', '=', 'valores.idval')
                ->join('servicios', 'valores.idser', '=', 'servicios.idser')
                ->where('servicios.nombreser', $servicio)
                ->whereRaw('(SELECT COUNT(*) FROM detalles_venta WHERE detalles_venta.idper = perfiles.idper AND detalles_venta.activodet = 1 AND detalles_venta.fechavendet >= CURDATE()) = 0')
                ->with(['cuenta.valor.servicio'])
                ->limit($limit)
                ->get();

            $perfiles = $perfilesDisponibles->map(function($perfil) {
                return [
                    'idper' => $perfil->idper,
                    'numero_perfil' => $perfil->numeroper,
                    'pin' => $perfil->pinper,
                    'cuenta' => [
                        'correo' => $perfil->cuenta->correocue ?? null,
                        'servicio' => $perfil->cuenta->valor->servicio->nombreser ?? null,
                        'plan' => $perfil->cuenta->valor->nombreval ?? null,
                    ],
                    'usuarios_activos' => 0,
                    'disponible' => true
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $perfiles,
                'count' => $perfiles->count(),
                'servicio' => $servicio,
                'message' => $perfiles->count() > 0
                    ? "Se encontraron {$perfiles->count()} perfiles disponibles de {$servicio}"
                    : "No hay perfiles disponibles de {$servicio}"
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al consultar perfiles',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todos los servicios disponibles
     * GET /api/v1/ai/servicios
     */
    public function serviciosDisponibles()
    {
        try {
            $servicios = Servicio::select([
                'idser',
                'nombreser',
                'completoser',
                'precioser',
                'comboser',
                'reventaser',
                'revcompser'
            ])->get();

            $data = $servicios->map(function($servicio) {
                return [
                    'id' => $servicio->idser,
                    'nombre' => $servicio->nombreser,
                    'precios' => [
                        'completo' => $servicio->completoser,
                        'perfil' => $servicio->precioser,
                        'combo' => $servicio->comboser,
                        'reventa' => $servicio->reventaser,
                        'reventa_completo' => $servicio->revcompser
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'count' => $data->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener servicios',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener precios actuales de servicios
     * GET /api/v1/ai/precios
     *
     * Query params:
     * - servicio: filtrar por servicio específico (opcional)
     */
    public function preciosServicios(Request $request)
    {
        try {
            // Los precios están en la tabla servicios, no en valores
            $query = Servicio::select([
                'idser',
                'nombreser',
                'completoser',
                'precioser',
                'comboser',
                'reventaser',
                'revcompser'
            ]);

            if ($request->has('servicio')) {
                $query->where('nombreser', 'LIKE', '%' . $request->servicio . '%');
            }

            $servicios = $query->get();

            $precios = $servicios->map(function($servicio) {
                return [
                    'servicio' => $servicio->nombreser,
                    'precios' => [
                        'completo' => floatval($servicio->completoser),
                        'perfil' => floatval($servicio->precioser),
                        'combo' => floatval($servicio->comboser),
                        'reventa' => floatval($servicio->reventaser),
                        'reventa_completo' => floatval($servicio->revcompser)
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $precios,
                'count' => $precios->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener precios',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar cliente por nombre, email o teléfono
     * GET /api/v1/ai/buscar-cliente
     *
     * Query params:
     * - q: término de búsqueda (requerido)
     */
    public function buscarCliente(Request $request)
    {
        try {
            $request->validate([
                'q' => 'required|string|min:3'
            ]);

            $termino = $request->q;

            $clientes = Cliente::where(function($query) use ($termino) {
                $query->where('nombrecli', 'LIKE', "%{$termino}%")
                      ->orWhere('email', 'LIKE', "%{$termino}%")
                      ->orWhere('telefonocli', 'LIKE', "%{$termino}%");
            })
            ->limit(10)
            ->get(['idcli', 'nombrecli', 'email', 'telefonocli', 'created_at']);

            return response()->json([
                'success' => true,
                'data' => $clientes,
                'count' => $clientes->count()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error en la búsqueda',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener historial de ventas de un cliente
     * GET /api/v1/ai/cliente/{id}/ventas
     */
    public function ventasCliente($id)
    {
        try {
            $cliente = Cliente::findOrFail($id);

            $ventas = Venta::where('idcli', $id)
                ->with(['detalles_venta.perfil'])
                ->orderBy('created_at', 'DESC')
                ->limit(10)
                ->get();

            $ventasResumidas = $ventas->map(function($venta) {
                return [
                    'idven' => $venta->idven,
                    'fecha' => $venta->fechaven,
                    'servicios' => $venta->detalles_venta->map(function($detalle) {
                        return [
                            'descripcion' => $detalle->descripciondet,
                            'monto' => $detalle->montodet,
                            'vencimiento' => $detalle->fechavendet,
                            'activo' => $detalle->activodet
                        ];
                    })
                ];
            });

            return response()->json([
                'success' => true,
                'cliente' => [
                    'id' => $cliente->idcli,
                    'nombre' => $cliente->nombrecli,
                    'email' => $cliente->email
                ],
                'ventas' => $ventasResumidas,
                'total_ventas' => $ventas->count()
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Cliente no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener ventas',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas generales para el asistente
     * GET /api/v1/ai/estadisticas
     */
    public function estadisticas()
    {
        try {
            $stats = [
                'clientes_totales' => Cliente::count(),
                'ventas_mes_actual' => Venta::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                'servicios_totales' => Servicio::count(),
                'perfiles_disponibles' => Perfil::whereRaw('(SELECT COUNT(*) FROM detalles_venta WHERE detalles_venta.idper = perfiles.idper AND detalles_venta.activodet = 1 AND detalles_venta.fechavendet >= CURDATE()) = 0')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener estadísticas',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener base de conocimientos (para chatbot de clientes)
     * GET /api/v1/ai/knowledge-base
     */
    public function knowledgeBase()
    {
        try {
            $kb = [
                'empresa' => [
                    'nombre' => 'Streamify',
                    'descripcion' => 'Plataforma de servicios de streaming compartidos',
                    'whatsapp' => '+593 99 123 4567',
                    'email' => 'soporte@streamify.com',
                    'horarios' => 'Lun-Vie 8AM-10PM, Sáb-Dom 9AM-6PM'
                ],
                'servicios' => Servicio::select([
                    'idser',
                    'nombreser',
                    'completoser as precio_completo',
                    'precioser as precio_perfil',
                    'comboser as precio_combo'
                ])->get(),
                'politicas' => [
                    'garantia' => '99% uptime garantizado, reemplazo en 24h si hay problemas',
                    'reembolso' => 'Reembolso completo si el servicio no funciona en 48h',
                    'soporte' => 'Soporte técnico 24/7 incluido',
                    'renovacion' => 'Descuentos: 10% en planes de 6 meses, 20% en anuales'
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $kb
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener knowledge base',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

