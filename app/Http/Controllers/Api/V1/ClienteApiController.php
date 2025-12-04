<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClienteApiController extends Controller
{
    /**
     * Listar todos los clientes
     * GET /api/v1/clientes
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');

            $query = Cliente::query();

            // Búsqueda por nombre, teléfono o email
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombrecli', 'like', "%{$search}%")
                      ->orWhere('telefonocli', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $clientes = $query->orderBy('idcli', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $clientes->items(),
                'pagination' => [
                    'total' => $clientes->total(),
                    'per_page' => $clientes->perPage(),
                    'current_page' => $clientes->currentPage(),
                    'last_page' => $clientes->lastPage(),
                    'from' => $clientes->firstItem(),
                    'to' => $clientes->lastItem(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener clientes',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo cliente
     * POST /api/v1/clientes
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombrecli' => 'required|string|max:50',
                'telefonocli' => 'required|string|max:15|unique:clientes,telefonocli',
                'email' => 'nullable|email|max:50',
                'password' => 'nullable|string|max:50',
                'pais' => 'nullable|string|max:50',
                'saldo' => 'nullable|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $cliente = Cliente::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Cliente creado exitosamente',
                'data' => $cliente
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al crear cliente',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener un cliente específico
     * GET /api/v1/clientes/{id}
     */
    public function show(string $id)
    {
        try {
            $cliente = Cliente::with(['ventas.producto', 'pedidos'])->find($id);

            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cliente no encontrado',
                    'message' => "No existe un cliente con ID {$id}"
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $cliente
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener cliente',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar cliente existente
     * PUT/PATCH /api/v1/clientes/{id}
     */
    public function update(Request $request, string $id)
    {
        try {
            $cliente = Cliente::find($id);

            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cliente no encontrado',
                    'message' => "No existe un cliente con ID {$id}"
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'nombrecli' => 'sometimes|string|max:50',
                'telefonocli' => 'sometimes|string|max:15|unique:clientes,telefonocli,' . $id . ',idcli',
                'email' => 'nullable|email|max:50',
                'pais' => 'nullable|string|max:50',
                'saldo' => 'nullable|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $cliente->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Cliente actualizado exitosamente',
                'data' => $cliente->fresh()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al actualizar cliente',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar cliente
     * DELETE /api/v1/clientes/{id}
     */
    public function destroy(string $id)
    {
        try {
            $cliente = Cliente::find($id);

            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cliente no encontrado',
                    'message' => "No existe un cliente con ID {$id}"
                ], 404);
            }

            $cliente->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cliente eliminado exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al eliminar cliente',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener ventas de un cliente
     * GET /api/v1/clientes/{id}/ventas
     */
    public function ventas(string $id)
    {
        try {
            $cliente = Cliente::with('ventas.producto')->find($id);

            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cliente no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'cliente' => $cliente->only(['idcli', 'nombrecli', 'telefonocli', 'email']),
                    'total_ventas' => $cliente->ventas->count(),
                    'ventas' => $cliente->ventas
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener ventas',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
