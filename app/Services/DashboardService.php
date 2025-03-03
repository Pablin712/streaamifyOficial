<?php

namespace App\Services;

use Carbon\Carbon;
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

class DashboardService
{
    public function obtenerDatosDashboard()
    {
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        $usuarios = ViewUsuarioActivo::all();
        $cuentas = Cuenta::with(['valor'])->where('activocue', true)->orderBy('fechavencue')->get();
        $usuarios_acobrar = 0;
        foreach ($usuarios as $usuario) {
            $fechaVencimiento = \Carbon\Carbon::parse($usuario->fecha_vencimiento);
            $hoy = \Carbon\Carbon::today();
            $diasRestantes = $hoy->diffInDays($fechaVencimiento, false);
            if ($diasRestantes <= 3) {
                $usuarios_acobrar += 1;
            }
        }
        $espacios = 0;
        foreach ($cuentas as $cuenta) {
            $usuarios = ViewUsuarioActivo::where('idcue', $cuenta->idcue)->count();
            $cuenta->usuarios_activos = $usuarios;
            $pantmaxval = $cuenta->valor->pantmaxval;
            $usuarios_activos = $cuenta->usuarios_activos;
            $resta = $pantmaxval - $usuarios_activos;
            $espacios += $resta;
        }
        return [
            'ventas' => Venta::with('detalles_venta')->orderBy('fechaven')->get(),
            'total_usuarios_activos' => ViewUsuarioActivo::count(),
            'ingresos_mes' => Venta::whereMonth('fechaven', $month)->whereYear('fechaven', $year)->sum('totalpagoven'),
            'ingresos_ano' => Venta::whereYear('fechaven', $year)->sum('totalpagoven'),
            'costos_mes' => Costo::whereMonth('fechacos', $month)->whereYear('fechacos', $year)->sum('montocos'),
            'gastos_mes' => Gasto::whereMonth('fechagas', $month)->whereYear('fechagas', $year)->sum('montogas'),
            'clientes_activos' => ViewClientesUsuarios::count(),
            'cuentas_caidas' => Cuenta::where('caidacue', true)->count(),
            'num_cuentas' => Cuenta::where('activocue', true)->count(),
            'ventas_mes' => Venta::whereMonth('fechaven', $month)->whereYear('fechaven', $year)->count(),
            'ventas_ano' => Venta::whereYear('fechaven', $year)->count(),
            'usuarios_acobrar' => $usuarios_acobrar,
            'promedio_pagos_mes' => Venta::whereMonth('fechaven', $month)->whereYear('fechaven', $year)->avg('totalpagoven'),
            'cliente_mas_facturado' => ViewClientesUsuarios::orderByDesc('facturado')->select('nombre_cliente', 'facturado')->first(),
            'cuentas' => $cuentas,
            'espacios' => $espacios,
        ];
    }
    public function getNetflix($month, $year)
    {
        $cuentas_netflix = Cuenta::where('idcue', 'like', 'NETFLIX%')->where('activocue', true)->count();
        $usuarios_netflix = ViewUsuarioActivo::where('idcue', 'like', 'NETFLIX%')->count();
        $ingresos_netflix = DetalleVenta::whereHas('venta', function ($query) use ($month, $year) {
            $query->whereMonth('fechaven', $month)
                ->whereYear('fechaven', $year);
        })
            ->where('idper', 'like', 'NETFLIX%')
            ->sum('montodet');
        $costos_netflix = Costo::whereMonth('fechacos', $month)->whereYear('fechacos', $year)->where('idcue', 'like', 'NETFLIX%')->sum('montocos');
        return compact('cuentas_netflix', 'usuarios_netflix', 'ingresos_netflix', 'costos_netflix');
    }
    public function getDisney($month, $year)
    {
        $cuentas_disney = Cuenta::where('idcue', 'like', 'DISNEY%')->where('activocue', true)->count();
        $usuarios_disney = ViewUsuarioActivo::where('idcue', 'like', 'DISNEY%')->count();
        $ingresos_disney = DetalleVenta::whereHas('venta', function ($query) use ($month, $year) {
            $query->whereMonth('fechaven', $month)
                ->whereYear('fechaven', $year);
        })
            ->where('idper', 'like', 'DISNEY%')
            ->sum('montodet');
        $costos_disney = Costo::whereMonth('fechacos', $month)->whereYear('fechacos', $year)->where('idcue', 'like', 'DISNEY%')->sum('montocos');
        return compact('cuentas_disney', 'usuarios_disney', 'ingresos_disney', 'costos_disney');
    }
    public function getPrime($month, $year)
    {
        $cuentas_prime = Cuenta::where('idcue', 'like', 'PRIME%')->where('activocue', true)->count();
        $usuarios_prime = ViewUsuarioActivo::where('idcue', 'like', 'PRIME%')->count();
        $ingresos_prime = DetalleVenta::whereHas('venta', function ($query) use ($month, $year) {
            $query->whereMonth('fechaven', $month)
                ->whereYear('fechaven', $year);
        })
            ->where('idper', 'like', 'PRIME%')
            ->sum('montodet');
        $costos_prime = Costo::whereMonth('fechacos', $month)->whereYear('fechacos', $year)->where('idcue', 'like', 'PRIME%')->sum('montocos');
        return compact('cuentas_prime', 'usuarios_prime', 'ingresos_prime', 'costos_prime');
    }
    public function getMax($month, $year)
    {
        $cuentas_max = Cuenta::where('idcue', 'like', 'MAX%')->where('activocue', true)->count();
        $usuarios_max = ViewUsuarioActivo::where('idcue', 'like', 'MAX%')->count();
        $ingresos_max = DetalleVenta::whereHas('venta', function ($query) use ($month, $year) {
            $query->whereMonth('fechaven', $month)
                ->whereYear('fechaven', $year);
        })
            ->where('idper', 'like', 'MAX%')
            ->sum('montodet');
        $costos_max = Costo::whereMonth('fechacos', $month)->whereYear('fechacos', $year)->where('idcue', 'like', 'MAX%')->sum('montocos');
        return compact('cuentas_max', 'usuarios_max', 'ingresos_max', 'costos_max');
    }
    public function getMagis($month, $year)
    {
        $cuentas_magis = Cuenta::where('idcue', 'like', 'FLUJO%')->where('activocue', true)->count();
        $usuarios_magis = ViewUsuarioActivo::where('idcue', 'like', 'FLUJO%')->count();
        $ingresos_magis = DetalleVenta::whereHas('venta', function ($query) use ($month, $year) {
            $query->whereMonth('fechaven', $month)
                ->whereYear('fechaven', $year);
        })
            ->where('idper', 'like', 'FLUJO%')
            ->sum('montodet');
        $costos_magis = Costo::whereMonth('fechacos', $month)->whereYear('fechacos', $year)->where('idcue', 'like', 'FLUJO%')->sum('montocos');
        return compact('cuentas_magis', 'usuarios_magis', 'ingresos_magis', 'costos_magis');
    }
    public function getCrunchyroll($month, $year)
    {
        $cuentas_crunchy = Cuenta::where('idcue', 'like', 'CRUNCHY%')->where('activocue', true)->count();
        $usuarios_crunchy = ViewUsuarioActivo::where('idcue', 'like', 'CRUNCHY%')->count();
        $ingresos_crunchy = DetalleVenta::whereHas('venta', function ($query) use ($month, $year) {
            $query->whereMonth('fechaven', $month)
                ->whereYear('fechaven', $year);
        })
            ->where('idper', 'like', 'CRUNCHY%')
            ->sum('montodet');
        $costos_crunchy = Costo::whereMonth('fechacos', $month)->whereYear('fechacos', $year)->where('idcue', 'like', 'CRUNCHY%')->sum('montocos');
        return compact('cuentas_crunchy', 'usuarios_crunchy', 'ingresos_crunchy', 'costos_crunchy');
    }
    public function getParamount($month, $year)
    {
        $cuentas_paramount = Cuenta::where('idcue', 'like', 'PARAMOUNT%')->where('activocue', true)->count();
        $usuarios_paramount = ViewUsuarioActivo::where('idcue', 'like', 'PARAMOUNT%')->count();
        $ingresos_paramount = DetalleVenta::whereHas('venta', function ($query) use ($month, $year) {
            $query->whereMonth('fechaven', $month)
                ->whereYear('fechaven', $year);
        })
            ->where('idper', 'like', 'PARAMOUNT%')
            ->sum('montodet');
        $costos_paramount = Costo::whereMonth('fechacos', $month)->whereYear('fechacos', $year)->where('idcue', 'like', 'PARAMOUNT%')->sum('montocos');
        return compact('cuentas_paramount', 'usuarios_paramount', 'ingresos_paramount', 'costos_paramount');
    }
    public function getSpotify($month, $year)
    {
        $cuentas_spotify = Cuenta::where('idcue', 'like', 'SPOTIFY%')->where('activocue', true)->count();
        $usuarios_spotify = ViewUsuarioActivo::where('idcue', 'like', 'SPOTIFY%')->count();
        $ingresos_spotify = DetalleVenta::whereHas('venta', function ($query) use ($month, $year) {
            $query->whereMonth('fechaven', $month)
                ->whereYear('fechaven', $year);
        })
            ->where('idper', 'like', 'SPOTIFY%')
            ->sum('montodet');
        $costos_spotify = Costo::whereMonth('fechacos', $month)->whereYear('fechacos', $year)->where('idcue', 'like', 'SPOTIFY%')->sum('montocos');
        return compact('cuentas_spotify', 'usuarios_spotify', 'ingresos_spotify', 'costos_spotify');
    }
    public function getOtros($month, $year)
    {
        $cuentas_otros = Cuenta::where('idcue', 'not like', 'NETFLIX%')->where('activocue', true)
            ->where('idcue', 'not like', 'DISNEY%')
            ->where('idcue', 'not like', 'PRIME%')
            ->where('idcue', 'not like', 'MAX%')
            ->where('idcue', 'not like', 'FLUJO%')
            ->where('idcue', 'not like', 'CRUNCHY%')
            ->where('idcue', 'not like', 'PARAMOUNT%')
            ->where('idcue', 'not like', 'SPOTIFY%')
            ->count();
        $usuarios_otros = ViewUsuarioActivo::where('idcue', 'not like', 'NETFLIX%')
            ->where('idcue', 'not like', 'DISNEY%')
            ->where('idcue', 'not like', 'PRIME%')
            ->where('idcue', 'not like', 'MAX%')
            ->where('idcue', 'not like', 'FLUJO%')
            ->where('idcue', 'not like', 'CRUNCHY%')
            ->where('idcue', 'not like', 'PARAMOUNT%')
            ->where('idcue', 'not like', 'SPOTIFY%')
            ->count();
        $ingresos_otros = DetalleVenta::whereHas('venta', function ($query) use ($month, $year) {
            $query->whereMonth('fechaven', $month)
                ->whereYear('fechaven', $year);
        })
            ->where('idper', 'not like', 'NETFLIX%')
            ->where('idper', 'not like', 'DISNEY%')
            ->where('idper', 'not like', 'PRIME%')
            ->where('idper', 'not like', 'MAX%')
            ->where('idper', 'not like', 'FLUJO%')
            ->where('idper', 'not like', 'CRUNCHY%')
            ->where('idper', 'not like', 'PARAMOUNT%')
            ->where('idper', 'not like', 'SPOTIFY%')
            ->sum('montodet');
        $costos_otros = Costo::whereMonth('fechacos', $month)
            ->whereYear('fechacos', $year)
            ->where('idcue', 'not like', 'NETFLIX%')
            ->where('idcue', 'not like', 'DISNEY%')
            ->where('idcue', 'not like', 'PRIME%')
            ->where('idcue', 'not like', 'MAX%')
            ->where('idcue', 'not like', 'FLUJO%')
            ->where('idcue', 'not like', 'CRUNCHY%')
            ->where('idcue', 'not like', 'PARAMOUNT%')
            ->where('idcue', 'not like', 'SPOTIFY%')
            ->sum('montocos');
        return compact('cuentas_otros', 'usuarios_otros', 'ingresos_otros', 'costos_otros');
    }

