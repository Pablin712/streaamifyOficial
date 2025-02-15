<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tarea;

class TareaController extends Controller
{
    public function index()
    {
        //Mysql
        /*
        $tareas = Tarea::orderByRaw("FIELD(prioridad, 'alta', 'media', 'baja')")
            ->orderBy('completada')
            ->orderBy('created_at', 'desc')
            ->get();
        */
        //postgrsql
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

        Tarea::create($request->all());

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
        $tarea->update($request->all());

        return redirect()->route('tareas.index')->with('success', 'Tarea actualizada.');
    }

    public function completar($id)
    {
        $tarea = Tarea::findOrFail($id);
        $tarea->update(['completada' => !$tarea->completada]);

        return redirect()->route('tareas.index')->with('success', 'Tarea actualizada.');
    }
}
