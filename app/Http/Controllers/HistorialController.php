<?php

namespace App\Http\Controllers;

use App\Models\Historial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistorialController extends Controller
{
    public function show(Request $request)
    {
        /** @var \App\Models\Empleado $user */
        $user = Auth::user();

        if (!$user->hasPermissionTo('historial')) {
            abort(403, 'No tienes permiso para ver esta página.');
        }

        // Si es petición AJAX, retornar datos paginados
        if ($request->ajax() || $request->has('ajax')) {
            return $this->getHistorialAjax($request);
        }

        return view('historial.index');
    }

    /**
     * Obtener historial paginado para AJAX
     */
    private function getHistorialAjax(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $page = $request->input('page', 1);
        $search = $request->input('search', '');
        $sortBy = $request->input('sort_by', '');
        $sortOrder = $request->input('sort_order', 'desc');

        $query = Historial::query();

        // Búsqueda
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('accionhis', 'like', "%{$search}%")
                    ->orWhere('empleadohis', 'like', "%{$search}%")
                    ->orWhere('descripcionhis', 'like', "%{$search}%");
            });
        }

        // Ordenamiento
        $validSortColumns = ['idhis' => 'idhis', 'created_at' => 'created_at'];
        if ($sortBy !== '' && isset($validSortColumns[$sortBy])) {
            $query->orderBy($validSortColumns[$sortBy], $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $totalRecords = $query->count();
        $historial = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        $html = view('historial.partials.table-rows', compact('historial'))->render();

        return response()->json([
            'html' => $html,
            'total_records' => $totalRecords,
            'current_page' => $page,
            'per_page' => $perPage
        ]);
    }
    public function clear(Request $request)
    {
        /** @var \App\Models\Empleado $user */
        $user = Auth::user();
        if (!$user->hasPermissionTo('historial.clear')) {
            abort(403, 'No tienes permiso para ver esta página.');
        }
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        // Borrar registros en el rango de fechas (suponiendo que 'fecha' es del tipo date o datetime)
        Historial::whereBetween('created_at', [$request->start_date, $request->end_date])->delete();

        return redirect()->route('historial')->with('success', 'Historial borrado exitosamente.');
    }
}