    private function getIngresosMonth(int $month, int $year): float
    {
        return Venta::whereMonth('fechaven', $month)
            ->whereYear('fechaven', $year)
            ->sum('totalpagoven');
    }
    private function getIngresosDay(string $date): float
    {
        return Venta::whereDate('fechaven', $date)
            ->sum('totalpagoven');
    }
    public function getIngresosBetweenDates(string $date1, string $date2): float
    {
        return Venta::whereBetween('fechaven', [$date1, $date2])
            ->sum('totalpagoven');
    }
    public function getIngresosChartData(string $interval): array
    {
        $today = Carbon::today();
        $data = [];
        switch ($interval) {
            case '1d': // Últimos 20 días (día a día)
                for ($i = 20; $i >= 0; $i--) {
                    $date = $today->copy()->subDays($i);
                    $formattedDate = $date->format('d M'); // Ejemplo: "01 Mar"
                    $data[$formattedDate] = $this->getIngresosDay($date->toDateString());
                }
                break;

            case '1w': // Últimas 12 semanas (semana a semana)
                for ($i = 12; $i >= 0; $i--) {
                    $startOfWeek = $today->copy()->subWeeks($i)->startOfWeek();
                    $weekNumber = $startOfWeek->weekOfMonth;
                    $monthName = $startOfWeek->translatedFormat('F'); // Nombre del mes en español
                    $formattedWeek = "Week $weekNumber $monthName"; // Ejemplo: "Week 2 Febrero"
                    $endOfWeek = $today->copy()->subWeeks($i)->endOfWeek()->toDateString();
                    $data[$formattedWeek] = $this->getIngresosBetweenDates($startOfWeek->toDateString(), $endOfWeek);
                }
                break;

            case '1m': // Últimos 8 meses (mes a mes)
                for ($i = 8; $i >= 0; $i--) {
                    $date = $today->copy()->subMonths($i);
                    $monthName = $date->translatedFormat('F'); // Nombre del mes en español
                    $year = $date->year;
                    $formattedMonth = "$monthName $year"; // Ejemplo: "Marzo 2024"
                    $data[$formattedMonth] = $this->getIngresosMonth($date->month, $year);
                }
                break;

            case '3m': // Últimos 6 años (trimestre a trimestre)
                for ($i = 6; $i >= 0; $i--) {
                    $startOfQuarter = $today->copy()->subQuarters($i)->startOfQuarter();
                    $quarterNumber = ceil($startOfQuarter->month / 3);
                    $year = $startOfQuarter->year;
                    $formattedQuarter = "Tr $quarterNumber $year"; // Ejemplo: "Trimestre 1 de 2024"
                    $endOfQuarter = $today->copy()->subQuarters($i)->endOfQuarter()->toDateString();
                    $data[$formattedQuarter] = $this->getIngresosBetweenDates($startOfQuarter->toDateString(), $endOfQuarter);
                }
                break;

            case '1y': // Últimos 5 años (año a año)
                for ($i = 5; $i >= 0; $i--) {
                    $year = $today->copy()->subYears($i)->year;
                    $data[$year] = Venta::whereYear('fechaven', $year)->sum('totalpagoven');
                }
                break;

            default: // Si el intervalo es inválido, usar 1M como predeterminado
                for ($i = 8; $i >= 0; $i--) {
                    $date = $today->copy()->subMonths($i);
                    $monthName = $date->translatedFormat('F');
                    $year = $date->year;
                    $formattedMonth = "$monthName $year";
                    $data[$formattedMonth] = $this->getIngresosMonth($date->month, $year);
                }
                break;
        }
        return $data;
    }

