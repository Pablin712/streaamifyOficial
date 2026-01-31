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
     * Evita duplicados: No registra si ya existe una asistencia del mismo empleado
     * en los últimos 30 segundos (previene múltiples pestañas).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function ping(Request $request)
    {
        $empleadoId = Auth::user()->idemp;
        $rutaActual = $request->input('ruta_actual');

        // Verificar si ya existe una asistencia reciente (últimos 30 segundos)
        $asistenciaReciente = Asistencia::where('empleado_id', $empleadoId)
            ->where('created_at', '>=', Carbon::now()->subSeconds(30))
            ->first();

        // Solo registrar si no hay asistencia reciente o si la ruta cambió
        if (!$asistenciaReciente || $asistenciaReciente->ruta_actual !== $rutaActual) {
            Asistencia::create([
                'empleado_id' => $empleadoId,
                'ruta_actual' => $rutaActual,
                'created_at' => Carbon::now(),
            ]);
        }

        return response()->noContent();
    }
}
