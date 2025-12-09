<?php

namespace App\Traits;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\CursorPaginator;

trait ApiPaginationTrait
{
    /**
     * Formatear respuesta paginada estándar (offset-based)
     *
     * @param LengthAwarePaginator $paginator
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function paginatedResponse($paginator, $message = 'Datos obtenidos exitosamente')
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'has_more_pages' => $paginator->hasMorePages(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
                'path' => $paginator->path(),
            ]
        ], 200);
    }

    /**
     * Formatear respuesta con cursor pagination
     *
     * @param CursorPaginator $paginator
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function cursorPaginatedResponse($paginator, $message = 'Datos obtenidos exitosamente')
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'per_page' => $paginator->perPage(),
                'next_cursor' => $paginator->nextCursor()?->encode(),
                'prev_cursor' => $paginator->previousCursor()?->encode(),
                'has_more' => $paginator->hasMorePages(),
                'path' => $paginator->path(),
            ]
        ], 200);
    }

    /**
     * Formatear respuesta de paginación simple (sin totales)
     *
     * @param \Illuminate\Pagination\Paginator $paginator
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function simplePaginatedResponse($paginator, $message = 'Datos obtenidos exitosamente')
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'has_more_pages' => $paginator->hasMorePages(),
            ],
            'links' => [
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
                'path' => $paginator->path(),
            ]
        ], 200);
    }

    /**
     * Validar y obtener el valor de per_page con límites
     *
     * @param \Illuminate\Http\Request $request
     * @param int $default
     * @param int $max
     * @return int
     */
    protected function getPerPage($request, $default = 15, $max = 100)
    {
        $perPage = $request->input('per_page', $default);
        return min(max((int) $perPage, 1), $max);
    }

    /**
     * Obtener parámetros de ordenamiento validados
     *
     * @param \Illuminate\Http\Request $request
     * @param string $defaultSortBy
     * @param string $defaultSortOrder
     * @param array $allowedFields
     * @return array
     */
    protected function getSortParams($request, $defaultSortBy = 'created_at', $defaultSortOrder = 'desc', $allowedFields = [])
    {
        $sortBy = $request->input('sort_by', $defaultSortBy);
        $sortOrder = $request->input('sort_order', $defaultSortOrder);

        // Validar campo de ordenamiento si se proporcionan campos permitidos
        if (!empty($allowedFields) && !in_array($sortBy, $allowedFields)) {
            $sortBy = $defaultSortBy;
        }

        // Validar dirección de ordenamiento
        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = $defaultSortOrder;
        }

        return [
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder
        ];
    }

    /**
     * Respuesta para colección vacía
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function emptyPaginatedResponse($message = 'No se encontraron resultados')
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [],
            'pagination' => [
                'total' => 0,
                'per_page' => 15,
                'current_page' => 1,
                'last_page' => 1,
                'from' => null,
                'to' => null,
                'has_more_pages' => false,
            ]
        ], 200);
    }
}
