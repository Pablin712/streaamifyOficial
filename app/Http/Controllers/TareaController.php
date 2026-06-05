<?php

namespace App\Http\Controllers;

use App\Models\Tarea;
use App\Services\TareaService;
use Illuminate\Support\Facades\Gate;

class TareaController extends Controller
{
    protected TareaService $tareaService;

    public function __construct(TareaService $tareaService)
    {
        $this->tareaService = $tareaService;
    }

    public function index()
    {
        // Sincroniza tareas individuales del sistema
        $this->tareaService->sincronizarTareas();
        return view('employee.tareas');
    }

    public function destroy($id)
    {
        Gate::authorize('tareas.destroy');
        Tarea::findOrFail($id)->delete();
        return redirect()->route('tareas.index')->with('success', 'Tarea eliminada.');
    }
}
