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
        if (!Auth::user()->hasPermissionTo('empleados')) {
            abort(403, 'No tienes permisos para ver los empleados.');
        }
        $hoy = Carbon::today()->format('Y-m-d');
        $empleados = $this->obtenerEmpleadosConAsistencias($hoy);

        $datos = $empleados->map(function ($empleado) use ($hoy) {
            return $this->procesarEmpleado($empleado, $hoy);
        });

        return view('employee.statistics', compact('datos', 'empleados'));
    }

    /**
     * Obtiene los empleados con sus asistencias para la fecha especificada.
     *
     * @param string $fecha
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function obtenerEmpleadosConAsistencias(string $fecha)
    {
        return Empleado::with(['asistencias' => function ($query) use ($fecha) {
            $query->whereDate('created_at', $fecha);
        }])->get();
    }

    /**
     * Procesa los datos de un empleado para calcular estadísticas.
     *
     * @param \App\Models\Empleado $empleado
     * @param string $fecha
     * @return array
     */
    protected function procesarEmpleado(Empleado $empleado, string $fecha)
    {
        $lapsos = $this->empleadoService->obtenerLapsosDeAsistenciasPorDia($empleado->idemp, $fecha);

        return [
            'empleado' => $empleado,
            'lapsos' => $lapsos['lapsos'],
            'total' => $lapsos['total_conexion'],
            'gestionClientesHoy' => $this->empleadoService->contarGestionClientesPorDia($empleado->idemp, $fecha),
            'gestionVentasHoy' => $this->empleadoService->contarVentasPorDia($empleado->idemp, $fecha),
            'gestionCuentasHoy' => $this->empleadoService->contarGestionCuentasPorDia($empleado->idemp, $fecha),
            'gestionInventarioHoy' => $this->empleadoService->contarGestionInventarioPorDia($empleado->idemp, $fecha),
            'gestionTareasHoy' => $this->empleadoService->contarGestionTareasPorDia($empleado->idemp, $fecha),
            'gestionRecargasHoy' => $this->empleadoService->contarGestionRecargasPorDia($empleado->idemp, $fecha),
            'gestionProductosHoy' => $this->empleadoService->contarGestionProductosPorDia($empleado->idemp, $fecha),
            'gestionCostosHoy' => $this->empleadoService->contarGestionCostosPorDia($empleado->idemp, $fecha),
        ];
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
        ]);

        return response()->noContent();
    }
}
