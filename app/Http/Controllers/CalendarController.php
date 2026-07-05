<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ConcentracionService;
use App\Services\CuentaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Carbon;
use App\Models\Tarea;
use App\Models\ViewUsuarioActivo;
use App\Models\Venta;
use App\Models\Gasto;
use App\Models\Costo;
use App\Models\DailyStatistic;
use App\Models\Cliente;
use App\Models\Horario;
use App\Models\Empleado;

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

        $empleado = Auth::user();
        $isLocked = ConcentracionService::isLocked();
        $concAll  = $isLocked
            ? app(\App\Services\ConcentracionService::class)->getIds($empleado->idemp)
            : null;

        if ($isLocked && ($concAll['all_cuentas'] ?? false)) {
            $cuentas = $this->cuentaService->obtenerCuentas();
        } elseif ($isLocked) {
            $cuentaIds = $concAll['idcue'];
            $cuentas = !empty($cuentaIds)
                ? $this->cuentaService->obtenerCuentas()->whereIn('idcue', $cuentaIds)->values()
                : collect();
        } else {
            $cuentas = $this->cuentaService->obtenerCuentasSegunPermiso($empleado);
        }
        $this->cuentaService->asignarUsuarios($cuentas);

        $usuarios = ! $isLocked
            ? ViewUsuarioActivo::all()
            : (($concAll['all_usuarios'] ?? false)
                ? ViewUsuarioActivo::all()
                : (! empty($concAll['iddet'])
                    ? ViewUsuarioActivo::whereIn('iddet', $concAll['iddet'])->get()
                    : collect()));

        // Trabajador externo: solo sus tareas asignadas, sin datos financieros del negocio
        if ($isLocked) {
            $tareas = Tarea::asignadasA($empleado->idemp)->get();
            $ventas       = collect();
            $gastos       = collect();
            $costos       = collect();
            $estadisticas = collect();
            $clientesNuevos = collect();
        } else {
            $tareas = Tarea::where('completada', false)->get();

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
        }

        // ── Horarios ────────────────────────────────────────────────────────
        $isAdmin     = Auth::user()->hasAnyRole(['Admin', 'Gerente']);
        $ventanaDesde = Carbon::today()->subMonth()->startOfMonth();
        $ventanaHasta = Carbon::today()->addMonths(3)->endOfMonth();

        $horariosQuery = Horario::with('empleado:idemp,nombreemp')
            ->whereBetween('fecha', [$ventanaDesde, $ventanaHasta])
            ->where('cancelado', false);

        if (!$isAdmin) {
            $horariosQuery->where('empleado_id', $empleado->idemp);
        }
        $horariosBrutos = $horariosQuery->orderBy('fecha')->get();

        // Asistencias pasadas para verificar cumplimiento
        $asistenciasPasadas = DB::table('asistencias')
            ->select('empleado_id', 'created_at')
            ->whereBetween('created_at', [$ventanaDesde, Carbon::now()])
            ->when(!$isAdmin, fn($q) => $q->where('empleado_id', $empleado->idemp))
            ->get();

        $horariosData = $horariosBrutos->map(fn($h) => [
            'id'          => $h->id,
            'empleado_id' => $h->empleado_id,
            'nombre'      => $h->empleado->nombreemp ?? '—',
            'fecha'       => $h->fecha->format('Y-m-d'),
            'hora_inicio' => $h->hora_inicio ? substr($h->hora_inicio, 0, 5) : null,
            'hora_fin'    => $h->hora_fin    ? substr($h->hora_fin, 0, 5)    : null,
            'estado'      => $this->estadoHorario($h, $asistenciasPasadas),
            'es_mio'      => $h->empleado_id === $empleado->idemp,
            'notas'       => $h->notas,
        ])->values()->toArray();

        // Extras: días con asistencias pero sin horario agendado
        $empNames = collect(DB::table('empleados')->select('idemp', 'nombreemp')->get())
            ->pluck('nombreemp', 'idemp');

        $horariosPorEmpFecha = $horariosBrutos->groupBy(
            fn($h) => $h->empleado_id . '|' . $h->fecha->format('Y-m-d')
        );
        $extrasData = $asistenciasPasadas
            ->groupBy(fn($a) => $a->empleado_id . '|' . substr($a->created_at, 0, 10))
            ->filter(fn($asis, $key) => !isset($horariosPorEmpFecha[$key]))
            ->map(function ($asis, $key) use ($empNames, $empleado) {
                [$empId, $fecha] = explode('|', $key, 2);
                return [
                    'id'          => null,
                    'empleado_id' => (int) $empId,
                    'nombre'      => $empNames[(int) $empId] ?? '—',
                    'fecha'       => $fecha,
                    'hora_inicio' => null,
                    'hora_fin'    => null,
                    'estado'      => 'extra',
                    'es_mio'      => (int) $empId === $empleado->idemp,
                    'notas'       => null,
                ];
            })
            ->values()
            ->toArray();

        // Lista de empleados activos para el filtro admin
        $empleadosActivos = $isAdmin
            ? Empleado::whereHas('roles')->get(['idemp', 'nombreemp'])
                ->map(fn($e) => ['id' => $e->idemp, 'nombre' => $e->nombreemp])
                ->values()->toArray()
            : [];

        return view('administration.calendar', compact(
            'cuentas', 'usuarios', 'tareas',
            'ventas', 'gastos', 'costos',
            'estadisticas', 'clientesNuevos',
            'isLocked', 'isAdmin',
            'horariosData', 'extrasData', 'empleadosActivos'
        ));
    }

    private function estadoHorario(Horario $h, $asistencias): string
    {
        if ($h->fecha->gte(Carbon::today())) return 'programado';

        $fechaStr = $h->fecha->format('Y-m-d');
        $empId    = (int) $h->empleado_id;

        $asistenciasDelDia = $asistencias->filter(
            fn($a) => (int) $a->empleado_id === $empId
                   && substr($a->created_at, 0, 10) === $fechaStr
        );

        if ($asistenciasDelDia->isEmpty()) return 'ausente';

        if ($h->hora_inicio && $h->hora_fin) {
            $hi = substr($h->hora_inicio, 0, 5);
            $hf = substr($h->hora_fin, 0, 5);
            $enRango = $asistenciasDelDia->filter(
                fn($a) => substr($a->created_at, 11, 5) >= $hi
                       && substr($a->created_at, 11, 5) <= $hf
            );
            return $enRango->isNotEmpty() ? 'confirmado' : 'ausente';
        }

        return 'confirmado';
    }
}