    private function getCostosMonth(int $month, int $year): float
    {
        return Costo::whereMonth('fechacos', $month)
            ->whereYear('fechacos', $year)
            ->sum('montocos');
    }
    private function getCostosDay(string $date): float
    {
        return Costo::whereDate('fechacos', $date)
            ->sum('montocos');
    }
    public function getCostosBetweenDates(string $date1, string $date2): float
    {
        return Costo::whereBetween('fechacos', [$date1, $date2])
            ->sum('montocos');
    }
    public function getCostosChartData(string $interval): array
    {
        $today = Carbon::today();
        $data = [];

        switch ($interval) {
            case '1d': // Últimos 20 días (día a día)
                for ($i = 20; $i >= 0; $i--) {
                    $date = $today->copy()->subDays($i);
                    $formattedDate = $date->format('d M'); // Ejemplo: "01 Mar"
                    $data[$formattedDate] = $this->getCostosDay($date->toDateString());
                }
                break;

            case '1w': // Últimas 12 semanas (semana a semana)
                for ($i = 12; $i >= 0; $i--) {
                    $startOfWeek = $today->copy()->subWeeks($i)->startOfWeek();
                    $weekNumber = $startOfWeek->weekOfMonth;
                    $monthName = $startOfWeek->translatedFormat('F'); // Nombre del mes en español
                    $formattedWeek = "Week $weekNumber $monthName"; // Ejemplo: "Week 2 Febrero"
                    $endOfWeek = $today->copy()->subWeeks($i)->endOfWeek()->toDateString();
                    $data[$formattedWeek] = $this->getCostosBetweenDates($startOfWeek->toDateString(), $endOfWeek);
                }
                break;

            case '1m': // Últimos 8 meses (mes a mes)
                for ($i = 8; $i >= 0; $i--) {
                    $date = $today->copy()->subMonths($i);
                    $monthName = $date->translatedFormat('F'); // Nombre del mes en español
                    $year = $date->year;
                    $formattedMonth = "$monthName $year"; // Ejemplo: "Marzo 2024"
                    $data[$formattedMonth] = $this->getCostosMonth($date->month, $year);
                }
                break;

            case '3m': // Últimos 6 años (trimestre a trimestre)
                for ($i = 6; $i >= 0; $i--) {
                    $startOfQuarter = $today->copy()->subQuarters($i)->startOfQuarter();
                    $quarterNumber = ceil($startOfQuarter->month / 3);
                    $year = $startOfQuarter->year;
                    $formattedQuarter = "Tr $quarterNumber $year"; // Ejemplo: "Tr 1 2024"
                    $endOfQuarter = $today->copy()->subQuarters($i)->endOfQuarter()->toDateString();
                    $data[$formattedQuarter] = $this->getCostosBetweenDates($startOfQuarter->toDateString(), $endOfQuarter);
                }
                break;

            case '1y': // Últimos 5 años (año a año)
                for ($i = 5; $i >= 0; $i--) {
                    $year = $today->copy()->subYears($i)->year;
                    $data[$year] = Venta::whereYear('fechaven', $year)->sum('totalpagoven');
                }
                break;

            default: // Si el intervalo es inválido, usar 1M como predeterminado
                for ($i = 8; $i >= 0; $i--) {
                    $date = $today->copy()->subMonths($i);
                    $monthName = $date->translatedFormat('F');
                    $year = $date->year;
                    $formattedMonth = "$monthName $year";
                    $data[$formattedMonth] = $this->getCostosMonth($date->month, $year);
                }
                break;
        }
        return $data;
    }
    private function getGastosMonth(int $month, int $year): float
    {
        return Gasto::whereMonth('fechagas', $month)
            ->whereYear('fechagas', $year)
            ->sum('montogas');
    }
    private function getGastosDay(string $date): float
    {
        return Gasto::whereDate('fechagas', $date)
            ->sum('montogas');
    }
    public function getGastosBetweenDates(string $date1, string $date2): float
    {
        return Gasto::whereBetween('fechagas', [$date1, $date2])
            ->sum('montogas');
    }
    public function getGastosChartData(string $interval): array
    {
        Carbon::setLocale('es'); // Asegurar que los nombres de los meses estén en español
        $today = Carbon::today();
        $data = [];

        switch ($interval) {
            case '1d': // Últimos 20 días (día a día)
                for ($i = 20; $i >= 0; $i--) {
                    $date = $today->copy()->subDays($i);
                    $formattedDate = $date->format('d M'); // Ejemplo: "01 Mar"
                    $data[$formattedDate] = $this->getGastosDay($date->toDateString());
                }
                break;

            case '1w': // Últimas 12 semanas (semana a semana)
                for ($i = 12; $i >= 0; $i--) {
                    $startOfWeek = $today->copy()->subWeeks($i)->startOfWeek();
                    $weekNumber = $startOfWeek->weekOfYear;
                    $monthName = $startOfWeek->translatedFormat('F');
                    $formattedWeek = "Semana $weekNumber de $monthName"; // Ejemplo: "Semana 6 de Febrero"
                    $endOfWeek = $today->copy()->subWeeks($i)->endOfWeek()->toDateString();
                    $data[$formattedWeek] = $this->getGastosBetweenDates($startOfWeek->toDateString(), $endOfWeek);
                }
                break;

            case '1m': // Últimos 8 meses (mes a mes)
                for ($i = 8; $i >= 0; $i--) {
                    $date = $today->copy()->subMonths($i);
                    $monthName = $date->translatedFormat('F');
                    $year = $date->year;
                    $formattedMonth = "$monthName $year"; // Ejemplo: "Marzo 2024"
                    $data[$formattedMonth] = $this->getGastosMonth($date->month, $year);
                }
                break;

            case '3m': // Últimos 6 años (trimestre a trimestre)
                for ($i = 6; $i >= 0; $i--) {
                    $startOfQuarter = $today->copy()->subQuarters($i)->startOfQuarter();
                    $quarterNumber = ceil($startOfQuarter->month / 3);
                    $year = $startOfQuarter->year;
                    $formattedQuarter = "Tr $quarterNumber $year"; // Ejemplo: "Tr 1 2024"
                    $endOfQuarter = $today->copy()->subQuarters($i)->endOfQuarter()->toDateString();
                    $data[$formattedQuarter] = $this->getGastosBetweenDates($startOfQuarter->toDateString(), $endOfQuarter);
                }
                break;

            case '1y': // Últimos 5 años (año a año)
                for ($i = 5; $i >= 0; $i--) {
                    $year = $today->copy()->subYears($i)->year;
                    $data[$year] = Venta::whereYear('fechaven', $year)->sum('totalpagoven');
                }
                break;

            default: // Si el intervalo es inválido, usar 1M como predeterminado
                for ($i = 8; $i >= 0; $i--) {
                    $date = $today->copy()->subMonths($i);
                    $monthName = $date->translatedFormat('F');
                    $year = $date->year;
                    $formattedMonth = "$monthName $year";
                    $data[$formattedMonth] = $this->getGastosMonth($date->month, $year);
                }
                break;
        }
        return $data;
    }

