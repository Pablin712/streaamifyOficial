<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tarea;
use App\Models\Historial;
use App\Models\Cuenta;
use App\Models\ViewUsuarioActivo;
use App\Services\TareaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class TareaController extends Controller
{
    /*
    public function __construct() {
        $this->middleware('can:tareas.destroy')->only('destroy');
    }
    */
    protected $tareaService;

    public function __construct(TareaService $tareaService)
    {
        $this->tareaService = $tareaService;
    }
    public function index()
    {
        $usuarios = ViewUsuarioActivo::all();
        $cuentas = Cuenta::with(['valor'])->where('activocue', true)->get();
        $this->tareaService->crearOActualizarTareaQuitarUsuarios($usuarios);
        $this->tareaService->crearOActualizarTareaCobrarUsuarios($usuarios);
        $this->tareaService->crearOActualizarTareaRenovarOEliminarCuentas($cuentas);
        $this->tareaService->crearOActualizarTareaArreglarCuentasCaidas($cuentas);
        $this->tareaService->crearOActualizarTareaAjustarEspaciosClientes($cuentas);
        $tareas = Tarea::orderBy('completada')
            ->orderByRaw("
                CASE 
                    WHEN prioridad = 'alta' THEN 1
                    WHEN prioridad = 'media' THEN 2
                    WHEN prioridad = 'baja' THEN 3
                END
            ")
            ->orderBy('fechalimit', 'asc')
            ->get();
        return view('employee.tareas', compact('tareas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombretarea' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'prioridad' => 'required|in:alta,media,baja',
            'fechalimit' => 'nullable|date'
        ]);

        $tarea = Tarea::create($request->all());
        Historial::create([
            'accion' => 'Creación de Tarea',
            'descripcion' =>  'Datos de la tarea: '. json_encode($tarea), // Campo opcional
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);
        $this->tareaService->notificarEmpleadoTareas($tarea);
        return redirect()->route('tareas.index')->with('success', 'Tarea agregada correctamente.');
    }

    public function destroy($id)
    {
        if (!Gate::allows('tareas.destroy')) {
            abort(403, 'No tienes permiso para ver esta página.');
        }
        Tarea::findOrFail($id)->delete();
        return redirect()->route('tareas.index')->with('success', 'Tarea eliminada.');
    }

    public function update(Request $request, $id)
    {
        $tarea = Tarea::findOrFail($id);
        Historial::create([
            'accion' => 'Actualización de Tarea',
            'descripcion' =>  'Datos de la tarea antigüa: '. json_encode($tarea), // Campo opcional
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);
        $tarea->update($request->all());

        return redirect()->route('tareas.index')->with('success', 'Tarea actualizada.');
    }

    public function completar($id)
    {
        $tarea = Tarea::findOrFail($id);
        Historial::create([
            'accion' => 'Tarea Completada',
            'descripcion' =>  'Datos de la tarea: '. json_encode($tarea), // Campo opcional
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);
        $tarea->update([
            'completada' => !$tarea->completada,
            'fecha_completada' => now(),
            'completada_por' => Auth::user()->idemp
        ])->save();

        return redirect()->route('tareas.index')->with('success', 'Tarea completada.');
    }
}
