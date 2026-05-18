<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Cuenta;
use App\Models\Valor;
use App\Models\Servicio;
use App\Models\Proveedor;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Controlador para gestión de Valores, Servicios y Proveedores (Agente IA - Técnico)
 * Maneja operaciones sobre valores, servicios y proveedores
 *
 * Rutas: /api/v2/tech-config/*
 */
class TecnicoConfigController extends Controller
{
    public function __construct()
    {
        request()->headers->set('Accept', 'application/json');
    }

    // ==================== VALORES ====================

    /**
     * Definir pantallas mínimas y máximas para valores de un servicio
     * POST /api/v2/tech-config/valores/pantallas
     *
     * Body:
     * {
     *   "servicio": "NETFLIX",
     *   "min_pantallas": 1,
     *   "max_pantallas": 5
     * }
     */
    public function definirPantallas(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'servicio' => 'required|string',
                'min_pantallas' => 'required|integer|min:1',
                'max_pantallas' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors()
                ], 422);
            }

            if ($request->min_pantallas > $request->max_pantallas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Las pantallas mínimas no pueden ser mayores a las máximas'
                ], 422);
            }

            // Buscar valores del servicio
            $valores = Valor::whereHas('servicio', function($q) use ($request) {
                $q->where('nombreser', 'LIKE', '%' . strtoupper($request->servicio) . '%');
            })->get();

            if ($valores->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron valores para el servicio especificado'
                ], 404);
            }

            // Actualizar pantallas en todos los valores
            $actualizados = [];
            foreach ($valores as $valor) {
                $valor->min_pantallas = $request->min_pantallas;
                $valor->max_pantallas = $request->max_pantallas;
                $valor->save();

                $actualizados[] = [
                    'idval' => $valor->idval,
                    'tipo' => $valor->tipoval,
                    'meses' => $valor->mesesval
                ];
            }

            return response()->json([
                'success' => true,
                'message' => "Se actualizaron {$valores->count()} valores",
                'data' => [
                    'servicio' => strtoupper($request->servicio),
                    'min_pantallas' => $request->min_pantallas,
                    'max_pantallas' => $request->max_pantallas,
                    'valores_actualizados' => $actualizados
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al definir pantallas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo valor
     * POST /api/v2/tech-config/valores/crear
     *
     * Body:
     * {
     *   "idser": "NETFLIX",
     *   "idpro": 1,
     *   "tipoval": "premium",
     *   "mesesval": 1,
     *   "min_pantallas": 1,
     *   "max_pantallas": 5,
     *   "bot": "https://..."
     * }
     */
    public function crearValor(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'idser' => 'required|string|exists:servicios,idser',
                'idpro' => 'required|integer|exists:proveedores,idpro',
                'tipoval' => 'required|string|max:50',
                'mesesval' => 'required|integer|min:1',
                'min_pantallas' => 'nullable|integer|min:1',
                'max_pantallas' => 'nullable|integer|min:1',
                'bot' => 'nullable|url'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors()
                ], 422);
            }

            // El trigger generará automáticamente el idval
            $valor = new Valor();
            $valor->idser = strtoupper($request->idser);
            $valor->idpro = $request->idpro;
            $valor->tipoval = $request->tipoval;
            $valor->mesesval = $request->mesesval;
            $valor->min_pantallas = $request->input('min_pantallas', 1);
            $valor->max_pantallas = $request->input('max_pantallas', 5);
            $valor->bot = $request->bot;
            $valor->save();

            return response()->json([
                'success' => true,
                'message' => 'Valor creado exitosamente',
                'data' => [
                    'idval' => $valor->idval,
                    'servicio' => $valor->idser,
                    'tipo' => $valor->tipoval,
                    'meses' => $valor->mesesval,
                    'pantallas' => [
                        'min' => $valor->min_pantallas,
                        'max' => $valor->max_pantallas
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear valor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Editar valor existente
     * PUT /api/v2/tech-config/valores/editar/{idval}
     */
    public function editarValor(Request $request, $idval)
    {
        try {
            $validator = Validator::make($request->all(), [
                'tipoval' => 'nullable|string|max:50',
                'mesesval' => 'nullable|integer|min:1',
                'min_pantallas' => 'nullable|integer|min:1',
                'max_pantallas' => 'nullable|integer|min:1',
                'bot' => 'nullable|url'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors()
                ], 422);
            }

            $valor = Valor::findOrFail($idval);

            if ($request->has('tipoval')) {
                $valor->tipoval = $request->tipoval;
            }
            if ($request->has('mesesval')) {
                $valor->mesesval = $request->mesesval;
            }
            if ($request->has('min_pantallas')) {
                $valor->min_pantallas = $request->min_pantallas;
            }
            if ($request->has('max_pantallas')) {
                $valor->max_pantallas = $request->max_pantallas;
            }
            if ($request->has('bot')) {
                $valor->bot = $request->bot;
            }

            $valor->save();

            return response()->json([
                'success' => true,
                'message' => 'Valor actualizado exitosamente',
                'data' => [
                    'idval' => $valor->idval,
                    'tipo' => $valor->tipoval,
                    'meses' => $valor->mesesval,
                    'pantallas' => [
                        'min' => $valor->min_pantallas,
                        'max' => $valor->max_pantallas
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al editar valor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener un valor específico por ID
     * GET /api/v2/tech-config/valores/obtener/{idval}
     */
    public function obtenerValor($idval)
    {
        try {
            $valor = Valor::with(['servicio', 'proveedor'])
                ->findOrFail($idval);

            return response()->json([
                'success' => true,
                'data' => [
                    'idval' => $valor->idval,
                    'servicio' => [
                        'idser' => $valor->servicio->idser ?? null,
                        'nombre' => $valor->servicio->nombreser ?? 'N/A',
                        'imagen' => $valor->servicio->imagenser ?? null
                    ],
                    'proveedor' => [
                        'idpro' => $valor->proveedor->idpro ?? null,
                        'nombre' => $valor->proveedor->nombrepro ?? 'N/A'
                    ],
                    'tipo' => $valor->tipoval,
                    'meses' => $valor->mesesval,
                    'pantallas' => [
                        'min' => $valor->min_pantallas,
                        'max' => $valor->max_pantallas
                    ],
                    'bot' => $valor->bot,
                    'created_at' => $valor->created_at,
                    'updated_at' => $valor->updated_at
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Valor no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener valor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar valores con filtros
     * GET /api/v2/tech-config/valores/listar
     */
    public function listarValores(Request $request)
    {
        try {
            $query = Valor::with(['servicio', 'proveedor']);

            if ($request->has('servicio')) {
                $query->where('idser', strtoupper($request->servicio));
            }

            if ($request->has('proveedor')) {
                $query->where('idpro', $request->proveedor);
            }

            $valores = $query->get()->map(function($val) {
                return [
                    'idval' => $val->idval,
                    'servicio' => $val->servicio->nombreser ?? 'N/A',
                    'proveedor' => $val->proveedor->nombrepro ?? 'N/A',
                    'tipo' => $val->tipoval,
                    'meses' => $val->mesesval,
                    'pantallas' => [
                        'min' => $val->min_pantallas,
                        'max' => $val->max_pantallas
                    ],
                    'bot' => $val->bot
                ];
            });

            return response()->json([
                'success' => true,
                'count' => $valores->count(),
                'valores' => $valores
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar valores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== SERVICIOS ====================

    /**
     * Crear nuevo servicio
     * POST /api/v2/tech-config/servicios/crear
     */
    public function crearServicio(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'idser' => 'required|string|max:20|unique:servicios,idser',
                'nombreser' => 'required|string|max:100',
                'imagenser' => 'nullable|url'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors()
                ], 422);
            }

            $servicio = Servicio::create([
                'idser' => strtoupper($request->idser),
                'nombreser' => $request->nombreser,
                'imagenser' => $request->imagenser
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Servicio creado exitosamente',
                'data' => [
                    'idser' => $servicio->idser,
                    'nombre' => $servicio->nombreser,
                    'imagen' => $servicio->imagenser
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear servicio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Editar servicio existente
     * PUT /api/v2/tech-config/servicios/editar/{idser}
     */
    public function editarServicio(Request $request, $idser)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombreser' => 'nullable|string|max:100',
                'imagenser' => 'nullable|url'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors()
                ], 422);
            }

            $servicio = Servicio::findOrFail(strtoupper($idser));

            if ($request->has('nombreser')) {
                $servicio->nombreser = $request->nombreser;
            }
            if ($request->has('imagenser')) {
                $servicio->imagenser = $request->imagenser;
            }

            $servicio->save();

            return response()->json([
                'success' => true,
                'message' => 'Servicio actualizado exitosamente',
                'data' => [
                    'idser' => $servicio->idser,
                    'nombre' => $servicio->nombreser,
                    'imagen' => $servicio->imagenser
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al editar servicio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener un servicio específico por ID
     * GET /api/v2/tech-config/servicios/obtener/{idser}
     */
    public function obtenerServicio($idser)
    {
        try {
            $servicio = Servicio::findOrFail(strtoupper($idser));

            // Contar recursos relacionados
            $valoresCount = Valor::where('idser', $servicio->idser)->count();
            $cuentasCount = DB::table('cuentas')
                ->join('valores', 'cuentas.idval', '=', 'valores.idval')
                ->where('valores.idser', $servicio->idser)
                ->where('activocue', true)
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'idser' => $servicio->idser,
                    'nombre' => $servicio->nombreser,
                    'imagen' => $servicio->imagenser,
                    'estadisticas' => [
                        'valores_asociados' => $valoresCount,
                        'cuentas_asociadas' => $cuentasCount
                    ],
                    'created_at' => $servicio->created_at,
                    'updated_at' => $servicio->updated_at
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Servicio no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener servicio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar servicios
     * GET /api/v2/tech-config/servicios/listar
     */
    public function listarServicios()
    {
        try {
            $servicios = Servicio::all()->map(function($ser) {
                return [
                    'idser' => $ser->idser,
                    'nombre' => $ser->nombreser,
                    'imagen' => $ser->imagenser
                ];
            });

            return response()->json([
                'success' => true,
                'count' => $servicios->count(),
                'servicios' => $servicios
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar servicios',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== PROVEEDORES ====================

    /**
     * Crear nuevo proveedor
     * POST /api/v2/tech-config/proveedores/crear
     */
    public function crearProveedor(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombrepro' => 'required|string|max:100',
                'telefonopro' => 'nullable|string|max:20',
                'direccionpro' => 'nullable|string|max:200'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors()
                ], 422);
            }

            $proveedor = Proveedor::create([
                'nombrepro' => $request->nombrepro,
                'telefonopro' => $request->telefonopro,
                'direccionpro' => $request->direccionpro
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Proveedor creado exitosamente',
                'data' => [
                    'idpro' => $proveedor->idpro,
                    'nombre' => $proveedor->nombrepro,
                    'telefono' => $proveedor->telefonopro
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear proveedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Editar proveedor existente
     * PUT /api/v2/tech-config/proveedores/editar/{idpro}
     */
    public function editarProveedor(Request $request, $idpro)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombrepro' => 'nullable|string|max:100',
                'telefonopro' => 'nullable|string|max:20',
                'direccionpro' => 'nullable|string|max:200'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors()
                ], 422);
            }

            $proveedor = Proveedor::findOrFail($idpro);

            if ($request->has('nombrepro')) {
                $proveedor->nombrepro = $request->nombrepro;
            }
            if ($request->has('telefonopro')) {
                $proveedor->telefonopro = $request->telefonopro;
            }
            if ($request->has('direccionpro')) {
                $proveedor->direccionpro = $request->direccionpro;
            }

            $proveedor->save();

            return response()->json([
                'success' => true,
                'message' => 'Proveedor actualizado exitosamente',
                'data' => [
                    'idpro' => $proveedor->idpro,
                    'nombre' => $proveedor->nombrepro,
                    'telefono' => $proveedor->telefonopro
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al editar proveedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener un proveedor específico por ID
     * GET /api/v2/tech-config/proveedores/obtener/{idpro}
     */
    public function obtenerProveedor($idpro)
    {
        try {
            $proveedor = Proveedor::findOrFail($idpro);

            // Contar recursos relacionados
            $valoresCount = Valor::where('idpro', $proveedor->idpro)->count();
            $cuentasCount = Cuenta::where('idpro', $proveedor->idpro)->where('activocue', true)->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'idpro' => $proveedor->idpro,
                    'nombre' => $proveedor->nombrepro,
                    'telefono' => $proveedor->telefonopro,
                    'direccion' => $proveedor->direccionpro,
                    'estadisticas' => [
                        'valores_asociados' => $valoresCount,
                        'cuentas_asociadas' => $cuentasCount
                    ],
                    'created_at' => $proveedor->created_at,
                    'updated_at' => $proveedor->updated_at
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Proveedor no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener proveedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar proveedores
     * GET /api/v2/tech-config/proveedores/listar
     */
    public function listarProveedores(Request $request)
    {
        try {
            $query = $request->get('q', '');
            $proveedoresQuery = Proveedor::where('activopro', true);

            if (!empty($query)) {
                $proveedoresQuery->where(function($q) use ($query) {
                    $q->where('nombrepro', 'LIKE', '%' . $query . '%')
                      ->orWhere('telefonopro', 'LIKE', '%' . $query . '%');
                });
            }

            $proveedores = $proveedoresQuery->orderBy('nombrepro')->get()->map(function($pro) {
                return [
                    'idpro' => $pro->idpro,
                    'nombre' => $pro->nombrepro,
                    'telefono' => $pro->telefonopro,
                    'direccion' => $pro->direccionpro
                ];
            });

            return response()->json([
                'success' => true,
                'count' => $proveedores->count(),
                'proveedores' => $proveedores
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar proveedores',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
