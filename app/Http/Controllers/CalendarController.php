<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CuentaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\Tarea;
use App\Models\ViewUsuarioActivo;

class CalendarController extends Controller
{
    protected $cuentaService;

    public function __construct(CuentaService $cuentaService)
    {
        $this->cuentaService = $cuentaService;
    }

    public function index(Request $request)
    {
        if (!Gate::allows('cuentas')) {
            abort(403, 'No tienes permiso para ver las cuentas.');
        }
        $cuentas = $this->cuentaService->obtenerCuentasSegunPermiso($empleado = Auth::user());
        $this->cuentaService->asignarUsuarios($cuentas);
        $usuarios = ViewUsuarioActivo::all();
        $tareas = Tarea::where('completada', false)->get();
        return view('administration.calendar', compact('cuentas', 'usuarios', 'tareas'));
    }
}
