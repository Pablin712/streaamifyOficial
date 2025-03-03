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
use App\Models\Historial;

use Illuminate\Support\Facades\Auth;

class ContabilidadController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }
    public function index(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('dashboard')) {
            abort(403, 'No tienes permiso para ver esta página.');
        }
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;
        $today = Carbon::today();
        $this->dashboardService->guardar($today);
        extract($this->dashboardService->obtenerDatosDashboard());

        extract($this->dashboardService->getNetflix($month, $year));
        extract($this->dashboardService->getDisney($month, $year));
        extract($this->dashboardService->getPrime($month, $year));
        extract($this->dashboardService->getMax($month, $year));
        extract($this->dashboardService->getMagis($month, $year));
        extract($this->dashboardService->getCrunchyroll($month, $year));
        extract($this->dashboardService->getParamount($month, $year));
        extract($this->dashboardService->getSpotify($month, $year));
        extract($this->dashboardService->getOtros($month, $year));

        return view('dashboard', compact(
            'ventas',
            'ingresos_mes',
            'ingresos_ano',
            'clientes_activos',
            'total_usuarios_activos',
            'cuentas_caidas',
            'usuarios_acobrar',
            'num_cuentas',
            'costos_mes',
            'gastos_mes',
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
            'costos_otros'
        ));
    }
    public function filterData(Request $request)
    {
        $range = $request->query('range', '1m'); // Por defecto, 1 mes

        $ingresosHistorial = $this->dashboardService->getIngresosChartData($range);
        $costosHistorial = $this->dashboardService->getCostosChartData($range);
        $gastosHistorial = $this->dashboardService->getGastosChartData($range);
        $gananciasHistorial = $this->dashboardService->getGananciasChartData($range);
        $ventasChart = $this->dashboardService->getVentasChartData($range);
        $newCustomers = $this->dashboardService->getNewCustomersChartData($range);
        $users = $this->dashboardService->getUsersChartData($range);
        $labels = array_keys($ingresosHistorial);
        return response()->json([
            'labels' => $labels,
            'ingresos' => array_values($ingresosHistorial),
            'costos' => array_values($costosHistorial),
            'gastos' => array_values($gastosHistorial),
            'ganancias' => array_values($gananciasHistorial),
            'ventasChart' => array_values($ventasChart),
            'newCustomers' => array_values($newCustomers),
            'users' => array_values($users),
        ]);
    }
}