    private function getGananciasMonth(int $month, int $year): float
    {
        $ingresos = $this->getIngresosMonth($month, $year);
        $costos = $this->getCostosMonth($month, $year);
        $gastos = $this->getGastosMonth($month, $year);
        return $ingresos - ($costos + $gastos);
    }
    private function getGananciasDay(string $date): float
    {
        $ingresos = $this->getIngresosDay($date);
        $costos = $this->getCostosDay($date);
        $gastos = $this->getGastosDay($date);
        return $ingresos - ($costos + $gastos);
    }
    public function getGananciasBetweenDates(string $date1, string $date2): float
    {
        $ingresos = $this->getIngresosBetweenDates($date1, $date2);
        $costos = $this->getCostosBetweenDates($date1, $date2);
        $gastos = $this->getGastosBetweenDates($date1, $date2);
        return $ingresos - ($costos + $gastos);
    }
    public function getGananciasChartData(string $interval): array
    {
        $ingresos = $this->getIngresosChartData($interval);
        $costos = $this->getCostosChartData($interval);
        $gastos = $this->getGastosChartData($interval);

        $ganancias = [];

        foreach ($ingresos as $key => $valorIngreso) {
            $costo = $costos[$key] ?? 0;
            $gasto = $gastos[$key] ?? 0;
            $ganancias[$key] = $valorIngreso - ($costo + $gasto);
        }

        return $ganancias;
    }

