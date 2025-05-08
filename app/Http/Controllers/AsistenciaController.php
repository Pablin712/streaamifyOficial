<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asistencia;
use Illuminate\Support\Facades\Auth;
use App\Models\Empleado;
use App\Services\EmpleadoService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;

class AsistenciaController extends Controller
{
    protected $empleadoService;

    /**
     * Constructor de la clase AsistenciaController.
     *
     * @param EmpleadoService $empleadoService
     */
    public function __construct(EmpleadoService $empleadoService)
    {
        $this->empleadoService = $empleadoService;
    }

    /**
     * Muestra las estadísticas de los empleados para el día actual.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (!Gate::allows('empleados')) {
            abort(403, 'No tienes permisos para ver los empleados.');
        }
        $mes = Carbon::today()->format('m');
        $anio = Carbon::today()->format('Y');
        $empleados = Empleado::orderBy('idemp', 'asc')->get();

        $ordenPersonalizado = [2, 12]; // IDs prioritarios
        $estadisticas = $this->empleadoService->obtenerEstadisticasDeEmpleados($empleados, $mes, $anio);
        $estadisticasOrdenadas = collect($estadisticas)->sortBy(function ($_, $id) use ($ordenPersonalizado) {
            $index = array_search($id, $ordenPersonalizado);
            return $index === false ? 9999 + $id : $index; // los no encontrados se ordenan después
        });
        // Guarda estadísticas de cada empleado
        return view('employee.statistics', compact('estadisticasOrdenadas', 'empleados'));
    }
    /**
     * Registra la asistencia del empleado.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function ping(Request $request)
    {
        Asistencia::create([
            'empleado_id' => Auth::user()->idemp, // asumiendo que usas Auth con empleados
            'ruta_actual' => $request->input('ruta_actual'),
            'created_at' => now(),
        ]);

        return response()->noContent();
    }
}
