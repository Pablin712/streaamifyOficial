<?php

namespace App\Http\Controllers;

use App\Models\DailyStatistic;
use App\Models\DetalleVenta;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Cuenta;
use App\Models\Empleado;
use App\Models\Costo;
use App\Models\Gasto;
use App\Models\ViewClientesUsuarios;
use App\Models\ViewUsuarioActivo;
use App\Models\Contabilidad;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\DashboardService;
use App\Services\CuentaSaludService;
use App\Services\MetaService;
use App\Models\Historial;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
class ContabilidadController extends Controller
{
    protected $dashboardService;
    protected $cuentaSaludService;
    protected $metaService;

    public function __construct(DashboardService $dashboardService, CuentaSaludService $cuentaSaludService, MetaService $metaService)
    {
        $this->dashboardService = $dashboardService;
        $this->cuentaSaludService = $cuentaSaludService;
        $this->metaService = $metaService;
    }
    public function index(Request $request)
    {
        if (!Gate::allows('dashboard')) {
            abort(403, 'No tienes permiso para ver esta página.');
        }
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;
        extract($this->dashboardService->obtenerDatosDashboard());
        $gastos = $this->dashboardService->getGastos($ingresos_mes, $month, $year);

        extract($this->dashboardService->getNetflix($month, $year));
        extract($this->dashboardService->getDisney($month, $year));
        extract($this->dashboardService->getPrime($month, $year));
        extract($this->dashboardService->getMax($month, $year));
        extract($this->dashboardService->getMagis($month, $year));
        extract($this->dashboardService->getCrunchyroll($month, $year));
        extract($this->dashboardService->getParamount($month, $year));
        extract($this->dashboardService->getSpotify($month, $year));
        extract($this->dashboardService->getOtros($month, $year));

        // Calcular totales financieros del negocio
        $bancos = \App\Models\Banco::all();
        $totalDisponible = $bancos->sum('monto');

        $deudas = \App\Models\Deuda::where('estado', 'pendiente')->get();
        $totalDeudasPendientes = $deudas->sum(function($deuda) {
            return $deuda->monto - $deuda->monto_pagado;
        });

        $cuentasEstadoResumen = $this->cuentaSaludService->resumenPorServicio();

        // Metas del mes en curso. Si algo falla no debe tumbar el dashboard:
        // el tablero es informativo y el resto de la pagina tiene que cargar.
        try {
            $metasTablero = $this->metaService->tablero($month, $year);
            $metasResumen = $this->metaService->resumen($metasTablero);
        } catch (\Throwable $e) {
            report($e);
            $metasTablero = [];
            $metasResumen = ['total' => 0, 'bien' => 0, 'atencion' => 0, 'mal' => 0];
        }

        return view('dashboard', compact(
            'metasTablero',
            'metasResumen',
            'cuentasEstadoResumen',
            'ingresos_mes',
            'ingresos_ano',
            'clientes_activos',
            'total_usuarios_activos',
            'cuentas_caidas',
            'usuarios_acobrar',
            'num_cuentas',

            'ingresos',
            'costos_mes',
            'costos_pct',
            'gastos_mes',
            'gastos_pct',
            'gastos_personal_mes',
            'balance',
            'balance_pct',
            'gastos',

            'promedio_pagos_mes',
            'cliente_mas_facturado',
            'ventas_mes',
            'ventas_ano',
            'espacios',

            'cuentas_netflix',
            'usuarios_netflix',
            'ingresos_netflix',
            'costos_netflix',

            'cuentas_disney',
            'usuarios_disney',
            'ingresos_disney',
            'costos_disney',

            'cuentas_prime', // Aquí es donde agregas las variables correspondientes
            'usuarios_prime',
            'ingresos_prime',
            'costos_prime',

            'cuentas_max',
            'usuarios_max',
            'ingresos_max',
            'costos_max',

            'cuentas_magis',
            'usuarios_magis',
            'ingresos_magis',
            'costos_magis',

            'cuentas_crunchy',
            'usuarios_crunchy',
            'ingresos_crunchy',
            'costos_crunchy',

            'cuentas_paramount',
            'usuarios_paramount',
            'ingresos_paramount',
            'costos_paramount',

            'cuentas_spotify',
            'usuarios_spotify',
            'ingresos_spotify',
            'costos_spotify',

            'cuentas_otros', // Agrega la variable para "otros"
            'usuarios_otros',
            'ingresos_otros',
            'costos_otros',

            'totalDisponible',
            'totalDeudasPendientes',

            'usuarios_removidos',
            'clientes_perdidos'
        ));
    }
    public function filterData(Request $request)
    {
        $range  = $request->query('range',  '1m');
        $limit  = (int) $request->query('limit',  0);
        $before = $request->query('before', null);
        $data   = $this->dashboardService->getChartData($range, $limit, $before ?: null);
        return response()->json($data);
    }
    public function generarPDF(Request $request){
        if (!Gate::allows('dashboard')) {
            abort(403, 'No tienes permiso para ver esta página.');
        }

        // Validar parámetros
        $tipoReporte = $request->get('tipo', 'mensual'); // mensual o anual

        if ($tipoReporte === 'anual') {
            return $this->generarReporteAnual($request);
        }

        // Reporte mensual
        $month = $request->get('mes', Carbon::now()->subMonth()->month);
        $year = $request->get('ano', Carbon::now()->year);

        $datos  = $this->dashboardService->obtenerDatosDashboardMensuales($month, $year);
        extract($datos);
        $gastos = $this->dashboardService->getGastos($ingresos_mes, $month, $year);

        // Resultados por servicio DEL MES pedido. Los metodos getNetflix(),
        // getDisney()... siguen usandose en el dashboard, pero para el reporte
        // no sirven: sus cuentas y usuarios son del estado actual del negocio,
        // no del periodo.
        $por_servicio = $this->dashboardService->obtenerResumenPorServicio($month, $year);

        // Altas y bajas de clientes servicio por servicio. daily_statistics
        // solo guarda el total del negocio, y con eso no se puede saber de que
        // servicio se esta yendo la gente ni cual esta creciendo.
        $rotacion = $this->dashboardService->obtenerRotacionPorServicio($month, $year);

        // Los graficos ya NO llegan como imagenes del navegador. Antes se
        // capturaban del dashboard en pantalla, asi que un reporte de junio
        // generado en septiembre mostraba las cifras de septiembre. Ahora la
        // vista los dibuja con las series del periodo (serie_diaria y
        // serie_meses) que devuelve el servicio.

        $mes = Carbon::create($year, $month, 1)->locale('es')->translatedFormat('F');

        // En vez de un compact() con cincuenta variables sueltas -donde era
        // facil olvidar una y que Blade fallara-, se pasa el arreglo del
        // servicio mas lo que se calcula aqui.
        $pdf = PDF::loadView('finance.resultPDF', array_merge($datos, [
            'mes'          => $mes,
            'year'         => $year,
            'gastos'       => $gastos,
            'por_servicio' => $por_servicio,
            'rotacion'     => $rotacion,
        ]));
        return $pdf->download('contabilidad-'.$mes.'-'.$year.'.pdf');
    }

    /**
     * Generar reporte anual consolidado
     */
    public function generarReporteAnual(Request $request)
    {
        $year = $request->get('ano', Carbon::now()->year);

        // Obtener datos anuales consolidados desde el servicio
        $datosAnuales = $this->dashboardService->getDatosAnuales($year);

        // Datos adicionales para el reporte
        extract($this->dashboardService->obtenerDatosDashboard());

        // Generar PDF con vista específica para reporte anual
        $pdf = PDF::loadView('finance.resultPDF-anual', compact(
            'year',
            'datosAnuales',
            'clientes_activos',
            'total_usuarios_activos',
            'num_cuentas',
            'cuentas_caidas'
        ));

        return $pdf->download('contabilidad-anual-'.$year.'.pdf');
    }
}