    private function getVentasMonth(int $month, int $year): float
    {
        return Venta::whereMonth('fechaven', $month)
            ->whereYear('fechaven', $year)
            ->count();
    }
    private function getVentasDay(string $date): float
    {
        return Venta::whereDate('fechaven', $date)->count();
    }
    public function getVentasBetweenDates(string $date1, string $date2): float
    {
        return Venta::whereBetween('fechaven', [$date1, $date2])
            ->count();
    }
    public function getVentasChartData(string $interval): array
    {
        $today = Carbon::today();
        $data = [];

        switch ($interval) {
            case '1d': // Últimos 20 días (día a día)
                for ($i = 20; $i >= 0; $i--) {
                    $date = $today->copy()->subDays($i)->toDateString();
                    $data[$date] = $this->getVentasDay($date);
                }
                break;

            case '1w': // Últimas 12 semanas (semana a semana)
                for ($i = 12; $i >= 0; $i--) {
                    $startOfWeek = $today->copy()->subWeeks($i)->startOfWeek()->toDateString();
                    $endOfWeek = $today->copy()->subWeeks($i)->endOfWeek()->toDateString();
                    $data[$startOfWeek] = $this->getVentasBetweenDates($startOfWeek, $endOfWeek);
                }
                break;

            case '1m': // Últimos 8 meses (mes a mes)
                for ($i = 8; $i >= 0; $i--) {
                    $month = $today->copy()->subMonths($i)->month;
                    $year = $today->copy()->subMonths($i)->year;
                    $data["$year-$month"] = $this->getVentasMonth($month, $year);
                }
                break;

            case '3m': // Últimos 6 años (trimestre a trimestre)
                for ($i = 6; $i >= 0; $i--) {
                    $startOfQuarter = $today->copy()->subQuarters($i)->startOfQuarter()->toDateString();
                    $endOfQuarter = $today->copy()->subQuarters($i)->endOfQuarter()->toDateString();
                    $data[$startOfQuarter] = $this->getVentasBetweenDates($startOfQuarter, $endOfQuarter);
                }
                break;

            case '1y': // Últimos 5 años (año a año)
                for ($i = 5; $i >= 0; $i--) {
                    $year = $today->copy()->subYears($i)->year;
                    $data[$year] = Venta::whereYear('fechaven', $year)->count();
                }
                break;

            default:
                for ($i = 8; $i >= 0; $i--) {
                    $month = $today->copy()->subMonths($i)->month;
                    $year = $today->copy()->subMonths($i)->year;
                    $data["$year-$month"] = $this->getVentasMonth($month, $year);
                }
                break;
        }
        //return dd($data); // Detiene la ejecución y muestra los datos
        return $data;
    }

