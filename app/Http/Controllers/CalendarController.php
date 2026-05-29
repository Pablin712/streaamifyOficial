<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CuentaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\Tarea;
use App\Models\ViewUsuarioActivo;
use App\Models\Venta;
use App\Models\Gasto;
use App\Models\Costo;
use App\Models\DailyStatistic;
use App\Models\Cliente;

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
        $tareas   = Tarea::where('completada', false)->get();

        $ventas = Venta::select('idven', 'idcli', 'fechaven', 'totalpagoven')
            ->with('cliente:idcli,nombrecli')
            ->orderBy('fechaven', 'desc')
            ->get()
            ->map(fn($v) => [
                'id'      => $v->idven,
                'cliente' => $v->cliente?->nombrecli ?? '—',
                'fecha'   => $v->fechaven?->format('Y-m-d'),
                'monto'   => (float) $v->totalpagoven,
            ]);

        $gastos = Gasto::select('idgas', 'fechagas', 'montogas', 'descripciongas')
            ->with('tipoGasto:idtip,detalletip')
            ->orderBy('fechagas', 'desc')
            ->get()
            ->map(fn($g) => [
                'id'          => $g->idgas,
                'tipo'        => $g->tipoGasto?->detalletip ?? '—',
                'descripcion' => $g->descripciongas,
                'fecha'       => $g->fechagas,
                'monto'       => (float) $g->montogas,
            ]);

        $costos = Costo::select('idcos', 'idcue', 'fechacos', 'montocos', 'descripcioncos', 'cuenta_usuario_snapshot', 'servicio_snapshot')
            ->orderBy('fechacos', 'desc')
            ->get()
            ->map(fn($c) => [
                'id'       => $c->idcos,
                'cuenta'   => $c->idcue,
                'usuario'  => $c->cuenta_usuario_snapshot,
                'servicio' => $c->servicio_snapshot,
                'fecha'    => $c->fechacos,
                'monto'    => (float) $c->montocos,
            ]);

        $estadisticas = DailyStatistic::orderBy('date', 'desc')
            ->get()
            ->keyBy(fn($s) => $s->date->format('Y-m-d'));

        $clientesNuevos = Cliente::select('idcli', 'nombrecli', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($c) => [
                'id'     => $c->idcli,
                'nombre' => $c->nombrecli,
                'fecha'  => $c->created_at?->format('Y-m-d'),
            ]);

        return view('administration.calendar', compact(
            'cuentas', 'usuarios', 'tareas',
            'ventas', 'gastos', 'costos',
            'estadisticas', 'clientesNuevos'
        ));
    }
}
