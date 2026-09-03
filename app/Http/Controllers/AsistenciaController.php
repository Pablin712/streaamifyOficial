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
        $mes  = (int) Carbon::today()->format('m');
        $anio = (int) Carbon::today()->format('Y');

        $empleados = Empleado::orderBy('idemp', 'asc')->get(['idemp', 'nombreemp']);

        // Version en lote: unas pocas consultas en lugar de once por dia y por
        // empleado (con doce empleados eran ~4.000 consultas por carga).
        $estadisticas = $this->empleadoService->obtenerEstadisticasMensualesEnLote($empleados, $mes, $anio);

        // Del mas activo al menos activo: primero quien mas horas estuvo
        // conectado y, a igualdad de horas, quien mas acciones registro.
        // Antes se usaba una lista fija de IDs prioritarios ([2, 12]), que
        // dejaba de tener sentido en cuanto cambiaba el equipo.
        $estadisticasOrdenadas = collect($estadisticas)
            ->sortByDesc(fn($e) => [$e['total_horas'], $e['total_acciones']])
            ->all();

        return view('employee.statistics', [
            'estadisticasOrdenadas' => $estadisticasOrdenadas,
            'empleados'             => $empleados,
            'mes'                   => $mes,
            'anio'                  => $anio,
        ]);
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
