<?php

namespace App\Http\Controllers;

use App\Models\Costo;
use App\Models\Cuenta;
use App\Models\Historial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CostoController extends Controller
{
    // Eliminamos el __construct() que usaba middleware para probar los métodos de forma manual

    public function index(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('costos')) {
            abort(403, 'No tienes permiso para ver los costos.');
        }

        // Si es petición AJAX, retornar datos paginados
        if ($request->ajax() || $request->has('ajax')) {
            return $this->getCostosAjax($request);
        }

        // Obtener todas las cuentas para el selector
        $cuentas = Cuenta::with(['valor'])
            ->where('activocue', true)
            ->orderBy('fechavencue')
            ->get();

        $idcueSeleccionado = $request->idcue;

        return view('finance.costos', compact('cuentas', 'idcueSeleccionado'));
    }

    /**
     * Obtener costos paginados para AJAX
     */
    private function getCostosAjax(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $page = $request->input('page', 1);
        $search = $request->input('search', '');
        $sortBy = $request->input('sort_by', '');
        $sortOrder = $request->input('sort_order', 'desc');

        $query = Costo::with(['cuenta']);

        // Búsqueda
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('descripcioncos', 'like', "%{$search}%")
                    ->orWhere('montocos', 'like', "%{$search}%")
                    ->orWhereHas('cuenta', function ($q2) use ($search) {
                        $q2->where('idcue', 'like', "%{$search}%");
                    });
            });
        }

        // Ordenamiento
        $validSortColumns = ['idcos' => 'idcos', 'fechacos' => 'fechacos', 'montocos' => 'montocos'];
        if ($sortBy !== '' && isset($validSortColumns[$sortBy])) {
            $query->orderBy($validSortColumns[$sortBy], $sortOrder);
        } else {
            $query->orderBy('fechacos', 'desc');
        }

        $totalRecords = $query->count();
        $costos = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        $html = view('finance.partials.costos-rows', compact('costos'))->render();

        return response()->json([
            'html' => $html,
            'total_records' => $totalRecords,
            'current_page' => $page,
            'per_page' => $perPage
        ]);
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('costos.store')) {
            abort(403, 'No tienes permiso para crear costos.');
        }

        // Validar los datos
        $request->validate([
            'idcue' => 'required|exists:cuentas,idcue',
            'descripcioncos' => 'required|string|max:50',
            'montocos' => 'required|numeric|min:0',
            'fechacos' => 'nullable|date'
        ]);

        // Crear el costo
        $costo = Costo::create($request->all());

        Historial::create([
            'accion' => 'Creación de Costo',
            'descripcion' => 'Datos: ' . json_encode($costo),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        return redirect()->route('costos', ['idcue' => $request->idcue])
            ->with('success', 'Costo creado correctamente.');
    }

    public function update(Request $request, $idcos)
    {
        if (!Auth::user()->hasPermissionTo('costos.update')) {
            abort(403, 'No tienes permiso para actualizar costos.');
        }

        $request->validate([
            'descripcioncos' => 'required|string|max:50',
            'montocos' => 'required|numeric',
            'fechacos' => 'required|date',
        ]);

        $costo = Costo::findOrFail($idcos);

        Historial::create([
            'accion' => 'Actualización de Costo',
            'descripcion' => 'Datos antiguos: ' . json_encode($costo),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        $costo->update([
            'descripcioncos' => $request->descripcioncos,
            'montocos' => $request->montocos,
            'fechacos' => $request->fechacos,
        ]);

        return redirect()->route('costos')->with('success', 'Costo actualizado con éxito.');
    }

    public function destroy($idcos)
    {
        if (!Auth::user()->hasPermissionTo('costos.destroy')) {
            abort(403, 'No tienes permiso para eliminar costos.');
        }

        $costo = Costo::findOrFail($idcos);
        $idcue = $costo->idcue; // Para regresar a la cuenta seleccionada

        Historial::create([
            'accion' => 'Eliminación de Costo',
            'descripcion' => 'Datos Eliminados: ' . json_encode($costo),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        $costo->delete();

        return redirect()->route('costos', ['idcue' => $idcue])
            ->with('success', 'Costo eliminado correctamente.');
    }
}
