<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asistencia;
use Illuminate\Support\Facades\Auth;
use App\Models\Empleado;
use App\Services\EmpleadoService;
use Illuminate\Support\Carbon;
class AsistenciaController extends Controller
{
    protected $empleadoService;
    /**
     * Constructor de la clase AsistenciaController.
     *
     * @param EmpleadoService $empleadoService
     */
    // Constructor de la clase AsistenciaController.
    public function __construct(EmpleadoService $empleadoService)
    {
        $this->empleadoService = $empleadoService;
    }
    public function index()
    {
        $empleados = Empleado::with(['asistencias' => function ($query) {
            $query->whereDate('created_at', Carbon::today());
        }])->get();

        $datos = [];

        foreach ($empleados as $empleado) {
            $lapsos = app()->call(
                [$this->empleadoService, 'obtenerLapsosDeAsistenciasPorDia'],
                ['idemp' => $empleado->idemp, 'fecha' => Carbon::today()->format('Y-m-d')]
            );

            $datos[] = [
                'empleado' => $empleado,
                'lapsos' => $lapsos['lapsos'],
                'total' => $lapsos['total_conexion']
            ];
        }

        return view('employee.statistics', compact('datos'));
    }
    public function ping(Request $request)
    {
        Asistencia::create([
            'empleado_id' => Auth::user()->idemp, // asumiendo que usas Auth con empleados
            'ruta_actual' => $request->input('ruta_actual'),
        ]);

        return response()->noContent();
    }
}
