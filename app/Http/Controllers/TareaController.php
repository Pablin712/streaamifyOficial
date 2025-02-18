<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tarea;
use App\Models\Historial;
use Illuminate\Support\Facades\Auth;
class TareaController extends Controller
{
    public function __construct() {
        $this->middleware('can:tareas.destroy')->only('destroy');
    }
    public function index()
    {
        $tareas = Tarea::orderByRaw("
                CASE 
                    WHEN prioridad = 'alta' THEN 1
                    WHEN prioridad = 'media' THEN 2
                    WHEN prioridad = 'baja' THEN 3
                END
            ")
            ->orderBy('completada')
            ->orderBy('created_at', 'desc')
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
            'realizado_por' => Auth::user()->nombreemp.' | '. request()->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);
        return redirect()->route('tareas.index')->with('success', 'Tarea agregada correctamente.');
    }

    public function destroy($id)
    {
        Tarea::findOrFail($id)->delete();
        return redirect()->route('tareas.index')->with('success', 'Tarea eliminada.');
    }

    public function update(Request $request, $id)
    {
        $tarea = Tarea::findOrFail($id);
        Historial::create([
            'accion' => 'Actualización de Tarea',
            'descripcion' =>  'Datos de la tarea antigüa: '. json_encode($tarea), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp.' | '. request()->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
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
            'realizado_por' => Auth::user()->nombreemp.' | '. request()->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);
        $tarea->update(['completada' => !$tarea->completada]);

        return redirect()->route('tareas.index')->with('success', 'Tarea completada.');
    }
}