    private function getNewCustomersMonth(int $month, int $year): float
    {
        return Cliente::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->count();
    }
    private function getNewCustomersDay(string $date): float
    {
        return Cliente::whereDate('created_at', $date)->count();
    }
    public function getNewCustomersBetweenDates(string $date1, string $date2): float
    {
        return Cliente::whereBetween('created_at', [$date1, $date2])
            ->count();
    }
    public function getNewCustomersChartData(string $interval): array
    {
        $today = Carbon::today();
        $data = [];

        switch ($interval) {
            case '1d': // Últimos 20 días (día a día)
                for ($i = 20; $i >= 0; $i--) {
                    $date = $today->copy()->subDays($i)->toDateString();
                    $data[$date] = $this->getNewCustomersDay($date);
                }
                break;

            case '1w': // Últimas 12 semanas (semana a semana)
                for ($i = 12; $i >= 0; $i--) {
                    $startOfWeek = $today->copy()->subWeeks($i)->startOfWeek()->toDateString();
                    $endOfWeek = $today->copy()->subWeeks($i)->endOfWeek()->toDateString();
                    $data[$startOfWeek] = $this->getNewCustomersBetweenDates($startOfWeek, $endOfWeek);
                }
                break;

            case '1m': // Últimos 8 meses (mes a mes)
                for ($i = 8; $i >= 0; $i--) {
                    $month = $today->copy()->subMonths($i)->month;
                    $year = $today->copy()->subMonths($i)->year;
                    $data["$year-$month"] = $this->getNewCustomersMonth($month, $year);
                }
                break;

            case '3m': // Últimos 6 años (trimestre a trimestre)
                for ($i = 6; $i >= 0; $i--) {
                    $startOfQuarter = $today->copy()->subQuarters($i)->startOfQuarter()->toDateString();
                    $endOfQuarter = $today->copy()->subQuarters($i)->endOfQuarter()->toDateString();
                    $data[$startOfQuarter] = $this->getNewCustomersBetweenDates($startOfQuarter, $endOfQuarter);
                }
                break;

            case '1y': // Últimos 5 años (año a año)
                for ($i = 5; $i >= 0; $i--) {
                    $year = $today->copy()->subYears($i)->year;
                    $data[$year] = Cliente::whereYear('created_at', $year)->count();
                }
                break;

            default:
                for ($i = 8; $i >= 0; $i--) {
                    $month = $today->copy()->subMonths($i)->month;
                    $year = $today->copy()->subMonths($i)->year;
                    $data["$year-$month"] = $this->getNewCustomersMonth($month, $year);
                }
                break;
        }
        //return dd($data); // Detiene la ejecución y muestra los datos
        return $data;
    }

