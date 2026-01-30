<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\DetalleProducto;
use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Controlador para gestión de Productos (Agente IA - Técnico)
 * Maneja operaciones sobre productos y servicios
 *
 * Estructura: Producto -> hasMany DetalleProducto -> belongsTo Servicio
 * - Producto Individual: tiene 1 DetalleProducto
 * - Producto Combo: tiene múltiples DetalleProducto
 *
 * Rutas: /api/v2/tech-productos/*
 */
class TecnicoProductosController extends Controller
{
    public function __construct()
    {
        request()->headers->set('Accept', 'application/json');
    }

    /**
     * Activar o desactivar un producto
     * POST /api/v2/tech-productos/cambiar-estado
     *
     * Body:
     * {
     *   "idprod": 1,      // ID del producto
     *   "activo": true    // true para activar, false para desactivar
     * }
     */
    public function cambiarEstado(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'idprod' => 'required|integer|exists:productos,id',
                'activo' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors()
                ], 422);
            }

            $producto = Producto::findOrFail($request->idprod);
            $estadoAnterior = $producto->activo;

            $producto->activo = $request->activo;
            $producto->save();

            return response()->json([
                'success' => true,
                'message' => $request->activo ? 'Producto activado' : 'Producto desactivado',
                'data' => [
                    'id' => $producto->id,
                    'codigo' => $producto->codigopro,
                    'nombre' => $producto->nombrepro,
                    'estado_anterior' => $estadoAnterior,
                    'estado_actual' => $producto->activo
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado del producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activar o desactivar múltiples productos por servicio
     * POST /api/v2/tech-productos/cambiar-estado-masivo
     *
     * Body:
     * {
     *   "servicio": "NETFLIX",     // Nombre del servicio a filtrar
     *   "activo": true,            // true para activar, false para desactivar
     *   "tipo": "individual"       // Opcional: "individual" o "combo"
     * }
     */
    public function cambiarEstadoMasivo(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'servicio' => 'required|string',
                'activo' => 'required',
                'tipo' => 'nullable|in:individual,combo'
            ]);

            // Convertir activo a booleano si viene como string
            if (!is_bool($request->activo)) {
                if (strtolower($request->activo) === 'true') {
                    $request->merge(['activo' => true]);
                } else if (strtolower($request->activo) === 'false') {
                    $request->merge(['activo' => false]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'El campo activo debe ser booleano (true o false)'
                    ], 422);
                }
            }

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Construir query: Producto -> DetalleProducto -> Servicio
            $query = Producto::whereHas('detalles.servicio', function($q) use ($request) {
                $q->where('nombreser', 'LIKE', '%' . strtoupper($request->servicio) . '%');
            });

            // Filtrar por tipo si se especificó
            if ($request->has('tipo')) {
                if ($request->tipo === 'individual') {
                    // Individual: productos con solo 1 detalle (1 servicio)
                    $query->has('detalles', '=', 1);
                } else if ($request->tipo === 'combo') {
                    // Combo: productos con más de 1 detalle (múltiples servicios)
                    $query->has('detalles', '>', 1);
                }
            }

            $productos = $query->get();
            $count = $productos->count();

            if ($count === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron productos con los criterios especificados'
                ], 404);
            }

            // Actualizar todos los productos
            $actualizados = [];
            foreach ($productos as $producto) {
                $estadoAnterior = $producto->activo;
                $producto->activo = $request->activo;
                $producto->save();

                $actualizados[] = [
                    'id' => $producto->id,
                    'codigo' => $producto->codigopro,
                    'nombre' => $producto->nombrepro,
                    'tipo' => $producto->detalles->count() === 1 ? 'individual' : 'combo',
                    'num_servicios' => $producto->detalles->count(),
                    'estado_anterior' => $estadoAnterior
                ];
            }

            return response()->json([
                'success' => true,
                'message' => $request->activo
                    ? "Se activaron {$count} productos"
                    : "Se desactivaron {$count} productos",
                'count' => $count,
                'productos' => $actualizados
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado masivo de productos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar precio de un producto
     * POST /api/v2/tech-productos/cambiar-precio
     *
     * Body:
     * {
     *   "idprod": 1,
     *   "preciopro": 5.50
     * }
     */
    public function cambiarPrecio(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'idprod' => 'required|integer|exists:productos,id',
                'preciopro' => 'required|numeric|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors()
                ], 422);
            }

            $producto = Producto::findOrFail($request->idprod);
            $precioAnterior = $producto->preciopro;

            $producto->preciopro = $request->preciopro;
            $producto->save();

            return response()->json([
                'success' => true,
                'message' => 'Precio actualizado exitosamente',
                'data' => [
                    'id' => $producto->id,
                    'nombre' => $producto->nombrepro,
                    'precio_anterior' => round($precioAnterior, 2),
                    'precio_actual' => round($producto->preciopro, 2)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar precio del producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar precios base de servicios y recalcular productos automáticamente
     * POST /api/v2/tech-productos/actualizar-precios-base
     *
     * Body:
     * {
     *   "precios": {
     *     "NETFLIX": {
     *       "precioser": 5.99,    // Precio individual
     *       "comboser": 4.50      // Precio para combos/meses
     *     },
     *     "MAX": {
     *       "precioser": 4.99,
     *       "comboser": 3.50
     *     }
     *   }
     * }
     *
     * Actualiza los precios base en la tabla servicios y recalcula automáticamente
     * todos los productos que usan esos servicios según las reglas de negocio:
     * - Individual 1 mes: precioser
     * - Individual varios meses: comboser * meses + (meses - 1) * 0.10
     * - Combos: suma de precioser de todos los servicios
     */
    public function actualizarPreciosBase(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'precios' => 'required|array',
                'precios.*.precioser' => 'required|numeric|min:0.75',
                'precios.*.comboser' => 'required|numeric|min:0.5',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $precios = $request->input('precios');
            $serviciosActualizados = [];

            // PASO 1: Actualizar los precios base de los servicios
            foreach ($precios as $idser => $precio) {
                $servicio = Servicio::where('idser', $idser)->first();
                
                if (!$servicio) {
                    continue;
                }

                $precioAnterior = [
                    'precioser' => $servicio->precioser,
                    'comboser' => $servicio->comboser
                ];

                $servicio->precioser = $precio['precioser'];
                $servicio->comboser = $precio['comboser'];
                $servicio->save();

                $serviciosActualizados[] = [
                    'idser' => $idser,
                    'nombreser' => $servicio->nombreser,
                    'precio_anterior' => $precioAnterior,
                    'precio_nuevo' => [
                        'precioser' => $servicio->precioser,
                        'comboser' => $servicio->comboser
                    ]
                ];
            }

            // PASO 2: Recalcular y actualizar los precios de TODOS los productos
            $productos = Producto::with('detalles.servicio')->get();
            $productosActualizados = [];

            foreach ($productos as $producto) {
                $detalles = $producto->detalles;

                // Si no hay detalles, pasar al siguiente producto
                if ($detalles->isEmpty()) {
                    continue;
                }

                $precioAnterior = $producto->preciopro;
                $nuevoPrecio = 0;

                if ($detalles->count() === 1) {
                    // Producto individual (1 solo servicio)
                    // Solo calcular si es categoría individual (categoria_id == 1)
                    if ($producto->categoria_id != 1) {
                        continue;
                    }

                    $servicio = $detalles->first()->servicio;
                    $detalle = $detalles->first();

                    if ($servicio == null) {
                        continue;
                    }

                    // Si tiene más de 1 mes: usar comboser
                    if ($detalle->meses > 1) {
                        $nuevoPrecio = $servicio->comboser * $detalle->meses + ($detalle->meses - 1) * 0.10;
                    } else {
                        // 1 mes: usar precioser
                        $nuevoPrecio = $servicio->precioser;
                    }
                } else {
                    // Producto combo (múltiples servicios)
                    // Suma de precios individuales de cada servicio
                    $nuevoPrecio = $detalles->sum(function ($detalle) {
                        return $detalle->servicio ? $detalle->servicio->precioser : 0;
                    });

                    if ($nuevoPrecio === 0) {
                        continue;
                    }
                }

                // Si el precio es entero (decimales .00), restar 0.01
                if (fmod($nuevoPrecio, 1) == 0.0) {
                    $nuevoPrecio -= 0.01;
                }

                $producto->preciopro = round($nuevoPrecio, 2);
                $producto->save();

                // Solo registrar si el precio cambió
                if (round($precioAnterior, 2) != round($nuevoPrecio, 2)) {
                    $productosActualizados[] = [
                        'id' => $producto->id,
                        'codigo' => $producto->codigopro,
                        'nombre' => $producto->nombrepro,
                        'tipo' => $detalles->count() === 1 ? 'individual' : 'combo',
                        'precio_anterior' => round($precioAnterior, 2),
                        'precio_nuevo' => round($nuevoPrecio, 2)
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Precios actualizados exitosamente',
                'servicios_actualizados' => count($serviciosActualizados),
                'productos_recalculados' => count($productosActualizados),
                'servicios' => $serviciosActualizados,
                'productos' => $productosActualizados
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar precios base',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo producto
     * POST /api/v2/tech-productos/crear
     *
     * Body:
     * {
     *   "codigopro": "PROD-001",
     *   "nombrepro": "Netflix Premium Individual",
     *   "preciopro": 15.99,
     *   "activo": true,
     *   "tipo_producto_id": 1,         // ID del tipo de producto
     *   "categoria_id": 1,             // ID de la categoría
     *   "servicios": [                 // Array de servicios
     *     {
     *       "idser": 1,                // ID del servicio (Netflix)
     *       "meses": 1,                // Duración en meses
     *       "descripcion": "Plan Premium"
     *     }
     *   ]
     * }
     */
    public function crear(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'codigopro' => 'required|string|max:50|unique:productos,codigopro',
                'nombrepro' => 'required|string|max:255',
                'preciopro' => 'required|numeric|min:0',
                'activo' => 'nullable|boolean',
                'tipo_producto_id' => 'required|integer|exists:tipo_productos,id',
                'categoria_id' => 'required|integer|exists:categorias,id',
                'servicios' => 'required|array|min:1',
                'servicios.*.idser' => 'required|integer|exists:servicios,idser',
                'servicios.*.meses' => 'nullable|integer|min:1',
                'servicios.*.descripcion' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Crear el producto
            $producto = Producto::create([
                'codigopro' => $request->codigopro,
                'nombrepro' => $request->nombrepro,
                'preciopro' => $request->preciopro,
                'activo' => $request->input('activo', true),
                'tipo_producto_id' => $request->tipo_producto_id,
                'categoria_id' => $request->categoria_id
            ]);

            // Crear los detalles (relación con servicios)
            $detalles = [];
            foreach ($request->servicios as $servicio) {
                $detalle = DetalleProducto::create([
                    'producto_id' => $producto->id,
                    'idser' => $servicio['idser'],
                    'meses' => $servicio['meses'] ?? 1,
                    'descripcion' => $servicio['descripcion'] ?? null
                ]);
                $detalles[] = $detalle;
            }

            DB::commit();

            // Cargar relaciones para respuesta
            $producto->load(['detalles.servicio', 'tipoProducto', 'categoria']);

            return response()->json([
                'success' => true,
                'message' => 'Producto creado exitosamente',
                'data' => [
                    'id' => $producto->id,
                    'codigo' => $producto->codigopro,
                    'nombre' => $producto->nombrepro,
                    'precio' => $producto->preciopro,
                    'activo' => $producto->activo,
                    'tipo' => $producto->detalles->count() === 1 ? 'individual' : 'combo',
                    'servicios' => $producto->detalles->map(function($detalle) {
                        return [
                            'id' => $detalle->id,
                            'servicio' => $detalle->servicio->nombreser,
                            'meses' => $detalle->meses,
                            'descripcion' => $detalle->descripcion
                        ];
                    })
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Editar producto existente
     * PUT /api/v2/tech-productos/editar/{idprod}
     *
     * Body:
     * {
     *   "nombrepro": "Netflix Premium Individual Actualizado",
     *   "preciopro": 17.99,
     *   "activo": true
     * }
     */
    public function editar(Request $request, $idprod)
    {
        try {
            $validator = Validator::make($request->all(), [
                'codigopro' => 'nullable|string|max:50|unique:productos,codigopro,' . $idprod,
                'nombrepro' => 'nullable|string|max:255',
                'preciopro' => 'nullable|numeric|min:0',
                'activo' => 'nullable|boolean',
                'tipo_producto_id' => 'nullable|integer|exists:tipo_productos,id',
                'categoria_id' => 'nullable|integer|exists:categorias,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors()
                ], 422);
            }

            $producto = Producto::findOrFail($idprod);

            if ($request->has('codigopro')) {
                $producto->codigopro = $request->codigopro;
            }
            if ($request->has('nombrepro')) {
                $producto->nombrepro = $request->nombrepro;
            }
            if ($request->has('preciopro')) {
                $producto->preciopro = $request->preciopro;
            }
            if ($request->has('activo')) {
                $producto->activo = $request->activo;
            }
            if ($request->has('tipo_producto_id')) {
                $producto->tipo_producto_id = $request->tipo_producto_id;
            }
            if ($request->has('categoria_id')) {
                $producto->categoria_id = $request->categoria_id;
            }

            $producto->save();

            // Cargar relaciones para respuesta
            $producto->load(['detalles.servicio']);

            return response()->json([
                'success' => true,
                'message' => 'Producto actualizado exitosamente',
                'data' => [
                    'id' => $producto->id,
                    'codigo' => $producto->codigopro,
                    'nombre' => $producto->nombrepro,
                    'precio' => $producto->preciopro,
                    'activo' => $producto->activo,
                    'tipo' => $producto->detalles->count() === 1 ? 'individual' : 'combo',
                    'num_servicios' => $producto->detalles->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al editar producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener un producto específico por ID
     * GET /api/v2/tech-productos/obtener/{id}
     *
     * Retorna toda la información del producto incluyendo sus servicios
     */
    public function obtener($id)
    {
        try {
            $producto = Producto::with(['detalles.servicio', 'tipoProducto', 'categoria'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $producto->id,
                    'codigo' => $producto->codigopro,
                    'nombre' => $producto->nombrepro,
                    'precio' => $producto->preciopro,
                    'activo' => $producto->activo,
                    'estrellas' => $producto->estrellaspro,
                    'descripcion' => $producto->descripcionpro,
                    'foto' => $producto->foto,
                    'tipo_producto' => [
                        'id' => $producto->tipoProducto->id ?? null,
                        'nombre' => $producto->tipoProducto->nombre ?? 'N/A'
                    ],
                    'categoria' => [
                        'id' => $producto->categoria->id ?? null,
                        'nombre' => $producto->categoria->nombre ?? 'N/A'
                    ],
                    'tipo' => $producto->detalles->count() === 1 ? 'individual' : 'combo',
                    'num_servicios' => $producto->detalles->count(),
                    'servicios' => $producto->detalles->map(function($detalle) {
                        return [
                            'id_detalle' => $detalle->id,
                            'servicio_id' => $detalle->idser,
                            'servicio_nombre' => $detalle->servicio->nombreser ?? 'N/A',
                            'meses' => $detalle->meses,
                            'descripcion' => $detalle->descripcion
                        ];
                    }),
                    'created_at' => $producto->created_at,
                    'updated_at' => $producto->updated_at
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar productos con filtros
     * GET /api/v2/tech-productos/listar
     *
     * Query params:
     * - servicio: Filtrar por nombre de servicio
     * - activo: true/false
     * - tipo: individual (1 servicio), combo (múltiples servicios)
     */
    public function listar(Request $request)
    {
        try {
            $query = Producto::with(['detalles.servicio']);

            if ($request->has('servicio')) {
                $query->whereHas('detalles.servicio', function($q) use ($request) {
                    $q->where('nombreser', 'LIKE', '%' . strtoupper($request->servicio) . '%');
                });
            }

            if ($request->has('activo')) {
                $query->where('activo', $request->boolean('activo'));
            }

            if ($request->has('tipo')) {
                if ($request->tipo === 'individual') {
                    $query->has('detalles', '=', 1);
                } else if ($request->tipo === 'combo') {
                    $query->has('detalles', '>', 1);
                }
            }

            $productos = $query->get()->map(function($prod) {
                return [
                    'id' => $prod->id,
                    'codigo' => $prod->codigopro,
                    'nombre' => $prod->nombrepro,
                    'precio' => $prod->preciopro,
                    'activo' => $prod->activo,
                    'tipo' => $prod->detalles->count() === 1 ? 'individual' : 'combo',
                    'servicios' => $prod->detalles->map(function($detalle) {
                        return [
                            'nombre' => $detalle->servicio->nombreser ?? 'N/A',
                            'meses' => $detalle->meses,
                            'descripcion' => $detalle->descripcion
                        ];
                    })
                ];
            });

            return response()->json([
                'success' => true,
                'count' => $productos->count(),
                'productos' => $productos
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar productos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
