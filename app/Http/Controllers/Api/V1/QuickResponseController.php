<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\QuickResponse;
use Illuminate\Http\Request;

class QuickResponseController extends Controller
{
    /**
     * Buscar respuestas rápidas
     * GET /api/v1/quick-responses/search
     */
    public function search(Request $request)
    {
        try {
            $query = QuickResponse::activas()->orderBy('orden');

            if ($request->has('tipo')) {
                $query->tipo($request->tipo);
            }

            if ($request->has('comando')) {
                $query->porComando($request->comando);
            }

            if ($request->has('q')) {
                $query->buscar($request->q);
            }

            $responses = $query->get();

            return response()->json([
                'success' => true,
                'data' => $responses,
                'count' => $responses->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al buscar respuestas rápidas',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener respuesta por comando específico
     * GET /api/v1/quick-responses/comando/{comando}
     */
    public function porComando($comando)
    {
        try {
            $comandoNormalizado = ltrim($comando, '/');

            $response = QuickResponse::activas()
                ->porComando($comandoNormalizado)
                ->first();

            if (!$response) {
                return response()->json([
                    'success' => false,
                    'error' => 'Comando no encontrado',
                    'message' => "No se encontró el comando '/{$comandoNormalizado}'"
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $response
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener respuesta',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar comandos disponibles
     * GET /api/v1/quick-responses
     */
    public function index(Request $request)
    {
        try {
            $query = QuickResponse::activas()->orderBy('orden');

            if ($request->has('tipo')) {
                $query->tipo($request->tipo);
            }

            $responses = $query->get();

            $comandos = $responses->map(function($r) {
                return [
                    'comando' => $r->comando,
                    'titulo' => $r->titulo,
                    'tipo' => $r->tipo,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $comandos,
                'count' => $comandos->count(),
                'tip' => 'Usa GET /api/v1/quick-responses/comando/{comando} para el contenido completo'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al listar respuestas',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear respuesta rápida
     * POST /api/v1/quick-responses
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'comando' => 'required|string|max:50|unique:quick_responses,comando',
                'titulo' => 'required|string|max:200',
                'contenido' => 'required|string',
                'tipo' => 'required|in:empleado,cliente,ambos',
                'activo' => 'nullable|boolean',
                'orden' => 'nullable|integer',
                'tags' => 'nullable|array',
            ]);

            $validated['comando'] = ltrim($validated['comando'], '/');

            $response = QuickResponse::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Respuesta rápida creada',
                'data' => $response
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al crear',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar respuesta
     * PUT /api/v1/quick-responses/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $response = QuickResponse::findOrFail($id);

            $validated = $request->validate([
                'comando' => 'sometimes|string|max:50|unique:quick_responses,comando,' . $id,
                'titulo' => 'sometimes|string|max:200',
                'contenido' => 'sometimes|string',
                'tipo' => 'sometimes|in:empleado,cliente,ambos',
                'activo' => 'sometimes|boolean',
                'orden' => 'sometimes|integer',
                'tags' => 'sometimes|array',
            ]);

            if (isset($validated['comando'])) {
                $validated['comando'] = ltrim($validated['comando'], '/');
            }

            $response->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Respuesta actualizada',
                'data' => $response
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Respuesta no encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al actualizar',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar respuesta
     * DELETE /api/v1/quick-responses/{id}
     */
    public function destroy($id)
    {
        try {
            $response = QuickResponse::findOrFail($id);
            $response->delete();

            return response()->json([
                'success' => true,
                'message' => 'Respuesta eliminada'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Respuesta no encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al eliminar',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