    private function getUsersMonth(int $month, int $year): float
    {
        return DailyStatistic::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->sum('active_users');
    }
    private function getUsersDay(string $date): float
    {
        return DailyStatistic::whereDate('date', $date)->value('active_users') ?? 0;
    }
    public function getUsersBetweenDates(string $date1, string $date2): float
    {
        return DailyStatistic::whereBetween('created_at', [$date1, $date2])
            ->sum('active_users');
    }
    public function getUsersChartData(string $interval): array
    {
        $today = Carbon::today();
        $data = [];

        switch ($interval) {
            case '1d': // Últimos 20 días (día a día)
                for ($i = 20; $i >= 0; $i--) {
                    $date = $today->copy()->subDays($i)->toDateString();
                    $data[$date] = $this->getUsersDay($date);
                }
                break;

            case '1w': // Últimas 12 semanas (semana a semana)
                for ($i = 12; $i >= 0; $i--) {
                    $startOfWeek = $today->copy()->subWeeks($i)->startOfWeek()->toDateString();
                    $endOfWeek = $today->copy()->subWeeks($i)->endOfWeek()->toDateString();
                    $data[$startOfWeek] = $this->getUsersBetweenDates($startOfWeek, $endOfWeek);
                }
                break;

            case '1m': // Últimos 8 meses (mes a mes)
                for ($i = 8; $i >= 0; $i--) {
                    $month = $today->copy()->subMonths($i)->month;
                    $year = $today->copy()->subMonths($i)->year;
                    $data["$year-$month"] = $this->getUsersMonth($month, $year);
                }
                break;

            case '3m': // Últimos 6 años (trimestre a trimestre)
                for ($i = 6; $i >= 0; $i--) {
                    $startOfQuarter = $today->copy()->subQuarters($i)->startOfQuarter()->toDateString();
                    $endOfQuarter = $today->copy()->subQuarters($i)->endOfQuarter()->toDateString();
                    $data[$startOfQuarter] = $this->getUsersBetweenDates($startOfQuarter, $endOfQuarter);
                }
                break;

            case '1y': // Últimos 5 años (año a año)
                for ($i = 5; $i >= 0; $i--) {
                    $year = $today->copy()->subYears($i)->year;
                    $data[$year] = DailyStatistic::whereYear('date', $year)->sum('active_users');
                }
                break;

            default:
                for ($i = 8; $i >= 0; $i--) {
                    $month = $today->copy()->subMonths($i)->month;
                    $year = $today->copy()->subMonths($i)->year;
                    $data["$year-$month"] = $this->getUsersMonth($month, $year);
                }
                break;
        }
        //return dd($data); // Detiene la ejecución y muestra los datos
        return $data;
    }
    public function guardar($date)
    {
        $activeUsers = ViewUsuarioActivo::count();
        $dailyRevenue = Venta::whereDate('created_at', $date)->sum('totalpagoven');
        $dailyCost = Costo::whereDate('fechacos', $date)->sum('montocos');
        $dailyBill = Gasto::whereDate('created_at', $date)->sum('montogas');
        $dailySales = Venta::whereDate('created_at', $date)->count();
        $newCustomers = Cliente::whereDate('created_at', $date)->count();
        DailyStatistic::updateOrCreate(
            ['date' => $date], // Fecha única
            [
                'active_users' => $activeUsers,
                'daily_revenue' => $dailyRevenue,
                'daily_cost' => $dailyCost,
                'daily_bill' => $dailyBill,
                'daily_sales' => $dailySales,
                'new_customers' => $newCustomers,
            ]
        );
    }
}
